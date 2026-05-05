<?php

declare(strict_types=1);

namespace Paganini\Batch\Persistence;

use PDO;
use Paganini\Batch\Contracts\BatchJobRepositoryContract;
use Paganini\Batch\DTO\BatchItem;
use Paganini\Batch\DTO\BatchJobRecord;
use Paganini\Batch\DTO\ItemOutcome;
use Paganini\Batch\Enums\ItemStatus;
use Paganini\Batch\Enums\JobStatus;
use Paganini\Batch\Exceptions\BatchJobNotFoundException;
use RuntimeException;

/**
 * Default reference repository backed by a single jobs table whose name and
 * column names are injected by the consumer at construction time. The expected
 * schema (consumer-owned, see project docs) is:
 *
 *   {jobsTable} (
 *     id            BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
 *     biz_key       VARCHAR(128) UNIQUE NOT NULL,
 *     payload       TEXT NULL,                 -- JSON-encoded
 *     total         INT UNSIGNED NOT NULL DEFAULT 0,
 *     cursor        INT UNSIGNED NOT NULL DEFAULT 0,
 *     success_count INT UNSIGNED NOT NULL DEFAULT 0,
 *     failure_count INT UNSIGNED NOT NULL DEFAULT 0,
 *     status        TINYINT UNSIGNED NOT NULL DEFAULT 0,
 *     last_error    TEXT NULL,
 *     ct            BIGINT UNSIGNED NOT NULL DEFAULT 0,    -- millis
 *     ut            BIGINT UNSIGNED NOT NULL DEFAULT 0
 *   )
 *
 * If your application uses different column names, pass overrides via
 * {@see $columns}. Per-item audit rows (success/failure history) are not
 * persisted by this default repo; consumers that need them can subclass and
 * extend {@see recordOutcome}.
 */
class PdoBatchJobRepository implements BatchJobRepositoryContract
{
    /**
     * Library-reserved key inside the persisted {@code payload} JSON used to round-trip the
     * scheduled items list. Choosing an underscore-prefixed name lets consumers freely use
     * any other top-level keys for their diagnostic payload without collision risk.
     */
    private const ITEMS_PAYLOAD_KEY = '__paganini_items__';

    /**
     * @param  array<string, string>  $columns  Override default column names; missing keys fall back to defaults.
     */
    public function __construct(
        protected readonly PDO $pdo,
        protected readonly string $jobsTable,
        protected array $columns = [],
    ) {
        $this->columns = array_merge([
            'id' => 'id',
            'biz_key' => 'biz_key',
            'payload' => 'payload',
            'total' => 'total',
            'cursor' => 'cursor',
            'success_count' => 'success_count',
            'failure_count' => 'failure_count',
            'status' => 'status',
            'last_error' => 'last_error',
            'ct' => 'ct',
            'ut' => 'ut',
        ], $this->columns);
    }

