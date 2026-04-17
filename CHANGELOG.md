# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed

- **Breaking:** **`MemoStoreInterface::set`**: third argument is now **`$ttlSeconds`** (seconds from now), not an absolute Unix timestamp. Callers that previously passed `time() + $ttl` should pass only `$ttl`. `get()` still returns entries with absolute `expiresAt`.

## [0.4.0] - 2026-04-17

### Added

- **Service discovery** (`Paganini\ServiceDiscovery`):
  - `Contracts\RedisStringClient` — narrow `get` / `mget` contract (`string|false` like phpredis).
  - `RedisServiceUriResolver` — comma-separated instance lists, `?int $index` (`null` = uniform random, non-null = `index mod n`), `resolveMany` with shared index and MGET.
  - `Exceptions\ServiceUriResolutionException` — missing or empty registration data.
  - `Contracts\ServiceUriResolverInterface` — resolver contract.
  - `ServiceUriList` — parse comma-separated lists and pick random / by index.
  - `ServiceUrlSpecifier::specifyHost` — Fusio-compatible `://{{key}}` replacement (first match).
- **Memo** (`Paganini\Memo`):
  - `MemoStoreInterface`, `ArrayMemoStore`, `ApcuMemoStore` (requires `ext-apcu`).
  - `Memoizer::getOrCompute` — process-local TTL cache.
  - `CacheKeyGeneratorInterface`, `DefaultCacheKeyGenerator`, `CacheKeyGenerator` — stable keys with `hash('xxh3', …)` (requires `xxh3` in `hash_algos()`).

### Changed

- `composer.json` suggests `ext-apcu`, `ext-redis`, `ext-hash`.

## [0.3.2] and earlier

See git history.
