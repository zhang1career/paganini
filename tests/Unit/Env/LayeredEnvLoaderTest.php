<?php

declare(strict_types=1);

namespace Tests\Unit\Env;

use Paganini\Env\LayeredEnvLoader;
use Paganini\Exceptions\IllegalArgumentException;
use PHPUnit\Framework\TestCase;

final class LayeredEnvLoaderTest extends TestCase
{
    protected function tearDown(): void
    {
        foreach (['TEST_LAYER_BASE', 'TEST_LAYER_OVER', 'APP_ENV', 'X'] as $k) {
            unset($_ENV[$k], $_SERVER[$k]);
            putenv($k);
        }
        parent::tearDown();
    }

    public function test_overlay_overrides_base(): void
    {
        $dir = sys_get_temp_dir().'/paganini-layered-env-'.bin2hex(random_bytes(8));
        mkdir($dir, 0700, true);

        file_put_contents($dir.'/.env', "APP_ENV=prod\nTEST_LAYER_BASE=from_base\n");
        file_put_contents($dir.'/.env.prod', "TEST_LAYER_BASE=from_overlay\nTEST_LAYER_OVER=only_overlay\n");

        $_ENV['APP_ENV'] = 'prod';
        $_SERVER['APP_ENV'] = 'prod';
        putenv('APP_ENV=prod');

        LayeredEnvLoader::loadEnvironmentOverlay($dir);

        $this->assertSame('from_overlay', $_ENV['TEST_LAYER_BASE']);
        $this->assertSame('only_overlay', $_ENV['TEST_LAYER_OVER']);
    }

    public function test_missing_overlay_file_is_noop(): void
    {
        $dir = sys_get_temp_dir().'/paganini-layered-env-'.bin2hex(random_bytes(8));
        mkdir($dir, 0700, true);
        file_put_contents($dir.'/.env', "APP_ENV=dev\nTEST_LAYER_BASE=keep\n");

        $_ENV['APP_ENV'] = 'dev';
        $_ENV['TEST_LAYER_BASE'] = 'keep';

        LayeredEnvLoader::loadEnvironmentOverlay($dir);

        $this->assertSame('keep', $_ENV['TEST_LAYER_BASE']);
    }

    public function test_invalid_app_env_throws(): void
    {
        $dir = sys_get_temp_dir().'/paganini-layered-env-'.bin2hex(random_bytes(8));
        mkdir($dir, 0700, true);
        file_put_contents($dir.'/.env', "APP_ENV=prod\n");
        file_put_contents($dir.'/.env.prod', "X=1\n");

        $_ENV['APP_ENV'] = 'staging';

        $this->expectException(IllegalArgumentException::class);
        LayeredEnvLoader::loadEnvironmentOverlay($dir);
    }
}
