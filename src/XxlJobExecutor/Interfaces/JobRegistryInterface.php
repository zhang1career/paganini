<?php

namespace Paganini\XxlJobExecutor\Interfaces;

/**
 * Job Registry Interface
 *
 * Manages registration and retrieval of job handlers
 */
interface JobRegistryInterface
{
    /**
     * Register a job handler
     *
     * @param string $handler Job identifier
     * @param array{0: class-string, 1: string} $callable Callable object, format: [ClassName::class, 'methodName']
     * @return void
     */
    public function register(string $handler, array $callable): void;

    /**
     * Get registered Job
     *
     * @param string $handler Job identifier
     * @return array{0: class-string, 1: string}|null Returns [ClassName::class, 'methodName'], or null if not found
     */
    public function getJob(string $handler);

    /**
     * Check if Job is registered
     *
     * @param string $handler Job identifier
     * @return bool
     */
    public function hasJob(string $handler): bool;

    /**
     * Get all registered Jobs
     *
     * @return array<string, array{0: class-string, 1: string}>
     */
    public function getAllJobs(): array;
}

