<?php

declare(strict_types=1);

namespace Paganini\Memo;

/**
 * Request- or process-local memo store (PHP array).
 */
final class ArrayMemoStore implements MemoStoreInterface
{
    /** @var array<string, array{value: mixed, expiresAt: int}> */
    private array $entries = [];

    public function get(string $key): ?array
    {
        return $this->entries[$key] ?? null;
    }

    public function set(string $key, mixed $value, int $ttlSeconds): void
    {
        $ttl = max(0, $ttlSeconds);
        $this->entries[$key] = ['value' => $value, 'expiresAt' => time() + $ttl];
    }
}
