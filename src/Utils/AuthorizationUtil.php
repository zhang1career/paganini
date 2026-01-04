<?php

namespace Paganini\Utils;


class AuthorizationUtil
{
    /**
     * Build a Bearer Token
     *
     * @param string $token
     * @return string Bearer Token
     */
    public static function buildBearerToken(string $token): string
    {
        return 'Bearer ' . $token;
    }
}
