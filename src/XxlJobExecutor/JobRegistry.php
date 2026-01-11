<?php

namespace Paganini\XxlJobExecutor;

use Paganini\XxlJobExecutor\Interfaces\JobRegistryInterface;

/**
 * Job Registry
 *
 * Framework-agnostic implementation of job registry
 */
class JobRegistry implements JobRegistryInterface
{
    /**
     * @var array<string, array{0: class-string, 1: string}> Registered jobs, format: ['handler' => [ClassName::class, 'methodName']]
     */
    private $jobs = [];

    /**
     * Register a job handler
     *
     * @param string $handler Job identifier
     * @param array{0: class-string, 1: string} $callable Callable object, format: [ClassName::class, 'methodName']
     * @return void
     */
    public function register(string $handler, array $callable): void
    {
        $this->jobs[$handler] = $callable;
    }

    /**
     * Get registered Job
     *
     * @param string $handler Job identifier
     * @return array{0: class-string, 1: string}|null Returns [ClassName::class, 'methodName'], or null if not found
     */
    public function getJob(string $handler)
    {
        return $this->jobs[$handler] ?? null;
    }

    /**
     * Check if Job is registered
     *
     * @param string $handler Job identifier
     * @return bool
     */
    public function hasJob(string $handler): bool
    {
        return isset($this->jobs[$handler]);
    }

    /**
     * Get all registered Jobs
     *
     * @return array<string, array{0: class-string, 1: string}>
     */
    public function getAllJobs(): array
    {
        return $this->jobs;
    }
}

