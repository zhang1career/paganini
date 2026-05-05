<?php

declare(strict_types=1);

namespace Paganini\Batch\Persistence;

use PDO;
use Paganini\Batch\Contracts\TransactionRunnerContract;
use Throwable;

/**
 * Plain-PDO transaction runner. Suitable for the inner phase (independent
 * transactions per item). For Laravel applications a thin adapter that
 * delegates to {@code Illuminate\Support\Facades\DB::transaction} is usually a
 * better fit because it integrates with the framework connection pool — the
 * consumer can implement that on the application side and inject it.
 */
final readonly class PdoTransactionRunner implements TransactionRunnerContract
{
    public function __construct(private PDO $pdo) {}

    public function run(callable $work): mixed
    {
        $this->pdo->beginTransaction();
        try {
            $result = $work();
            $this->pdo->commit();

            return $result;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $e;
        }
    }
}
