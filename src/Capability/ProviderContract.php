<?php

namespace Paganini\Capability;

interface ProviderContract
{
    public function key(): string;

    public function supports(array $context): bool;

    /**
     * @return array<string, mixed>
     */
    public function fetch(array $subject, array $context): array;
}
