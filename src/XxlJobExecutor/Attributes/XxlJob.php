<?php

namespace Paganini\XxlJobExecutor\Attributes;

/**
 * XxlJob Attribute
 *
 * Used to mark XXL-JOB business methods
 * Note: PHP 7.0 does not support attributes. This class is kept for documentation purposes.
 *
 * @example
 * // In PHP 8.0+: #[XxlJob('discoverService')]
 * // For PHP 7.0, use annotations in docblocks instead
 * public static function discover(): array
 * {
 *     // Business logic
 *     return [true, $result, null];
 * }
 */
class XxlJob
{
    /**
     * @var string
     */
    private $handler;

    /**
     * @param string $handler Job identifier, corresponds to executorHandler in XXL-JOB
     */
    public function __construct(string $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @return string
     */
    public function getHandler(): string
    {
        return $this->handler;
    }
}

