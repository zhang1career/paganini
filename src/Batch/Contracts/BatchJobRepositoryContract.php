<?php

declare(strict_types=1);

namespace Paganini\Batch\Contracts;

use Paganini\Batch\DTO\BatchJobRecord;
use Paganini\Batch\DTO\ItemOutcome;
use Paganini\Batch\Enums\JobStatus;

/**
 * Persistence contract for batch job state. The library does not own the
 * physical table — the implementation is constructed by the consumer with the
 * table name(s) it wants to use, satisfying the "table name injected by the
 * application" rule.
 *
 * Implementations should be safe to call from inside or outside of a
 * {@see TransactionRunnerContract} block; the executor invokes them in well
 * defined places and never assumes implicit transaction context.
 */
interface BatchJobRepositoryContract
{
    /**
     * Insert a new job header. Must enforce uniqueness on {@code bizKey} so
     * that a second concurrent caller observes
     * {@see \Paganini\Batch\Exceptions\BatchAlreadyRunningException} (raised by
     * the executor after seeing the existing non-terminated record) rather
     * than getting a duplicate row.
     *
     * @param  array<string, mixed>  $payload
     * @param  list<\Paganini\Batch\DTO\BatchItem>  $items  Persisted as part of the job header
     *                                                     (typically inside the {@code payload} JSON
     *                                                     under a library-reserved key) so the
     *                                                     executor can resume a Running job by
     *                                                     replaying the unfinished tail without
     *                                                     re-invoking the plan provider.
     */
    public function create(string $bizKey, array $payload, array $items): BatchJobRecord;

    public function find(int $jobId): ?BatchJobRecord;

    public function findByBizKey(string $bizKey): ?BatchJobRecord;

    /**
     * Persist one item's outcome and advance the job cursor by one. Implementations
     * are expected to be transactional (the executor calls this from inside the
     * inner-phase {@see TransactionRunnerContract::run} for success items, and
     * outside the rolled-back transaction for failure items, so the call is
     * idempotent on the cursor counter must not be assumed).
     */
    public function recordOutcome(int $jobId, int $cursorAfter, ItemOutcome $outcome): void;

    public function markStatus(int $jobId, JobStatus $status, ?string $lastError = null): void;
}
