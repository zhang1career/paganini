# Changelog

All notable changes to this project are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [0.6.0] - 2026-05-05

### Added

- **Batch** resume support (`Paganini\Batch\Execution\BatchExecutor`):
  - When `execute($bizKey, …)` is called and the latest job for `$bizKey` is still `JobStatus::Running` (i.e. a previous attempt crashed before writing a terminal status), the executor now **resumes** the job instead of throwing: it skips the outer phase entirely (state already committed) and replays only the unfinished tail starting at `cursor + 1`.
  - The `BatchPlanProviderContract` passed to `execute()` is **not** invoked on resume; consumers therefore do not need to make their plan provider re-runnable.
  - Items are persisted on the job header at creation time and round-tripped via the repository so resume works without an items-detail table.

### Changed

- **Breaking** — `BatchJobRepositoryContract::create` signature: `create(string $bizKey, array $payload, int $totalItems)` → `create(string $bizKey, array $payload, array $items)`. Total derives from `count($items)`. Implementations should persist the items list (the reference `PdoBatchJobRepository` stores it inside the existing `payload` JSON column under the library-reserved `__paganini_items__` key).
- **Breaking** — `BatchJobRecord` now carries an `items: list<BatchItem>` field populated by the repository on hydrate; in-memory / custom repositories must round-trip it for resume to work.

### Deprecated

- `BatchAlreadyRunningException` — no longer raised by the executor (resume supersedes it). Class kept for backward-compatible `catch` blocks.

## [0.5.0] - 2026-05-05

### Added

- **Batch** (`Paganini\Batch`): generic "big task / small tasks" runner.
  - `Contracts\BatchPlanProviderContract` — outer-phase callable that produces a `BatchPlan` (header payload + ordered items) atomically with big-task state changes.
  - `Contracts\BatchItemHandlerContract` — inner-phase callable invoked once per item inside its own transaction.
  - `Contracts\BatchJobRepositoryContract` — persistence contract; the consumer constructs the implementation with the table name(s) it owns (table-name injection).
  - `Contracts\TransactionRunnerContract` — minimal runner abstraction; a default `PdoTransactionRunner` ships with the library; framework adapters (e.g. Laravel `DB`) live in the consumer.
  - `Execution\BatchExecutor` — orchestrates outer-phase (job header committed in one transaction) then per-item inner-phase (each in its own transaction); item failures are recorded but do not abort the run.
  - `Persistence\PdoBatchJobRepository` — reference single-table repository. Column names overridable via constructor.
  - DTOs: `BatchItem`, `BatchPlan`, `BatchJobRecord`, `ItemOutcome`, `BatchRunResult`. Enums: `JobStatus`, `ItemStatus`. Exceptions: `BatchAlreadyRunningException`, `BatchJobNotFoundException`.

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
