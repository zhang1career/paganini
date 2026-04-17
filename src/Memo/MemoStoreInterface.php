<?php

declare(strict_types=1);

namespace Paganini\Memo;

/**
 * @phpstan-type CachedEntry array{value: mixed, expiresAt: int}
 */
interface MemoStoreInterface
{
    /**
     * @param string $key
     * @return CachedEntry|null
     */
    public function get(string $key): ?array;

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttlSeconds seconds until expiry (from now); negative values are treated as 0
     */
    public function set(string $key, mixed $value, int $ttlSeconds): void;
}
