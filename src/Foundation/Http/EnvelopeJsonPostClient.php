<?php

declare(strict_types=1);

namespace Paganini\Foundation\Http;

use JsonException;
use RuntimeException;

/**
 * JSON POST using PHP streams (no extra Composer deps). Suitable for service_foundation envelopes.
 */
final class EnvelopeJsonPostClient implements JsonPostClientInterface
{
    private readonly string $baseUrl;

    public function __construct(
        string $baseUrl,
        private readonly float $timeoutSeconds = 10.0,
    ) {
        $t = trim($baseUrl);
        if ($t === '') {
            throw new \InvalidArgumentException('baseUrl must be non-empty.');
        }
        $this->baseUrl = rtrim($t, '/');
    }

    public function postJson(string $path, array $body, array $headers = []): array
    {
        $path = '/'.ltrim($path, '/');
        $url = $this->baseUrl.$path;
        try {
            $payload = json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
        } catch (JsonException $e) {
            throw new RuntimeException('JSON encode failed: '.$e->getMessage(), 0, $e);
        }

        $headerLines = ['Content-Type: application/json'];
        foreach ($headers as $name => $value) {
            $headerLines[] = $name.': '.$value;
        }

        $ctx = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => implode("\r\n", $headerLines),
                'content' => $payload,
                'timeout' => $this->timeoutSeconds,
                'ignore_errors' => true,
            ],
        ]);

        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            throw new RuntimeException('HTTP POST failed: '.$url);
        }

        $lines = (isset($http_response_header) && is_array($http_response_header))
            ? $http_response_header
            : [];
        $status = $this->statusFromLines($lines);
        if ($status >= 400) {
            throw new RuntimeException('HTTP POST '.$status.' for '.$url);
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new RuntimeException('Invalid JSON response from '.$url, 0, $e);
        }

        if (! is_array($decoded)) {
            throw new RuntimeException('Response root must be a JSON object: '.$url);
        }

        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * @param  list<string>|array<int, string>  $lines
     */
    private function statusFromLines(array $lines): int
    {
        $line = (string) ($lines[0] ?? '');
        if (preg_match('#HTTP/\d\.\d\s+(\d{3})#', $line, $m) !== 1) {
            return 200;
        }

        return (int) $m[1];
    }
}
