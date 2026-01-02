<?php

namespace Paganini\Utils;

class UuidUtil
{

    /**
     * Generate a UUID
     *
     * @return string
     */
    public static function uuid() : string {
        // Generate UUID v4 using native PHP functions
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        
        return sprintf(
            '%08s-%04s-%04s-%04s-%12s',
            bin2hex(substr($data, 0, 4)),
            bin2hex(substr($data, 4, 2)),
            bin2hex(substr($data, 6, 2)),
            bin2hex(substr($data, 8, 2)),
            bin2hex(substr($data, 10, 6))
        );
    }


    /**
     * Generate a short UUID
     *
     * @return array|string
     */
    public static function shortUuid() : array|string {
        return self::short(self::uuid());
    }


    /**
     * Expend a shorted UUID to normal format
     * '123456781234123412341234567890ab' -> '12345678-1234-1234-1234-1234567890ab'
     *
     * @param string $uuid
     * @return string
     */
    public static function expand(string $uuid) : string {
        return substr($uuid, 0, 8)
            . '-' . substr($uuid, 8, 4)
            . '-' . substr($uuid, 12, 4)
            . '-' . substr($uuid, 16, 4)
            . '-' . substr($uuid, 20);
    }


    /**
     * Shorten a UUID to a string without '-'
     * '12345678-1234-1234-1234-1234567890ab' -> '123456781234123412341234567890ab'
     *
     * @param string $uuid
     * @return array
     */
    public static function short(string $uuid) : string {
        return str_replace('-', '', $uuid);
    }
}

