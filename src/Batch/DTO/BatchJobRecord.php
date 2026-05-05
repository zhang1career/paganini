<?php

declare(strict_types=1);

namespace Paganini\Batch\DTO;

use Paganini\Batch\Enums\JobStatus;

final readonly class BatchJobRecord
{
    /**
     * @param  array<string, mixed>  $payload  Consumer-defined diagnostic payload.
     * @param  list<BatchItem>  $items  Items planned at job creation; persisted so the executor
     *                                  can resume an unterminated job by replaying the unfinished
     *                                  tail, NOT by re-invoking the plan provider (which would
     *                                  otherwise re-mutate already-committed outer-phase state).
     */
    public function __construct(
        public int $id,
        public string $bizKey,
        public array $payload,
        public array $items,
        public int $total,
        public int $cursor,
        public int $successCount,
        public int $failureCount,
        public JobStatus $status,
        public ?string $lastError,
        public int $ct,
        public int $ut,
    ) {}
}