    public function create(string $bizKey, array $payload, array $items): BatchJobRecord
    {
        $now = $this->nowMillis();
        $totalItems = count($items);
        $sql = sprintf(
            'INSERT INTO %s (%s, %s, %s, %s, %s, %s) VALUES (:biz_key, :payload, :total, :status, :ct, :ut)',
            $this->q($this->jobsTable),
            $this->c('biz_key'),
            $this->c('payload'),
            $this->c('total'),
            $this->c('status'),
            $this->c('ct'),
            $this->c('ut'),
        );
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':biz_key' => $bizKey,
            ':payload' => json_encode(
                $this->mergePayloadWithItems($payload, $items),
                JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
            ),
            ':total' => $totalItems,
            ':status' => JobStatus::Running->value,
            ':ct' => $now,
            ':ut' => $now,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $row = $this->find($id);
        if ($row === null) {
            throw new RuntimeException('Inserted batch job row not found.');
        }

        return $row;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @param  list<BatchItem>  $items
     * @return array<string, mixed>
     */
    private function mergePayloadWithItems(array $payload, array $items): array
    {
        $serializedItems = [];
        foreach ($items as $item) {
            $serializedItems[] = ['ref' => $item->ref, 'payload' => $item->payload];
        }
        $payload[self::ITEMS_PAYLOAD_KEY] = $serializedItems;

        return $payload;
    }

    public function find(int $jobId): ?BatchJobRecord
    {
        $stmt = $this->pdo->prepare(sprintf(
            'SELECT * FROM %s WHERE %s = :id LIMIT 1',
            $this->q($this->jobsTable),
            $this->c('id'),
        ));
        $stmt->execute([':id' => $jobId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function findByBizKey(string $bizKey): ?BatchJobRecord
    {
        $stmt = $this->pdo->prepare(sprintf(
            'SELECT * FROM %s WHERE %s = :biz_key ORDER BY %s DESC LIMIT 1',
            $this->q($this->jobsTable),
            $this->c('biz_key'),
            $this->c('id'),
        ));
        $stmt->execute([':biz_key' => $bizKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }

        return $this->hydrate($row);
    }

    public function recordOutcome(int $jobId, int $cursorAfter, ItemOutcome $outcome): void
    {
        $now = $this->nowMillis();
        $bumpSuccess = $outcome->status === ItemStatus::Success ? 1 : 0;
        $bumpFailure = $outcome->status === ItemStatus::Failure ? 1 : 0;

        $sql = sprintf(
            'UPDATE %s SET %s = :cursor, '
            .'%s = %s + :bump_success, '
            .'%s = %s + :bump_failure, '
            .'%s = COALESCE(:last_error, %s), '
            .'%s = :ut '
            .'WHERE %s = :id',
            $this->q($this->jobsTable),
            $this->c('cursor'),
            $this->c('success_count'),
            $this->c('success_count'),
            $this->c('failure_count'),
            $this->c('failure_count'),
            $this->c('last_error'),
            $this->c('last_error'),
            $this->c('ut'),
            $this->c('id'),
        );
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':cursor', $cursorAfter, PDO::PARAM_INT);
        $stmt->bindValue(':bump_success', $bumpSuccess, PDO::PARAM_INT);
        $stmt->bindValue(':bump_failure', $bumpFailure, PDO::PARAM_INT);
        $stmt->bindValue(':last_error', $outcome->error, $outcome->error === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':ut', $now, PDO::PARAM_INT);
        $stmt->bindValue(':id', $jobId, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() < 1) {
            throw new BatchJobNotFoundException('Batch job not found: '.$jobId);
        }
    }

    public function markStatus(int $jobId, JobStatus $status, ?string $lastError = null): void
    {
        $sql = sprintf(
            'UPDATE %s SET %s = :status, %s = COALESCE(:last_error, %s), %s = :ut WHERE %s = :id',
            $this->q($this->jobsTable),
            $this->c('status'),
            $this->c('last_error'),
            $this->c('last_error'),
            $this->c('ut'),
            $this->c('id'),
        );
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':status', $status->value, PDO::PARAM_INT);
        $stmt->bindValue(':last_error', $lastError, $lastError === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':ut', $this->nowMillis(), PDO::PARAM_INT);
        $stmt->bindValue(':id', $jobId, PDO::PARAM_INT);
        $stmt->execute();
        if ($stmt->rowCount() < 1) {
            throw new BatchJobNotFoundException('Batch job not found: '.$jobId);
        }
    }

    /**
     * @param  array<string, mixed>  $row
     */
    protected function hydrate(array $row): BatchJobRecord
    {
        $payloadRaw = $row[$this->columns['payload']] ?? null;
        $rawPayload = [];
        if (is_string($payloadRaw) && $payloadRaw !== '') {
            $decoded = json_decode($payloadRaw, true);
            if (is_array($decoded)) {
                /** @var array<string, mixed> $rawPayload */
                $rawPayload = $decoded;
            }
        }

        $items = [];
        if (isset($rawPayload[self::ITEMS_PAYLOAD_KEY]) && is_array($rawPayload[self::ITEMS_PAYLOAD_KEY])) {
            foreach ($rawPayload[self::ITEMS_PAYLOAD_KEY] as $entry) {
                if (! is_array($entry) || ! isset($entry['ref'])) {
                    continue;
                }
                $items[] = new BatchItem(
                    ref: (string) $entry['ref'],
                    payload: is_array($entry['payload'] ?? null) ? $entry['payload'] : [],
                );
            }
        }
        unset($rawPayload[self::ITEMS_PAYLOAD_KEY]);

        return new BatchJobRecord(
            id: (int) $row[$this->columns['id']],
            bizKey: (string) $row[$this->columns['biz_key']],
            payload: $rawPayload,
            items: $items,
            total: (int) $row[$this->columns['total']],
            cursor: (int) $row[$this->columns['cursor']],
            successCount: (int) $row[$this->columns['success_count']],
            failureCount: (int) $row[$this->columns['failure_count']],
            status: JobStatus::from((int) $row[$this->columns['status']]),
            lastError: $row[$this->columns['last_error']] === null ? null : (string) $row[$this->columns['last_error']],
            ct: (int) $row[$this->columns['ct']],
            ut: (int) $row[$this->columns['ut']],
        );
    }

    protected function nowMillis(): int
    {
        return (int) (microtime(true) * 1000);
    }

    protected function q(string $identifier): string
    {
        return '`'.str_replace('`', '``', $identifier).'`';
    }

    protected function c(string $logical): string
    {
        return $this->q($this->columns[$logical]);
    }
}
