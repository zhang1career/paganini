<?php

declare(strict_types=1);

namespace Paganini\Foundation\Snowflake;

use Paganini\Foundation\Http\JsonPostClientInterface;

/**
 * Calls {@code POST /api/snowflake/id} on a resolved API gateway host; inject {@see JsonPostClientInterface} + {@code access_key}.
 */
final class SnowflakeIdHttpClient
{
    public function __construct(
        private readonly JsonPostClientInterface $http,
        private readonly string $accessKey,
    ) {
        if (trim($accessKey) === '') {
            throw new \InvalidArgumentException('accessKey must be non-empty.');
        }
    }

    public function generateIdString(): string
    {
        $json = $this->http->postJson('/api/snowflake/id', [
            'access_key' => $this->accessKey,
        ]);

        return SnowflakeIdEnvelope::extractIdOrThrow($json);
    }
}
