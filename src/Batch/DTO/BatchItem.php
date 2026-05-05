<?php

declare(strict_types=1);

namespace Paganini\Batch\DTO;

/**
 * One unit of work inside a batch. {@see $ref} should uniquely identify the
 * item within the job (e.g. an order id) so retries can be reasoned about
 * by the consumer.
 */
final readonly class BatchItem
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        public string $ref,
        public array $payload = [],
    ) {}
}
