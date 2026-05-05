<?php

declare(strict_types=1);

namespace Tests\Unit\Batch;

use PDO;
use Paganini\Batch\DTO\BatchItem;
use Paganini\Batch\DTO\ItemOutcome;
use Paganini\Batch\Enums\JobStatus;
use Paganini\Batch\Persistence\PdoBatchJobRepository;
use Tests\TestCase;

class PdoBatchJobRepositoryTest extends TestCase
{
    private PDO $pdo;

    private PdoBatchJobRepository $repo;

    /** Custom column-name overrides confirm the table-injection contract works. */
    private array $columns = [
        'id' => 'job_id',
        'biz_key' => 'business_key',
    ];

    private string $jobsTable = 'paganini_batch_jobs_test';

    protected function setUp(): void
    {
        parent::setUp();

        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec(sprintf(
            'CREATE TABLE `%s` ('
            .'`job_id` INTEGER PRIMARY KEY AUTOINCREMENT,'
            .'`business_key` VARCHAR(128) NOT NULL UNIQUE,'
            .'`payload` TEXT NULL,'
            .'`total` INTEGER NOT NULL DEFAULT 0,'
            .'`cursor` INTEGER NOT NULL DEFAULT 0,'
            .'`success_count` INTEGER NOT NULL DEFAULT 0,'
            .'`failure_count` INTEGER NOT NULL DEFAULT 0,'
            .'`status` INTEGER NOT NULL DEFAULT 0,'
            .'`last_error` TEXT NULL,'
            .'`ct` INTEGER NOT NULL DEFAULT 0,'
            .'`ut` INTEGER NOT NULL DEFAULT 0'
            .')',
            $this->jobsTable,
        ));

        $this->repo = new PdoBatchJobRepository($this->pdo, $this->jobsTable, $this->columns);
    }

    public function test_create_then_find_round_trips_items_and_payload(): void
    {
        $items = [
            new BatchItem('order-101', ['stake' => 100]),
            new BatchItem('order-102'),
        ];
        $row = $this->repo->create('biz-1', ['game_id' => 12, 'note' => '中文'], $items);

        $this->assertGreaterThan(0, $row->id);
        $this->assertSame('biz-1', $row->bizKey);
        $this->assertSame(2, $row->total);
        $this->assertSame(0, $row->cursor);
        $this->assertSame(JobStatus::Running, $row->status);
        $this->assertSame(['game_id' => 12, 'note' => '中文'], $row->payload);
        $this->assertCount(2, $row->items);
        $this->assertSame('order-101', $row->items[0]->ref);
        $this->assertSame(['stake' => 100], $row->items[0]->payload);

        $byId = $this->repo->find($row->id);
        $byKey = $this->repo->findByBizKey('biz-1');
        $this->assertNotNull($byId);
        $this->assertNotNull($byKey);
        $this->assertSame($row->id, $byId->id);
        $this->assertSame($row->id, $byKey->id);
        // Items survive a fresh hydrate from the persisted row — required for resume.
        $this->assertCount(2, $byKey->items);
        $this->assertSame('order-101', $byKey->items[0]->ref);
        // Library-reserved key is stripped from the consumer-visible payload.
        $this->assertArrayNotHasKey('__paganini_items__', $byKey->payload);
    }

    public function test_record_outcome_advances_cursor_and_counts(): void
    {
        $job = $this->repo->create('biz-2', [], [
            new BatchItem('a'),
            new BatchItem('b'),
            new BatchItem('c'),
        ]);

        $this->repo->recordOutcome($job->id, 1, ItemOutcome::success('a'));
        $this->repo->recordOutcome($job->id, 2, ItemOutcome::failure('b', 'boom'));
        $this->repo->recordOutcome($job->id, 3, ItemOutcome::success('c'));

        $loaded = $this->repo->find($job->id);
        $this->assertNotNull($loaded);
        $this->assertSame(3, $loaded->cursor);
        $this->assertSame(2, $loaded->successCount);
        $this->assertSame(1, $loaded->failureCount);
        $this->assertSame('boom', $loaded->lastError);
    }

    public function test_mark_status(): void
    {
        $job = $this->repo->create('biz-3', [], []);
        $this->repo->markStatus($job->id, JobStatus::Completed);

        $loaded = $this->repo->find($job->id);
        $this->assertNotNull($loaded);
        $this->assertSame(JobStatus::Completed, $loaded->status);
    }
}
