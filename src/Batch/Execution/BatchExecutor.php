<?php

declare(strict_types=1);

namespace Paganini\Batch\Execution;

use Paganini\Batch\Contracts\BatchItemHandlerContract;
use Paganini\Batch\Contracts\BatchJobRepositoryContract;
use Paganini\Batch\Contracts\BatchPlanProviderContract;
use Paganini\Batch\Contracts\TransactionRunnerContract;
use Paganini\Batch\DTO\BatchItem;
use Paganini\Batch\DTO\BatchJobRecord;
use Paganini\Batch\DTO\BatchRunResult;
use Paganini\Batch\DTO\ItemOutcome;
use Paganini\Batch\Enums\JobStatus;
use Throwable;

/**
 * Orchestrates a "big task / small tasks" run.
 *
 * Phase 1 (outer transaction, owned by {@see TransactionRunnerContract::run}):
 *  - {@see BatchPlanProviderContract::makePlan} runs alongside any big-task state
 *    mutations the consumer wants to commit atomically with the job header
 *    (e.g. "lock game G and mark it settled, return the list of accepted bets
 *    that need to be paid out").
 *  - The job header is written via {@see BatchJobRepositoryContract::create},
 *    persisting both the consumer payload AND the items list so the executor
 *    can resume without re-invoking the plan provider (which would otherwise
 *    re-mutate already-committed outer-phase state).
 *  - Outer transaction commits. From now on the big-task state is durable even
 *    if every small task fails.
 *
 * Phase 2 (per-item, each in its own inner transaction):
 *  - {@see BatchItemHandlerContract::handle} processes a single item.
 *  - On success: {@see BatchJobRepositoryContract::recordOutcome} is called
 *    *inside* the inner transaction, so the cursor advances atomically with
 *    the consumer's writes.
 *  - On failure: the inner transaction is rolled back, then a failure outcome
 *    is persisted *outside* of any transaction (so the rollback does not erase
 *    the audit row).
 *
 * Phase 3:
 *  - {@see BatchJobRepositoryContract::markStatus} closes the job as
 *    {@see JobStatus::Completed} (no failures) or {@see JobStatus::Partial}
 *    (one or more item failures).
 *
 * Resume semantics:
 *  - When {@see execute} is called with a {@code bizKey} whose latest job is
 *    still {@see JobStatus::Running} (i.e. a previous attempt crashed before
 *    Phase 3 wrote a terminal status), the executor REUSES that job: it skips
 *    the outer phase entirely (state already mutated, items already persisted)
 *    and replays only the unfinished items, starting at {@code cursor + 1}.
 *  - The {@see BatchPlanProviderContract} passed in is therefore IGNORED on
 *    resume — callers can pass any provider (typically the same one), but the
 *    items used are always the ones persisted on the original job header.
 *  - If the outer phase itself throws on the FIRST attempt, the job header is
 *    not created, the transaction is rolled back by the runner, and the
 *    exception is re-thrown so the caller can decide what to do.
 */
final readonly class BatchExecutor
{
    public function __construct(
        private TransactionRunnerContract $outerTx,
        private TransactionRunnerContract $innerTx,
        private BatchJobRepositoryContract $repository,
    ) {}

    /**
     * @throws Throwable  re-thrown from the outer phase if {@see BatchPlanProviderContract::makePlan} fails
     *                    on a fresh execution (resume never calls the provider).
     */
    public function execute(
        string $bizKey,
        BatchPlanProviderContract $planProvider,
        BatchItemHandlerContract $handler,
    ): BatchRunResult {
        $existing = $this->repository->findByBizKey($bizKey);
        if ($existing !== null && ! $existing->status->isTerminated()) {
            return $this->resumeInnerPhase($existing, $handler);
        }

        [$job, $items] = $this->createJobInOuterPhase($bizKey, $planProvider);

        return $this->runInnerPhase($job, $items, $handler, startCursor: 0);
    }

    /**
     * @return array{0: BatchJobRecord, 1: list<BatchItem>}
     */
    private function createJobInOuterPhase(string $bizKey, BatchPlanProviderContract $planProvider): array
    {
        return $this->outerTx->run(function () use ($bizKey, $planProvider): array {
            $plan = $planProvider->makePlan();
            $job = $this->repository->create($bizKey, $plan->payload, $plan->items);

            return [$job, $plan->items];
        });
    }

    private function resumeInnerPhase(BatchJobRecord $job, BatchItemHandlerContract $handler): BatchRunResult
    {
        // Items planned at original creation are persisted on the job header. cursor counts how many
        // were already attempted, so the unfinished tail is array_slice(items, cursor).
        $remaining = array_values(array_slice($job->items, $job->cursor));

        return $this->runInnerPhase($job, $remaining, $handler, startCursor: $job->cursor);
    }

    /**
     * @param  list<BatchItem>  $items
     */
    private function runInnerPhase(
        BatchJobRecord $job,
        array $items,
        BatchItemHandlerContract $handler,
        int $startCursor,
    ): BatchRunResult {
        $successCount = 0;
        $failures = [];

        $cursor = $startCursor;
        foreach ($items as $item) {
            $cursor++;
            try {
                $this->innerTx->run(function () use ($item, $job, $handler, $cursor): void {
                    $handler->handle($item, $job->payload);
                    $this->repository->recordOutcome($job->id, $cursor, ItemOutcome::success($item->ref));
                });
                $successCount++;
            } catch (Throwable $e) {
                $outcome = ItemOutcome::failure($item->ref, $e->getMessage());
                $this->repository->recordOutcome($job->id, $cursor, $outcome);
                $failures[] = $outcome;
            }
        }

        $finalStatus = $failures === [] ? JobStatus::Completed : JobStatus::Partial;
        $this->repository->markStatus(
            $job->id,
            $finalStatus,
            $failures === [] ? null : $failures[count($failures) - 1]->error,
        );

        return new BatchRunResult(
            jobId: $job->id,
            bizKey: $job->bizKey,
            total: $job->total,
            successCount: $successCount,
            failureCount: count($failures),
            status: $finalStatus,
            failures: $failures,
        );
    }
}
