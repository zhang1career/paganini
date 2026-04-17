<?php

declare(strict_types=1);

namespace Paganini\Memo;

use RuntimeException;
use function function_exists;
use function is_array;

/**
 * Cross-request memo within the same FPM worker when ext-apcu is available.
 */
final class ApcuMemoStore implements MemoStoreInterface
{
    private const PREFIX = 'paganini.memo.';

    public function __construct(
        private readonly string $namespace = 'default',
    ) {
        if (!function_exists('apcu_fetch')) {
            throw new RuntimeException('ext-apcu is required for ApcuMemoStore.');
        }
    }

    public function get(string $key): ?array
    {
        $raw = apcu_fetch($this->apcuKey($key), $success);
        if (!$success || !is_array($raw) || !isset($raw['value'], $raw['expiresAt'])) {
            return null;
        }

        return $raw;
    }

    public function set(string $key, mixed $value, int $ttlSeconds): void
    {
        $ttl = max(0, $ttlSeconds);
        $expiresAt = time() + $ttl;
        apcu_store($this->apcuKey($key), ['value' => $value, 'expiresAt' => $expiresAt], $ttl);
    }

    private function apcuKey(string $key): string
    {
        return self::PREFIX . $this->namespace . '.' . $key;
    }
}
