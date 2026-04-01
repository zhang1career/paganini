<?php

namespace Paganini\Aggregation\DTO;

readonly class AggregationResult
{
    public function __construct(
        public array $biz,
        public array $degradedKeys,
        public array $keysUsed,
    ) {
    }

    public function hasDegraded(): bool
    {
        return $this->degradedKeys !== [];
    }

    public function toArray(): array
    {
        return [
            'biz' => $this->biz,
            'degraded_keys' => $this->degradedKeys,
            'keys_used' => $this->keysUsed,
        ];
    }
}
