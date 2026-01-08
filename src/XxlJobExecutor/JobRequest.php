<?php

namespace Paganini\XxlJobExecutor;

/**
 * Job Request DTO
 *
 * Encapsulates job execution request parameters
 */
readonly class JobRequest
{
    /**
     * @param int $jobId Job ID
     * @param string $executorHandler Job identifier
     * @param mixed $executorParams Job parameters
     * @param int $logId Current scheduling log ID
     */
    public function __construct(
        public int $jobId,
        public string $executorHandler,
        public mixed $executorParams,
        public int $logId
    ) {
    }

    /**
     * Create from array (e.g., from HTTP request)
     *
     * @param array<string, mixed> $data Request data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            jobId: (int)$data['jobId'],
            executorHandler: (string)$data['executorHandler'],
            executorParams: $data['executorParams'] ?? null,
            logId: (int)$data['logId']
        );
    }
}

