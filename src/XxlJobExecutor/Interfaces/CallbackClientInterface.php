<?php

namespace Paganini\XxlJobExecutor\Interfaces;

/**
 * Callback Client Interface
 *
 * Sends callback requests to job scheduler
 */
interface CallbackClientInterface
{
    /**
     * Send callback request to scheduler
     *
     * @param int $logId Current scheduling log ID
     * @param int $handleCode Execution result code (200=success, 500=failure, 502=timeout)
     * @param string $handleMsg Execution result message
     * @return bool True if callback was sent successfully, false otherwise
     */
    public function sendCallback(int $logId, int $handleCode, string $handleMsg): bool;
}

