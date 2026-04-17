<?php

declare(strict_types=1);

namespace Paganini\ServiceDiscovery;

use Paganini\ServiceDiscovery\Contracts\ServiceUriResolverInterface;

/**
 * Replaces `://{{service_key}}` in a URL (Fusio-compatible first match).
 */
final class ServiceUrlSpecifier
{
    private const PATTERN = '/:\/\/{{([a-zA-Z0-9_\-]*)}}/';

    /**
     * @param ?int $index forwarded to {@see ServiceUriResolverInterface::resolve()} (null = random)
     */
    public static function specifyHost(string $url, ServiceUriResolverInterface $resolver, ?int $index = null): string
    {
        if ($url === '') {
            return '';
        }
        if (!preg_match(self::PATTERN, $url, $match)) {
            return $url;
        }
        $key = $match[1];
        $value = $resolver->resolve($key, $index);

        return str_replace('{{' . $key . '}}', $value, $url);
    }
}
