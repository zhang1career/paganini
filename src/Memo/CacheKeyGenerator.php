<?php

declare(strict_types=1);

namespace Paganini\Memo;

use JsonException;
use RuntimeException;
use function in_array;

/**
 * Stable cache keys for memoization (non-cryptographic).
 */
final class CacheKeyGenerator
{
    /**
     * @param array<string, mixed> $namedArgs
     * @throws JsonException
     */
    public static function fromAssociativeArray(array $namedArgs): string
    {
        return (new DefaultCacheKeyGenerator())->hashKey('', $namedArgs);
    }

    /**
     * @param list<mixed> $positionalArgs
     * @throws JsonException
     */
    public static function fromPositionalList(array $positionalArgs): string
    {
        self::assertXxh3Available();
        $payload = json_encode($positionalArgs, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);

        return hash('xxh3', $payload);
    }

    public static function assertXxh3Available(): void
    {
        if (!in_array('xxh3', hash_algos(), true)) {
            throw new RuntimeException('hash algorithm xxh3 is required (ext-hash, PHP 8.1+).');
        }
    }
}
