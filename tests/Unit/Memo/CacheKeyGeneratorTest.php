<?php

declare(strict_types=1);

namespace Tests\Unit\Memo;

use PHPUnit\Framework\TestCase;
use Paganini\Memo\CacheKeyGenerator;
use Paganini\Memo\DefaultCacheKeyGenerator;

final class CacheKeyGeneratorTest extends TestCase
{
    public function testAssociativeOrderIndependent(): void
    {
        $a = CacheKeyGenerator::fromAssociativeArray(['b' => 2, 'a' => 1]);
        $b = CacheKeyGenerator::fromAssociativeArray(['a' => 1, 'b' => 2]);
        self::assertSame($a, $b);
    }

    public function testNamespaceChangesHash(): void
    {
        $g = new DefaultCacheKeyGenerator();
        $a = $g->hashKey('ns1', ['x' => 1]);
        $b = $g->hashKey('ns2', ['x' => 1]);
        self::assertNotSame($a, $b);
    }

    public function testPositional(): void
    {
        $k = CacheKeyGenerator::fromPositionalList([1, 'x']);
        self::assertNotSame('', $k);
    }
}
