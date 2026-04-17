<?php

declare(strict_types=1);

namespace Tests\Unit\ServiceDiscovery;

use PHPUnit\Framework\TestCase;
use Paganini\ServiceDiscovery\RedisServiceUriResolver;
use Paganini\ServiceDiscovery\Exceptions\ServiceUriResolutionException;
use Tests\Unit\Redis\FakeRedisStringClient;

final class RedisServiceUriResolverTest extends TestCase
{
    public function testResolveRandom(): void
    {
        $redis = new FakeRedisStringClient(['svc' => 'h1:1,h2:2']);
        $r = new RedisServiceUriResolver($redis);
        $uri = $r->resolve('svc', null);
        self::assertContains($uri, ['h1:1', 'h2:2']);
    }

    public function testResolveIndexed(): void
    {
        $redis = new FakeRedisStringClient(['svc' => 'h1:1,h2:2,h3:3']);
        $r = new RedisServiceUriResolver($redis);
        self::assertSame('h1:1', $r->resolve('svc', 0));
        self::assertSame('h2:2', $r->resolve('svc', 1));
        self::assertSame('h3:3', $r->resolve('svc', 2));
        self::assertSame('h1:1', $r->resolve('svc', 3));
    }

    public function testResolvePrefix(): void
    {
        $redis = new FakeRedisStringClient(['pfxsvc' => 'a:1']);
        $r = new RedisServiceUriResolver($redis, 'pfx');
        self::assertSame('a:1', $r->resolve('svc', 0));
    }

    public function testResolveMissingThrows(): void
    {
        $this->expectException(ServiceUriResolutionException::class);
        $r = new RedisServiceUriResolver(new FakeRedisStringClient([]));
        $r->resolve('missing', null);
    }

    public function testResolveManySharedIndex(): void
    {
        $redis = new FakeRedisStringClient([
            'a' => 'x:1,x:2',
            'b' => 'y:1,y:2,y:3',
        ]);
        $r = new RedisServiceUriResolver($redis);
        $out = $r->resolveMany(['a', 'b'], 1);
        self::assertSame(['a' => 'x:2', 'b' => 'y:2'], $out);
    }

    public function testResolveManyDuplicateNames(): void
    {
        $redis = new FakeRedisStringClient(['a' => 'x:1,x:2']);
        $r = new RedisServiceUriResolver($redis);
        $out = $r->resolveMany(['a', 'a'], 0);
        self::assertSame(['a' => 'x:1'], $out);
    }
}
