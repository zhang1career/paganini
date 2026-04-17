<?php

declare(strict_types=1);

namespace Paganini\Memo;

/**
 * Process-local TTL cache; no reflection.
 */
final class Memoizer
{
    public function __construct(
        private readonly MemoStoreInterface $store,
    ) {
    }

    /**
     * @template T
     *
     * @param non-empty-string $key
     * @param callable(): T $compute
     *
     * @return T
     */
    public function getOrCompute(string $key, int $ttlSeconds, callable $compute): mixed
    {
        $now = time();
        $cached = $this->store->get($key);
        if ($cached !== null && $cached['expiresAt'] > $now) {
            return $cached['value'];
        }
        $value = $compute();
        $this->store->set($key, $value, $ttlSeconds);

        return $value;
    }
}
