<?php

declare(strict_types=1);

namespace Paganini\ServiceDiscovery\Contracts;

/**
 * Narrow Redis contract for string GET / MGET only (test doubles, phpredis, adapters).
 */
interface RedisStringClient
{
    /**
     * @return string|false false if key does not exist
     */
    public function get(string $key): string|false;

    /**
     * @param list<string> $keys
     *
     * @return list<string|false> one entry per key, false if missing
     */
    public function mget(array $keys): array;
}
