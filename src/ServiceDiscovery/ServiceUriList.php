<?php

declare(strict_types=1);

namespace Paganini\ServiceDiscovery;

use LogicException;

/**
 * Parses comma-separated service URIs (Fusio-compatible): trim, drop empties, preserve left-to-right order.
 */
final class ServiceUriList
{
    /**
     * @return list<string>
     */
    public static function parseCommaSeparated(string $raw): array
    {
        $parts = explode(',', $raw);
        $list = [];
        foreach ($parts as $part) {
            $t = trim($part);
            if ($t !== '') {
                $list[] = $t;
            }
        }

        return $list;
    }

    /**
     * @param list<string> $list
     */
    public static function pickIndex(array $list, int $index): int
    {
        $n = count($list);
        if ($n === 0) {
            return 0;
        }

        return ($index % $n + $n) % $n;
    }

    /**
     * @param list<string> $list
     */
    public static function pickRandom(array $list): string
    {
        $n = count($list);
        if ($n === 0) {
            throw new LogicException('Empty list.');
        }

        return $list[array_rand($list)];
    }

    /**
     * @param list<string> $list
     */
    public static function pick(array $list, ?int $index): string
    {
        $n = count($list);
        if ($n === 0) {
            throw new LogicException('Empty list.');
        }
        if ($index === null) {
            return self::pickRandom($list);
        }

        return $list[self::pickIndex($list, $index)];
    }
}
