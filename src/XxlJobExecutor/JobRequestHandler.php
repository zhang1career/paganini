<?php

namespace Paganini\XxlJobExecutor;

use Paganini\XxlJobExecutor\Interfaces\JobRegistryInterface;
use Paganini\POJOs\Response;

/**
 * Job Request Handler
 *
 * Orchestrates the job request processing flow:
 * 1. Create file lock
 * 2. Get job from registry
 * 3. Validate job exists
 * 4. Return job information for dispatch
 *
 * Actual job execution happens asynchronously via queue
 */
class JobRequestHandler
{
    /**
     * @var JobRegistryInterface
     */
    private $jobRegistry;

    /**
     * @var JobFileLock
     */
    private $fileLock;

    public function __construct(JobRegistryInterface $jobRegistry, JobFileLock $fileLock)
    {
        $this->jobRegistry = $jobRegistry;
        $this->fileLock = $fileLock;
    }

    /**
     * Handle job execution request
     *
     * This method handles the initial request processing:
     * - Creates file lock
     * - Validates job exists
     * - Returns job information for dispatch
     *
     * Actual job execution happens asynchronously via queue
     *
     * @param JobRequest $request Job request
     * @return Response Response with job information or error
     */
    public function handle(JobRequest $request): Response
    {
        // Create job file lock
        $jobFilePath = $this->fileLock->create((string)$request->getJobId());
        if ($jobFilePath === null) {
            return Response::fail('creating job file failed! filepath=' . $this->buildJobFilePath((string)$request->getJobId()));
        }

        // Get job from registry
        $job = $this->jobRegistry->getJob($request->getExecutorHandler());
        if (!$job) {
            // Clean up file lock on error
            $this->fileLock->delete((string)$request->getJobId());
            return Response::fail('executor handler not registered! handler=' . $request->getExecutorHandler());
        }

        // Return success with job information
        // The actual execution will be handled by the queue system
        return Response::success([
            'job' => $job,
            'params' => $request->getExecutorParams(),
            'logId' => $request->getLogId(),
            'filePath' => $jobFilePath,
        ]);
    }

    /**
     * Build job file path (for error messages)
     *
     * @param string $jobId Job ID
     * @return string File path
     */
    private function buildJobFilePath(string $jobId): string
    {
        // This is just for error messages, actual path is handled by FileLockInterface implementation
        return 'jobs/' . $jobId . '.job';
    }
}
