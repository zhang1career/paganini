<?php

namespace Paganini\XxlJobExecutor;

use Paganini\XxlJobExecutor\Interfaces\CallbackClientInterface;
use Paganini\XxlJobExecutor\Interfaces\FileLockInterface;

/**
 * Job Execution Handler
 *
 * Handles the actual execution of a job in the queue:
 * 1. Execute job callable
 * 2. Send callback to scheduler
 * 3. Clean up file lock
 */
class JobExecutionHandler
{
    public function __construct(
        private readonly CallbackClientInterface $callbackClient,
        private readonly FileLockInterface $fileLock
    ) {
    }

    /**
     * Execute job and handle callback
     *
     * @param array{0: class-string, 1: string} $callable Job callable
     * @param mixed $params Job parameters
     * @param int $logId Current scheduling log ID
     * @param string $jobId Job ID (for file lock cleanup)
     * @return void
     */
    public function execute(
        array $callable,
        mixed $params,
        int $logId,
        string $jobId
    ): void {
        // Execute job
        [$success, $data, $errorMessage] = call_user_func($callable, $params);

        // Clean up file lock
        $this->fileLock->delete($jobId);

        // Send callback
        if (!$success) {
            $this->callbackClient->sendCallback($logId, 500, $errorMessage ?? 'Job execution failed');
            return;
        }

        $handleMsg = json_encode($data);
        $this->callbackClient->sendCallback($logId, 200, $handleMsg);
    }
}

