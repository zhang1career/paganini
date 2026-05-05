<?php

declare(strict_types=1);

namespace Paganini\Batch\DTO;

use Paganini\Batch\Enums\JobStatus;

final readonly class BatchRunResult
{
    /**
     * @param  list<ItemOutcome>  $failures  Failed items collected during the inner phase (success items omitted to keep the result small).
     */
    public function __construct(
        public int $jobId,
        public string $bizKey,
        public int $total,
        public int $successCount,
        public int $failureCount,
        public JobStatus $status,
        public array $failures,
    ) {}

    public function fullySucceeded(): bool
    {
        return $this->status === JobStatus::Completed && $this->failureCount === 0;
    }
}
