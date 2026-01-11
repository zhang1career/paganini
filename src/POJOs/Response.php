<?php

namespace Paganini\POJOs;

use Paganini\Constants\ResponseConstant;

class Response
{
    /**
     * @var int
     */
    private $errorCode;

    /**
     * @var mixed
     */
    private $data;

    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $_embedded;

    /**
     * @param int $errorCode Response code, 0 for success, other for failure
     * @param mixed $data Response data (optional)
     * @param string $message Response message
     * @param array $_embedded Embedded data (optional), container for additional info
     */
    public function __construct(int $errorCode, $data = null, string $message = '', array $_embedded = [])
    {
        $this->errorCode = $errorCode;
        $this->data = $data;
        $this->message = $message;
        $this->_embedded = $_embedded;
    }


    /**
     * Getters
     */
    public function getErrorCode(): int
    {
        return $this->errorCode;
    }

    /**
     * @return mixed
     */
    public function getData()
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
    public static function success($data = null, string $message = ''): self
    {
        return new self(ResponseConstant::RET_OK, $data, $message);
    }

    /**
     * Create failure response
     *
     * @param string $message Error message
     * @return self
     */
    public static function fail(string $message): self
    {
        return new self(ResponseConstant::RET_ERR, null, $message);
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
        return new self($errorCode, null, $message);
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
        return new self($errorCode, null, $message, $_embedded);
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

