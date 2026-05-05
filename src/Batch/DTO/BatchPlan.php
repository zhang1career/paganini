<?php

declare(strict_types=1);

namespace Paganini\Batch\DTO;

/**
 * Output of the outer phase: the persistent payload to record on the job header
 * plus the ordered list of items to run.
 */
final readonly class BatchPlan
{
    /**
     * @param  array<string, mixed>  $payload  Serialised on the job header for diagnostics / resume.
     * @param  list<BatchItem>  $items
     */
    public function __construct(
        public array $payload,
        public array $items,
    ) {}
}
