<?php

declare(strict_types=1);

namespace Paganini\Batch\Enums;

enum JobStatus: int
{
    case Pending = 0;
    case Running = 1;
    case Completed = 2;
    /** Some items succeeded, some failed. Job is closed. */
    case Partial = 3;
    /** Outer phase failed; no items processed. */
    case Failed = 4;

    public function isTerminated(): bool
    {
        return match ($this) {
            self::Completed, self::Partial, self::Failed => true,
            self::Pending, self::Running => false,
        };
    }
}
