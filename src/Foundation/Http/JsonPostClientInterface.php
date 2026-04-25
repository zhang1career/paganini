<?php

declare(strict_types=1);

namespace Paganini\Foundation\Http;

/**
 * Minimal JSON POST for gateway-style APIs (callers supply resolved host).
 */
interface JsonPostClientInterface
{
    /**
     * POST JSON to {@code $path} (leading slash optional) relative to client base URL.
     *
     * @param  array<string, mixed>  $body
     * @param  array<string, string>  $headers  extra headers (name => value)
     * @return array<string, mixed> decoded JSON object root
     */
    public function postJson(string $path, array $body, array $headers = []): array;
}
