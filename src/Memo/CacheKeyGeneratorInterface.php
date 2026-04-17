<?php

declare(strict_types=1);

namespace Paganini\Memo;

/**
 * Stable cache keys for memoization (non-cryptographic).
 *
 * @phpstan-type NamedArgs array<string, mixed>
 */
interface CacheKeyGeneratorInterface
{
    /**
     * @param NamedArgs $namedArgs
     */
    public function hashKey(string $namespace, array $namedArgs): string;
}
