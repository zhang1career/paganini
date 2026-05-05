<?php

declare(strict_types=1);

namespace Tests\Unit\Batch;

use Paganini\Batch\Contracts\BatchItemHandlerContract;
use Paganini\Batch\Contracts\BatchJobRepositoryContract;
use Paganini\Batch\Contracts\BatchPlanProviderContract;
use Paganini\Batch\Contracts\TransactionRunnerContract;
use Paganini\Batch\DTO\BatchItem;
use Paganini\Batch\DTO\BatchJobRecord;
use Paganini\Batch\DTO\BatchPlan;
use Paganini\Batch\DTO\ItemOutcome;
use Paganini\Batch\Enums\ItemStatus;
use Paganini\Batch\Enums\JobStatus;
use Paganini\Batch\Execution\BatchExecutor;
use RuntimeException;
use Tests\TestCase;
use Throwable;

class BatchExecutorTest extends TestCase
{
    public function test_completed_when_all_items_succeed(): void
    {
        $repo = new InMemoryBatchJobRepository;
        $tx = new ImmediateTransactionRunner;
        $executor = new BatchExecutor($tx, $tx, $repo);

        $plan = new BatchPlan(['game_id' => 7], [
            new BatchItem('order-1'),
            new BatchItem('order-2'),
            new BatchItem('order-3'),
        ]);

        $handler = new class implements BatchItemHandlerContract
        {
            public array $seen = [];

            public function handle(BatchItem $item, array $jobPayload): void
            {
                $this->seen[] = $item->ref;
            }
        };

        $result = $executor->execute('game:7', new StubPlanProvider($plan), $handler);

        $this->assertSame(JobStatus::Completed, $result->status);
        $this->assertSame(3, $result->successCount);
        $this->assertSame(0, $result->failureCount);
        $this->assertTrue($result->fullySucceeded());
        $this->assertSame(['order-1', 'order-2', 'order-3'], $handler->seen);
        $this->assertSame(3, $repo->find($result->jobId)->cursor);
        $this->assertSame(3, $repo->find($result->jobId)->successCount);
    }

    public function test_partial_when_one_item_fails(): void
    {
        $repo = new InMemoryBatchJobRepository;
        $tx = new ImmediateTransactionRunner;
        $executor = new BatchExecutor($tx, $tx, $repo);

        $plan = new BatchPlan([], [
            new BatchItem('a'),
            new BatchItem('b'),
            new BatchItem('c'),
        ]);

        $handler = new class implements BatchItemHandlerContract
        {
            public function handle(BatchItem $item, array $jobPayload): void
            {
                if ($item->ref === 'b') {
                    throw new RuntimeException('boom');
                }
            }
        };

        $result = $executor->execute('biz', new StubPlanProvider($plan), $handler);

        $this->assertSame(JobStatus::Partial, $result->status);
        $this->assertSame(2, $result->successCount);
        $this->assertSame(1, $result->failureCount);
        $this->assertCount(1, $result->failures);
        $this->assertSame('b', $result->failures[0]->ref);
        $this->assertSame('boom', $result->failures[0]->error);
        $this->assertSame(3, $repo->find($result->jobId)->cursor);
    }

    public function test_failed_item_rollback_does_not_lose_audit_row(): void
    {
        $repo = new InMemoryBatchJobRepository;
        $outerTx = new ImmediateTransactionRunner;
        $innerTx = new RollbackAwareTransactionRunner($repo);
        $executor = new BatchExecutor($outerTx, $innerTx, $repo);

        $plan = new BatchPlan([], [
            new BatchItem('a'),
            new BatchItem('b'),
        ]);

        $handler = new class implements BatchItemHandlerContract
        {
            public function handle(BatchItem $item, array $jobPayload): void
            {
                if ($item->ref === 'b') {
                    throw new RuntimeException('payout failed');
                }
            }
        };

        $result = $executor->execute('biz-x', new StubPlanProvider($plan), $handler);

        $this->assertSame(JobStatus::Partial, $result->status);
        $job = $repo->find($result->jobId);
        $this->assertNotNull($job);
        $this->assertSame(2, $job->cursor);
        $this->assertSame(1, $job->successCount);
        $this->assertSame(1, $job->failureCount);
        $this->assertSame('payout failed', $job->lastError);
    }

