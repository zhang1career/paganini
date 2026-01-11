<?php

namespace Paganini\XxlJobExecutor;

/**
 * Token Authenticator
 *
 * Framework-agnostic token authentication for XXL-JOB requests
 */
class TokenAuthenticator
{
    /**
     * @var string
     */
    private $expectedToken;

    public function __construct(string $expectedToken)
    {
        $this->expectedToken = $expectedToken;
    }

    /**
     * Validate token
     *
     * @param string|null $providedToken Token from request header
     * @return bool True if token is valid, false otherwise
     */
    public function validate($providedToken): bool
    {
        if ($providedToken === null) {
            return false;
        }

        return $this->expectedToken === $providedToken;
    }

    /**
     * Get expected token (for logging purposes)
     *
     * @return string
     */
    public function getExpectedToken(): string
    {
        return $this->expectedToken;
    }
}

