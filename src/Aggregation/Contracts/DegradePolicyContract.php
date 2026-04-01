<?php

namespace Paganini\Aggregation\Contracts;

interface DegradePolicyContract
{
    public function strategy(): string;

    public function shouldFailFast(): bool;

    public function degradedValue(string $key): mixed;
}
