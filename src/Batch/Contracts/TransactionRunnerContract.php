<?php

declare(strict_types=1);

namespace Paganini\Batch\Contracts;

use Throwable;

/**
 * Runs a callable inside a database transaction. The implementation owns
 * begin/commit/rollback semantics; the executor only relies on the contract
 * "if $work throws, the transaction is rolled back; otherwise it is committed".
 *
 * Implementations may use savepoints when nested. Different runner instances
 * are required for the outer phase and inner phase if independent transactions
 * are desired (see BatchExecutor).
 */
interface TransactionRunnerContract
{
    /**
     * @template T
     *
     * @param  callable(): T  $work
     * @return T
     *
     * @throws Throwable
     */
    public function run(callable $work): mixed;
}
