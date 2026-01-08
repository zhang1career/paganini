<?php

namespace Paganini\XxlJobExecutor\Interfaces;

/**
 * File Lock Interface
 *
 * Manages job file locks for tracking job execution
 */
interface FileLockInterface
{
    /**
     * Create a job file lock
     *
     * @param string $jobId Job ID
     * @return string|null Returns file path if created successfully, null otherwise
     */
    public function create(string $jobId): ?string;

    /**
     * Delete a job file lock
     *
     * @param string $jobId Job ID
     * @return bool True if deleted successfully, false otherwise
     */
    public function delete(string $jobId): bool;

    /**
     * Check if a job file lock exists
     *
     * @param string $jobId Job ID
     * @return bool True if lock exists, false otherwise
     */
    public function exists(string $jobId): bool;
}

