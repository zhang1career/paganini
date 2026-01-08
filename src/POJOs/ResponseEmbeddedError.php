<?php

namespace Paganini\POJOs;

/**
 * Embedded Error DTO
 *
 * Encapsulates detailed error information
 */
readonly class ResponseEmbeddedError
{
    public function __construct(
        public string $path,
        public string $message,
        public string $rejectValue,
        public string $source
    ) {
    }
}