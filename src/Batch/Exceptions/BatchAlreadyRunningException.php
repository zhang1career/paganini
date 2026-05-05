<?php

declare(strict_types=1);

namespace Paganini\Batch\Exceptions;

use RuntimeException;

/**
 * @deprecated since 0.6.0 — the executor now resumes Running jobs by replaying the
 * unfinished tail instead of throwing this exception. Kept for backward compatibility
 * with consumers that still {@code catch} it; new code should rely on resume semantics.
 */
class BatchAlreadyRunningException extends RuntimeException {}
