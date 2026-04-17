<?php

declare(strict_types=1);

namespace Paganini\ServiceDiscovery;

use Paganini\ServiceDiscovery\Contracts\RedisStringClient;
use Paganini\ServiceDiscovery\Contracts\ServiceUriResolverInterface;
use Paganini\ServiceDiscovery\Exceptions\ServiceUriResolutionException;
use function count;

/**
 * Reads comma-separated instance lists from Redis (GET/MGET), uniform random or index mod selection.
 *
 * List order follows the registration string left-to-right.
 */
final class RedisServiceUriResolver implements ServiceUriResolverInterface
{
    public function __construct(
        private readonly RedisStringClient $redis,
        private readonly string $keyPrefix = '',
    ) {
    }

    public function resolve(string $serviceName, ?int $index = null): string
    {
        $raw = $this->redis->get($this->prefixedKey($serviceName));
        if ($raw === false || $raw === '') {
            throw new ServiceUriResolutionException('No data found for service: ' . $serviceName);
        }

        $list = ServiceUriList::parseCommaSeparated($raw);
        if ($list === []) {
            throw new ServiceUriResolutionException('Empty instance list for service: ' . $serviceName);
        }

        return ServiceUriList::pick($list, $index);
    }

    public function resolveMany(array $serviceNames, ?int $index = null): array
    {
        $unique = $this->uniquePreserveOrder($serviceNames);
        if ($unique === []) {
            return [];
        }

        $keys = array_map(fn (string $n): string => $this->prefixedKey($n), $unique);
        $values = $this->redis->mget($keys);
        if (count($values) !== count($unique)) {
            throw new ServiceUriResolutionException('Redis mget length mismatch.');
        }

        $out = [];
        foreach ($unique as $i => $name) {
            $raw = $values[$i] ?? false;
            if ($raw === false || $raw === '') {
                throw new ServiceUriResolutionException('No data found for service: ' . $name);
            }
            $list = ServiceUriList::parseCommaSeparated($raw);
            if ($list === []) {
                throw new ServiceUriResolutionException('Empty instance list for service: ' . $name);
            }
            $out[$name] = ServiceUriList::pick($list, $index);
        }

        return $out;
    }

    /**
     * @param list<string> $names
     *
     * @return list<string>
     */
    private function uniquePreserveOrder(array $names): array
    {
        $seen = [];
        $out = [];
        foreach ($names as $n) {
            if (isset($seen[$n])) {
                continue;
            }
            $seen[$n] = true;
            $out[] = $n;
        }

        return $out;
    }

    private function prefixedKey(string $serviceName): string
    {
        return $this->keyPrefix . $serviceName;
    }
}
