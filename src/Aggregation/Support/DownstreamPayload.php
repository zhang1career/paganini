<?php

namespace Paganini\Aggregation\Support;

use Paganini\Aggregation\Exceptions\DownstreamServiceException;

class DownstreamPayload
{
    public static function extractData(mixed $payload, string $serviceName = 'downstream service'): array
    {
        if (!is_array($payload)) {
            throw new DownstreamServiceException("Invalid payload from {$serviceName}: expected JSON object.");
        }

        if (array_key_exists('errorCode', $payload) && (int) $payload['errorCode'] !== 0) {
            $message = (string) ($payload['message'] ?? 'downstream service error');
            throw new DownstreamServiceException("Downstream error from {$serviceName}: {$message}");
        }

        if (!array_key_exists('data', $payload) || !is_array($payload['data'])) {
            throw new DownstreamServiceException("Invalid payload from {$serviceName}: missing object field data.");
        }

        return $payload['data'];
    }
}
