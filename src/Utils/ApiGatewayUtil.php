<?php

namespace Paganini\Utils;

use InvalidArgumentException;

class ApiGatewayUtil
{
    /**
     * Token expiration time in seconds (2 days)
     */
    const EXPIRATION = 172800;

    /**
     * Redis key prefix for SSO service tokens
     */
    const REDIS_PREFIX_SSO_SERV = 'sso:serv:';

    /**
     * Field names for tokens
     */
    const ACCESS_TOKEN = 'token';
    const REFRESH_TOKEN = 'refresh_token';

    /**
     * Write token fields to Redis hash.
     *
     * @param mixed $redisConnection Redis connection/client that supports hSet, hGet, expire
     * @param string $userName
     * @param array $tokens
     * @return array List of written Redis keys (may contain duplicates like original implementation)
     */
    public static function saveTokens($redisConnection,
                                      string $userName,
                                      array  $tokens): array
    {
        if (!$redisConnection) {
            throw new InvalidArgumentException('Redis connection is null');
        }

        $writtenKeys = [];

        $redisKey = self::REDIS_PREFIX_SSO_SERV . $userName;

        foreach ($tokens as $_field => $_value) {
            if (is_array($_value) || is_object($_value)) {
                $encoded = json_encode($_value, JSON_UNESCAPED_UNICODE);
                if ($encoded === false) {
                    $msg = "json_encode failed for field: $_field, error: " . json_last_error_msg();
                    error_log($msg);
                    $_value = serialize($_value);
                } else {
                    $_value = $encoded;
                }
            } elseif ($_value === null) {
                $_value = '';
            } else {
                $_value = (string)$_value;
            }

            // write field
            $redisConnection->hSet($redisKey, $_field, $_value);
            $writtenKeys[] = $redisKey;
        }

        $redisConnection->expire($redisKey, self::EXPIRATION);

        return $writtenKeys;
    }


    /**
     * Read access token from Redis hash. Returns null if hGet returns false (error/missing).
     *
     * @param mixed $redisConnection
     * @param string $userName
     * @return string|null
     */
    public static function getAccessToken($redisConnection,
                                          string $userName)
    {
        if (!$redisConnection) {
            throw new InvalidArgumentException('Redis connection is null');
        }

        $redisKey = self::REDIS_PREFIX_SSO_SERV . $userName;
        $value = $redisConnection->hGet($redisKey, self::ACCESS_TOKEN);
        if ($value === false) {
            $msg = "redis hGet failed for key: $redisKey, field: access_token";
            error_log($msg);
            return null;
        }

        return $value;
    }


    /**
     * Read refresh token from Redis hash. Returns null if hGet returns false (error/missing).
     *
     * @param mixed $redisConnection
     * @param string $userName
     * @return string|null
     */
    public static function getRefreshToken($redisConnection,
                                           string $userName)
    {
        if (!$redisConnection) {
            throw new InvalidArgumentException('Redis connection is null');
        }

        $redisKey = self::REDIS_PREFIX_SSO_SERV . $userName;
        $value = $redisConnection->hGet($redisKey, self::REFRESH_TOKEN);
        if ($value === false) {
            $msg = "redis hGet failed for key: $redisKey, field: refresh_token";
            error_log($msg);
            return null;
        }

        return $value;
    }
}

