<?php

declare(strict_types=1);

namespace Paganini\Env;

use Dotenv\Dotenv;
use Paganini\Exceptions\IllegalArgumentException;

/**
 * Overlay a second env file after the base file (e.g. .env then .env.prod when APP_ENV=prod).
 * Uses Dotenv mutable mode so keys in the overlay replace those from the base file.
 */
final class LayeredEnvLoader
{
    public const DEFAULT_APP_ENV_KEY = 'APP_ENV';

    public const DEFAULT_BASE_FILE = '.env';

    /**
     * Load {$baseFile}.{$appEnv} when the file exists (e.g. .env.prod).
     * Call after the base env file has been loaded; values override existing keys.
     *
     * @param string $environmentPath Absolute directory containing env files
     * @param string $baseFile Base filename, typically ".env"
     * @param string $appEnvKey Variable holding the env segment (e.g. APP_ENV -> prod)
     * @throws IllegalArgumentException
     */
    public static function loadEnvironmentOverlay(
        string $environmentPath,
        string $baseFile = self::DEFAULT_BASE_FILE,
        string $appEnvKey = self::DEFAULT_APP_ENV_KEY,
    ): void {
        $trimmedPath = rtrim($environmentPath, "\0\\/");
        if ($trimmedPath === '') {
            return;
        }

        $raw = self::readEnvValue($appEnvKey);
        if ($raw === null) {
            return;
        }

        $appEnv = trim($raw);
        if ($appEnv === '') {
            return;
        }

        if (! self::isSafeEnvSegment($appEnv)) {
            throw new IllegalArgumentException(
                'Invalid value for '.$appEnvKey.': only letters, digits, underscore, and hyphen are allowed for overlay filenames.'
            );
        }

        $overlayFile = $baseFile.'.'.$appEnv;
        $fullPath = $trimmedPath.DIRECTORY_SEPARATOR.$overlayFile;
        if (! is_file($fullPath)) {
            return;
        }

        Dotenv::createUnsafeMutable($trimmedPath, $overlayFile)->safeLoad();
    }

    private static function readEnvValue(string $key): ?string
    {
        if (array_key_exists($key, $_ENV)) {
            $v = $_ENV[$key];

            return is_scalar($v) ? (string) $v : null;
        }

        $fromGetenv = getenv($key);
        if ($fromGetenv !== false) {
            return $fromGetenv;
        }

        return null;
    }

    private static function isSafeEnvSegment(string $segment): bool
    {
        return (bool) preg_match('/\A[a-zA-Z0-9_-]+\z/', $segment);
    }
}
