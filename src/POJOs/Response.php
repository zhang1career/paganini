<?php

namespace Paganini\POJOs;

use Paganini\Constants\ResponseConstant;

class Response
{
    /**
     * @param int $errorCode Response code, 0 for success, other for failure
     * @param mixed $data Response data (optional)
     * @param string $message Response message
     * @param list<ResponseEmbeddedError> $_embedded Embedded data (optional), container for additional info
     */
    public function __construct(
        private int    $errorCode,
        private mixed  $data = null,
        private string $message = '',
        private array  $_embedded = []
    )
    {
    }


    /**
     * Getters
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getEmbedded(): array
    {
        return $this->_embedded;
    }


    /**
     * Create success response
     *
     * @param mixed $data Response data
     * @param string $message Response message
     * @return self
     */
    public static function success(mixed $data = null, string $message = ''): self
    {
        return new self(errorCode: ResponseConstant::RET_OK, data: $data, message: $message);
    }

    /**
     * Create failure response
     *
     * @param string $message Error message
     * @return self
     */
    public static function fail(string $message): self
    {
        return new self(errorCode: ResponseConstant::RET_ERR, message: $message);
    }

    /**
     * Create failure response with specific error code
     *
     * @param int $errorCode Error code
     * @param string $message Error message
     * @return self
     */
    public static function failWithCode(int $errorCode, string $message): self
    {
        return new self(errorCode: $errorCode, message: $message);
    }

    /**
     * Create failure response with embedded data
     *
     * @param int $errorCode Error code
     * @param string $message Error message
     * @param array $_embedded Embedded data
     * @return self
     */
    public static function failWithDetail(int $errorCode, string $message, array $_embedded): self
    {
        return new self(errorCode: $errorCode, message: $message, _embedded: $_embedded);
    }

    /**
     * Check if response indicates success
     *
     * @return bool True if success, false otherwise
     */
    public function isSuccess(): bool
    {
        return $this->errorCode === ResponseConstant::RET_OK;
    }
}