    public function test_resume_skips_already_processed_items_and_does_not_invoke_plan_provider(): void
    {
        // Simulate a crash by making the failure-outcome record itself throw the FIRST time it
        // is invoked: the executor's catch block re-raises the throw, so markStatus is never
        // called and the job stays in JobStatus::Running with whatever cursor was reached. This
        // mirrors a worker process being killed mid-run (e.g. SIGKILL, OOM).
        $repo = new CrashOnNextFailureRepo;
        $tx = new ImmediateTransactionRunner;
        $executor = new BatchExecutor($tx, $tx, $repo);

        $crashingHandler = new class implements BatchItemHandlerContract
        {
            public function handle(BatchItem $item, array $jobPayload): void
            {
                if ($item->ref === 'b') {
                    throw new RuntimeException('payout failed');
                }
            }
        };

        $plan = new BatchPlan(['note' => 'first'], [
            new BatchItem('a'),
            new BatchItem('b'),
            new BatchItem('c'),
        ]);

        try {
            $executor->execute('biz-resume', new StubPlanProvider($plan), $crashingHandler);
            $this->fail('Expected SimulatedCrashException to bubble out of the executor');
        } catch (SimulatedCrashException) {
            // Expected: failure-outcome write was the simulated crash point, markStatus skipped.
        }

        $job = $repo->findByBizKey('biz-resume');
        $this->assertNotNull($job);
        $this->assertSame(JobStatus::Running, $job->status);
        $this->assertSame(1, $job->cursor, 'item a should have been recorded before the crash');
        $this->assertCount(3, $job->items);

        // Resume: inject a non-crashing handler. Plan provider here is a sentinel that fails the
        // test if invoked — resume must NOT call it (otherwise the consumer's outer-state mutations
        // would run a second time).
        $resumeHandler = new class implements BatchItemHandlerContract
        {
            public array $seen = [];

            public function handle(BatchItem $item, array $jobPayload): void
            {
                $this->seen[] = $item->ref;
            }
        };

        $exploding = new class implements BatchPlanProviderContract
        {
            public function makePlan(): BatchPlan
            {
                throw new RuntimeException('plan provider must not run on resume');
            }
        };

        $result = $executor->execute('biz-resume', $exploding, $resumeHandler);

        $this->assertSame(JobStatus::Completed, $result->status);
        // Only the unfinished tail (b, c) is replayed — item 'a' is NOT redone, preventing double-pay.
        $this->assertSame(['b', 'c'], $resumeHandler->seen);
        $this->assertSame(2, $result->successCount);

        $jobAfter = $repo->find($result->jobId);
        $this->assertNotNull($jobAfter);
        $this->assertSame(3, $jobAfter->cursor);
        // success_count is cumulative across attempts (1 from first run + 2 from resume).
        $this->assertSame(3, $jobAfter->successCount);
    }

    public function test_outer_phase_failure_propagates_and_no_job_persisted(): void
    {
        $repo = new InMemoryBatchJobRepository;
        $tx = new ImmediateTransactionRunner;
        $executor = new BatchExecutor($tx, $tx, $repo);

        $provider = new class implements BatchPlanProviderContract
        {
            public function makePlan(): BatchPlan
            {
                throw new RuntimeException('plan failed');
            }
        };

        try {
            $executor->execute('biz-z', $provider, new NullHandler);
            $this->fail('Expected exception not thrown');
        } catch (Throwable $e) {
            $this->assertSame('plan failed', $e->getMessage());
        }

        $this->assertNull($repo->findByBizKey('biz-z'));
    }
}

final class StubPlanProvider implements BatchPlanProviderContract
{
    public function __construct(private readonly BatchPlan $plan) {}

    public function makePlan(): BatchPlan
    {
        return $this->plan;
    }
}

final class NullHandler implements BatchItemHandlerContract
{
    public function handle(BatchItem $item, array $jobPayload): void {}
}

final class ImmediateTransactionRunner implements TransactionRunnerContract
{
    public function run(callable $work): mixed
    {
        return $work();
    }
}

final class SimulatedCrashException extends RuntimeException {}

/** Inner-tx runner that simulates a real DB rollback by remembering the repo state and restoring on failure. */
final class RollbackAwareTransactionRunner implements TransactionRunnerContract
{
    public function __construct(private InMemoryBatchJobRepository $repo) {}

