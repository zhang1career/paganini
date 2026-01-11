<?php

namespace Paganini\POJOs;

/**
 * Embedded Error DTO
 *
 * Encapsulates detailed error information
 */
class ResponseEmbeddedError
{
    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $message;

    /**
     * @var string
     */
    public $rejectValue;

    /**
     * @var string
     */
    public $source;

    public function __construct(string $path, string $message, string $rejectValue, string $source)
    {
        $this->path = $path;
        $this->message = $message;
        $this->rejectValue = $rejectValue;
        $this->source = $source;
    }
}