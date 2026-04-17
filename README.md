# Paganini

Utility library for PHP: aggregation helpers, env loading, **service discovery from Redis**, and **process-local memoization**. No Laravel/Symfony framework dependency in core APIs.

## Requirements

- PHP **8.2+**
- **`hash` algorithm `xxh3`** available (typical PHP builds with `ext-hash`; `CacheKeyGenerator::assertXxh3Available()` validates at runtime).

## Installation

```bash
composer require zhang1career/paganini
```

Optional extensions (see `composer suggest`):

- **`ext-apcu`** — `ApcuMemoStore` for memo shared across requests in the same FPM worker.
- **`ext-redis`** — use phpredis (or wrap Predis) behind `Paganini\ServiceDiscovery\Contracts\RedisStringClient`.

## Service discovery (Redis)

Register comma-separated instances per logical service name (same idea as Fusio `ServiceRegister`):

- Redis value: `host1:8080,host2:8080` (order defines index `0..n-1` for `index mod`).

```php
use Paganini\ServiceDiscovery\RedisServiceUriResolver;
use Paganini\ServiceDiscovery\Contracts\RedisStringClient;

// Inject your adapter: phpredis, Predis, etc.
$redis = /* RedisStringClient */;
$resolver = new RedisServiceUriResolver($redis, keyPrefix: '');

$uri = $resolver->resolve('my_service', null);        // uniform random
$uri = $resolver->resolve('my_service', 7);           // deterministic: 7 mod n

$map = $resolver->resolveMany(['a', 'b'], 1);         // one shared index for all names
```

**URL placeholders** (Fusio-compatible, first `://{{key}}` only):

```php
use Paganini\ServiceDiscovery\ServiceUrlSpecifier;

$url = ServiceUrlSpecifier::specifyHost('http://{{my_service}}/v1', $resolver, null);
```

## Memoization (no reflection)

```php
use Paganini\Memo\ArrayMemoStore;
use Paganini\Memo\Memoizer;

$memo = new Memoizer(new ArrayMemoStore());
$value = $memo->getOrCompute('cache-key', 60, fn () => $resolver->resolve('svc', null));
```

Use **`ApcuMemoStore`** only when `ext-apcu` is installed.

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## Roadmap / backlog

See [docs/TODO.md](docs/TODO.md).

## License

MIT
