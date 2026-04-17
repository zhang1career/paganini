<?php

declare(strict_types=1);

namespace Paganini\Memo;

use JsonException;

/**
 * xxh3 over namespace + sorted named args (JSON).
 */
final class DefaultCacheKeyGenerator implements CacheKeyGeneratorInterface
{
    /**
     * @throws JsonException
     */
    public function hashKey(string $namespace, array $namedArgs): string
    {
        CacheKeyGenerator::assertXxh3Available();
        ksort($namedArgs);
        $encoded = json_encode($namedArgs, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        $payload = $namespace === '' ? $encoded : $namespace . "\0" . $encoded;

        return hash('xxh3', $payload);
    }
}
