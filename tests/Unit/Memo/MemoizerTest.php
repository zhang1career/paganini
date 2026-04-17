<?php

declare(strict_types=1);

namespace Tests\Unit\Memo;

use PHPUnit\Framework\TestCase;
use Paganini\Memo\ArrayMemoStore;
use Paganini\Memo\Memoizer;

final class MemoizerTest extends TestCase
{
    public function testGetOrComputeCaches(): void
    {
        $store = new ArrayMemoStore();
        $m = new Memoizer($store);
        $calls = 0;
        $v1 = $m->getOrCompute('k', 60, function () use (&$calls) {
            ++$calls;

            return 'a';
        });
        $v2 = $m->getOrCompute('k', 60, function () use (&$calls) {
            ++$calls;

            return 'b';
        });
        self::assertSame('a', $v1);
        self::assertSame('a', $v2);
        self::assertSame(1, $calls);
    }
}
