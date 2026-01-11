<?php

namespace Paganini\XxlJobExecutor;

/**
 * Job Request DTO
 *
 * Encapsulates job execution request parameters
 */
class JobRequest
{
    /**
     * @var int
     */
    private $jobId;

    /**
     * @var string
     */
    private $executorHandler;

    /**
     * @var mixed
     */
    private $executorParams;

    /**
     * @var int
     */
    private $logId;

    /**
     * @param int $jobId Job ID
     * @param string $executorHandler Job identifier
     * @param mixed $executorParams Job parameters
     * @param int $logId Current scheduling log ID
     */
    public function __construct(int $jobId, string $executorHandler, $executorParams, int $logId)
    {
        $this->jobId = $jobId;
        $this->executorHandler = $executorHandler;
        $this->executorParams = $executorParams;
        $this->logId = $logId;
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
            (int)$data['jobId'],
            (string)$data['executorHandler'],
            $data['executorParams'] ?? null,
            (int)$data['logId']
        );
    }

    /**
     * @return int
     */
    public function getJobId(): int
    {
        return $this->jobId;
    }

    /**
     * @return string
     */
    public function getExecutorHandler(): string
    {
        return $this->executorHandler;
    }

    /**
     * @return mixed
     */
    public function getExecutorParams()
    {
        return $this->executorParams;
    }

    /**
     * @return int
     */
    public function getLogId(): int
    {
        return $this->logId;
    }
}

