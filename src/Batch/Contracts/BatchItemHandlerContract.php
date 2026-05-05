<?php

declare(strict_types=1);

namespace Paganini\Batch\Contracts;

use Paganini\Batch\DTO\BatchItem;

/**
 * Inner-phase callable: handles one item. Invoked inside the inner-phase
 * transaction owned by {@see \Paganini\Batch\Execution\BatchExecutor}. Throw on
 * failure — the transaction is rolled back and the executor records a failure
 * outcome, then continues with the next item.
 */
interface BatchItemHandlerContract
{
    /**
     * @param  array<string, mixed>  $jobPayload  Same payload returned by the plan provider.
     */
    public function handle(BatchItem $item, array $jobPayload): void;
}
