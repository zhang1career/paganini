<?php

namespace Paganini\XxlJobExecutor;

/**
 * Token Authenticator
 *
 * Framework-agnostic token authentication for XXL-JOB requests
 */
readonly class TokenAuthenticator
{
    public function __construct(
        private string $expectedToken
    ) {
    }

    /**
     * Validate token
     *
     * @param string|null $providedToken Token from request header
     * @return bool True if token is valid, false otherwise
     */
    public function validate(?string $providedToken): bool
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

