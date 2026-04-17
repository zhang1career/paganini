<?php

declare(strict_types=1);

namespace Paganini\ServiceDiscovery\Exceptions;

use RuntimeException;

/**
 * Raised when Redis has no registration data or the parsed instance list is empty.
 */
final class ServiceUriResolutionException extends RuntimeException
{
}
