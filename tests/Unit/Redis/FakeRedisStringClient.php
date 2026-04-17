<?php

declare(strict_types=1);

namespace Tests\Unit\Redis;

use Paganini\ServiceDiscovery\Contracts\RedisStringClient;

/**
 * In-memory fake for {@see RedisStringClient}.
 */
final class FakeRedisStringClient implements RedisStringClient
{
    /**
     * @param array<string, string> $keyToValue
     */
    public function __construct(
        private array $keyToValue,
    ) {
    }

    public function get(string $key): string|false
    {
        return $this->keyToValue[$key] ?? false;
    }

    public function mget(array $keys): array
    {
        $out = [];
        foreach ($keys as $key) {
            $out[] = $this->keyToValue[$key] ?? false;
        }

        return $out;
    }
}
