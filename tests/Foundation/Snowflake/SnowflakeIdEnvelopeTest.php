<?php

declare(strict_types=1);

namespace Tests\Foundation\Snowflake;

use Paganini\Foundation\Snowflake\SnowflakeIdEnvelope;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class SnowflakeIdEnvelopeTest extends TestCase
{
    public function test_extracts_id_on_ok(): void
    {
        $id = SnowflakeIdEnvelope::extractIdOrThrow([
            'errorCode' => 0,
            'message' => '',
            'data' => ['id' => '1234567890123456789'],
        ]);

        $this->assertSame('1234567890123456789', $id);
    }

    public function test_throws_on_error_code(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('invalid');

        SnowflakeIdEnvelope::extractIdOrThrow([
            'errorCode' => 100,
            'message' => 'invalid access_key',
            'data' => null,
        ]);
    }

    public function test_throws_when_id_missing(): void
    {
        $this->expectException(RuntimeException::class);

        SnowflakeIdEnvelope::extractIdOrThrow([
            'errorCode' => 0,
            'data' => [],
        ]);
    }
}
