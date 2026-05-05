<?php

declare(strict_types=1);

namespace Paganini\Batch\Contracts;

use Paganini\Batch\DTO\BatchPlan;

/**
 * Outer-phase callable: produces the work plan for a batch run. Invoked once
 * inside the outer transaction owned by {@see \Paganini\Batch\Execution\BatchExecutor}
 * (so the provider may safely mutate big-task state — e.g. lock a parent row,
 * mark it settled — alongside producing the items list).
 *
 * Must throw on unrecoverable conditions; the outer transaction will be rolled
 * back and the job record will not be created.
 */
interface BatchPlanProviderContract
{
    public function makePlan(): BatchPlan;
}
