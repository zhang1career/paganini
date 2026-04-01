<?php

namespace Paganini\Aggregation\DTO;

readonly class RequestContext
{
    public function __construct(
        public string  $path,
        public array   $query,
        public array   $headers,
        public ?string $traceId = null,
    ) {
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'query' => $this->query,
            'headers' => $this->headers,
            'trace_id' => $this->traceId,
        ];
    }
}
