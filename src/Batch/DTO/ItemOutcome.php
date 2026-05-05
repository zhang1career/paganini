<?php

declare(strict_types=1);

namespace Paganini\Batch\DTO;

use Paganini\Batch\Enums\ItemStatus;

/**
 * Per-item bookkeeping payload passed back to the repository so it can persist
 * cursor advancement together with success/failure metadata.
 */
final readonly class ItemOutcome
{
    public function __construct(
        public string $ref,
        public ItemStatus $status,
        public ?string $error = null,
    ) {}

    public static function success(string $ref): self
    {
        return new self($ref, ItemStatus::Success);
    }

    public static function failure(string $ref, string $error): self
    {
        return new self($ref, ItemStatus::Failure, $error);
    }
}