    public function run(callable $work): mixed
    {
        $snapshot = $this->repo->snapshot();
        try {
            return $work();
        } catch (Throwable $e) {
            $this->repo->restore($snapshot);
            throw $e;
        }
    }
}

class InMemoryBatchJobRepository implements BatchJobRepositoryContract
{
    /** @var array<int, array<string, mixed>> */
    private array $rows = [];

    private int $nextId = 1;

    public function create(string $bizKey, array $payload, array $items): BatchJobRecord
    {
        $now = (int) (microtime(true) * 1000);
        $id = $this->nextId++;
        $this->rows[$id] = [
            'id' => $id,
            'biz_key' => $bizKey,
            'payload' => $payload,
            'items' => array_values($items),
            'total' => count($items),
            'cursor' => 0,
            'success_count' => 0,
            'failure_count' => 0,
            'status' => JobStatus::Running,
            'last_error' => null,
            'ct' => $now,
            'ut' => $now,
        ];

        $found = $this->find($id);
        if ($found === null) {
            throw new RuntimeException('Inserted row vanished.');
        }

        return $found;
    }

    public function find(int $jobId): ?BatchJobRecord
    {
        if (! isset($this->rows[$jobId])) {
            return null;
        }

        return $this->hydrate($this->rows[$jobId]);
    }

    public function findByBizKey(string $bizKey): ?BatchJobRecord
    {
        foreach ($this->rows as $row) {
            if ($row['biz_key'] === $bizKey) {
                return $this->hydrate($row);
            }
        }

        return null;
    }

    public function recordOutcome(int $jobId, int $cursorAfter, ItemOutcome $outcome): void
    {
        if (! isset($this->rows[$jobId])) {
            throw new RuntimeException('Job not found: '.$jobId);
        }
        $this->rows[$jobId]['cursor'] = $cursorAfter;
        if ($outcome->status === ItemStatus::Success) {
            $this->rows[$jobId]['success_count']++;
        } else {
            $this->rows[$jobId]['failure_count']++;
            $this->rows[$jobId]['last_error'] = $outcome->error;
        }
        $this->rows[$jobId]['ut'] = (int) (microtime(true) * 1000);
    }

    public function markStatus(int $jobId, JobStatus $status, ?string $lastError = null): void
    {
        if (! isset($this->rows[$jobId])) {
            throw new RuntimeException('Job not found: '.$jobId);
        }
        $this->rows[$jobId]['status'] = $status;
        if ($lastError !== null) {
            $this->rows[$jobId]['last_error'] = $lastError;
        }
        $this->rows[$jobId]['ut'] = (int) (microtime(true) * 1000);
    }

    /** @return array<int, array<string, mixed>> */
    public function snapshot(): array
    {
        return array_map(static fn (array $r): array => $r, $this->rows);
    }

    /**
     * @param  array<int, array<string, mixed>>  $snapshot
     */
    public function restore(array $snapshot): void
    {
        $this->rows = $snapshot;
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function hydrate(array $row): BatchJobRecord
    {
        $items = is_array($row['items'] ?? null) ? array_values($row['items']) : [];

        return new BatchJobRecord(
            id: (int) $row['id'],
            bizKey: (string) $row['biz_key'],
            payload: (array) $row['payload'],
            items: $items,
            total: (int) $row['total'],
            cursor: (int) $row['cursor'],
            successCount: (int) $row['success_count'],
            failureCount: (int) $row['failure_count'],
            status: $row['status'] instanceof JobStatus ? $row['status'] : JobStatus::from((int) $row['status']),
            lastError: $row['last_error'] === null ? null : (string) $row['last_error'],
            ct: (int) $row['ct'],
            ut: (int) $row['ut'],
        );
    }
}

/**
 * In-memory repo whose first FAILURE recordOutcome call throws SimulatedCrashException, mimicking
 * a worker process being killed before it could persist the failure audit row or run markStatus.
 * Subsequent calls (including the resume run) behave normally.
 */
final class CrashOnNextFailureRepo extends InMemoryBatchJobRepository
{
    private bool $armed = true;

    public function recordOutcome(int $jobId, int $cursorAfter, ItemOutcome $outcome): void
    {
        if ($this->armed && $outcome->status === ItemStatus::Failure) {
            $this->armed = false;
            throw new SimulatedCrashException('process killed before failure outcome could be recorded');
        }
        parent::recordOutcome($jobId, $cursorAfter, $outcome);
    }
}
