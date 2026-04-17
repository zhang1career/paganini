<?php

declare(strict_types=1);

namespace Tests\Unit\ServiceDiscovery;

use PHPUnit\Framework\TestCase;
use Paganini\ServiceDiscovery\RedisServiceUriResolver;
use Paganini\ServiceDiscovery\ServiceUrlSpecifier;
use Tests\Unit\Redis\FakeRedisStringClient;

final class ServiceUrlSpecifierTest extends TestCase
{
    public function testNoPlaceholderReturnsUrl(): void
    {
        $r = new RedisServiceUriResolver(new FakeRedisStringClient([]));
        self::assertSame('http://example.com', ServiceUrlSpecifier::specifyHost('http://example.com', $r));
    }

    public function testReplacesFirstHostPlaceholder(): void
    {
        $redis = new FakeRedisStringClient(['svc' => 'h1:1,h2:2']);
        $r = new RedisServiceUriResolver($redis);
        $url = ServiceUrlSpecifier::specifyHost('http://{{svc}}/api', $r, null);
        self::assertContains($url, ['http://h1:1/api', 'http://h2:2/api']);
    }

    public function testIndexForwarded(): void
    {
        $redis = new FakeRedisStringClient(['svc' => 'h1:1,h2:2']);
        $r = new RedisServiceUriResolver($redis);
        $url = ServiceUrlSpecifier::specifyHost('http://{{svc}}/api', $r, 1);
        self::assertSame('http://h2:2/api', $url);
    }
}
