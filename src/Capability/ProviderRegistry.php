<?php

namespace Paganini\Capability;

use InvalidArgumentException;

class ProviderRegistry
{
    /** @var array<ProviderContract> */
    private array $providers = [];

    /**
     * @param array<ProviderContract> $providers
     */
    public function __construct(array $providers)
    {
        foreach ($providers as $p) {
            if (!$p instanceof ProviderContract) {
                throw new InvalidArgumentException('Each item must implement ProviderContract.');
            }
            $this->providers[] = $p;
        }
    }

    /**
     * @return array<ProviderContract>
     */
    public function matched(array $context): array
    {
        return array_values(array_filter(
            $this->providers,
            fn (ProviderContract $p): bool => $p->supports($context)
        ));
    }
}
