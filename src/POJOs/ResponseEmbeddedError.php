<?php

namespace Paganini\POJOs;

/**
 * Embedded Error DTO
 *
 * Encapsulates detailed error information
 */
readonly class ResponseEmbeddedError
{
    private string $path;
    private string $source;
    private string $message;
    private string $rejectValue;
    private string $availableValues;

    public function __construct(string $first, string $second = '', string $third = '', string $fourth = '')
    {
        if ($fourth !== '') {
            // Backward-compatible positional mode:
            // (path, message, rejectValue, source)
            $this->path = $first;
            $this->message = $second;
            $this->rejectValue = $third;
            $this->source = $fourth;
            $this->availableValues = '';
            return;
        }

        // Current compact mode:
        // (message, rejectValue, availableValues)
        $this->path = '';
        $this->source = '';
        $this->message = $first;
        $this->rejectValue = $second;
        $this->availableValues = $third;
    }

    public function toArray(): array
    {
        $ret = [
            'message' => $this->message,
            'rejectValue' => $this->rejectValue,
            'availableValues' => $this->availableValues,
        ];

        if ($this->path !== '') {
            $ret['path'] = $this->path;
        }
        if ($this->source !== '') {
            $ret['source'] = $this->source;
        }

        return $ret;
    }
}