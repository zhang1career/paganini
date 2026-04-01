<?php

namespace Paganini\Aggregation\Policies;

use Paganini\Aggregation\Contracts\DegradePolicyContract;

class DefaultDegradePolicy implements DegradePolicyContract
{
    public const STRATEGY_MASK_NULL = 'mask_null';
    public const STRATEGY_MASK_ERROR_OBJECT = 'mask_error_object';
    public const STRATEGY_FAIL_FAST = 'fail_fast';

    public function __construct(
        private readonly string $strategy = self::STRATEGY_MASK_NULL,
        private readonly string $maskErrorMessage = 'Service temporarily unavailable.',
    ) {
    }

    public function strategy(): string
    {
        if (!in_array($this->strategy, [
            self::STRATEGY_MASK_NULL,
            self::STRATEGY_MASK_ERROR_OBJECT,
            self::STRATEGY_FAIL_FAST,
        ], true)) {
            return self::STRATEGY_MASK_NULL;
        }

        return $this->strategy;
    }

    public function shouldFailFast(): bool
    {
        return $this->strategy() === self::STRATEGY_FAIL_FAST;
    }

    public function degradedValue(string $key): ?array
    {
        if ($this->strategy() === self::STRATEGY_MASK_ERROR_OBJECT) {
            return [
                'degraded' => true,
                'key' => $key,
                'message' => $this->maskErrorMessage,
            ];
        }

        return null;
    }
}
