<?php

namespace Paganini\XxlJobExecutor;

use Paganini\XxlJobExecutor\Interfaces\FileLockInterface;

/**
 * Job File Lock
 *
 * Framework-agnostic wrapper for file lock operations
 * Delegates to FileLockInterface implementation
 */
class JobFileLock
{
    public function __construct(
        private readonly FileLockInterface $fileLock
    ) {
    }

    /**
     * Create a job file lock
     *
     * @param string $jobId Job ID
     * @return string|null Returns file path if created successfully, null otherwise
     */
    public function create(string $jobId): ?string
    {
        return $this->fileLock->create($jobId);
    }

    /**
     * Delete a job file lock
     *
     * @param string $jobId Job ID
     * @return bool True if deleted successfully, false otherwise
     */
    public function delete(string $jobId): bool
    {
        return $this->fileLock->delete($jobId);
    }

    /**
     * Check if a job file lock exists
     *
     * @param string $jobId Job ID
     * @return bool True if lock exists, false otherwise
     */
    public function exists(string $jobId): bool
    {
        return $this->fileLock->exists($jobId);
    }
}

