<?php

declare(strict_types=1);

namespace Paganini\ServiceDiscovery\Contracts;

interface ServiceUriResolverInterface
{
    /**
     * Resolve a single logical service name to one instance URI (host:port or scheme://… fragment).
     *
     * @param int|null $index null = uniform random; non-null = index mod list size (list order = registration string left-to-right)
     */
    public function resolve(string $serviceName, ?int $index = null): string;

    /**
     * @param list<string> $serviceNames deduplicated internally; one shared $index for all services
     *
     * @return array<string, string> service name => selected instance URI
     */
    public function resolveMany(array $serviceNames, ?int $index = null): array;
}
