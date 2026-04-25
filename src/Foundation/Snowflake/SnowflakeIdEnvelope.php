<?php

declare(strict_types=1);

namespace Paganini\Foundation\Snowflake;

use Paganini\Constants\ResponseConstant;
use RuntimeException;

/**
 * Parses {@code app_snowflake} {@code POST /api/snowflake/id} envelope (see docs/api_snowflake.json).
 */
final class SnowflakeIdEnvelope
{
    /**
     * @param  array<string, mixed>  $json
     */
    public static function extractIdOrThrow(array $json): string
    {
        $code = (int) ($json['errorCode'] ?? -1);
        if ($code !== ResponseConstant::RET_OK) {
            $msg = (string) ($json['message'] ?? 'snowflake error');

            throw new RuntimeException($msg.' (errorCode='.$code.')');
        }
        $data = $json['data'] ?? null;
        if (! is_array($data)) {
            throw new RuntimeException('Snowflake response missing data.');
        }
        $id = $data['id'] ?? null;
        if (! is_string($id) || $id === '') {
            throw new RuntimeException('Snowflake response data.id missing or not a non-empty string.');
        }

        return $id;
    }
}
