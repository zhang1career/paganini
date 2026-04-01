<?php

namespace Tests\Unit\Capability;

use Paganini\Capability\ProviderContract;
use Paganini\Capability\ProviderRegistry;
use Tests\TestCase;

class ProviderRegistryTest extends TestCase
{
    public function test_matched_filters_by_supports(): void
    {
        $a = new class implements ProviderContract {
            public function key(): string { return 'a'; }
            public function supports(array $context): bool { return ($context['x'] ?? null) === 1; }
            public function fetch(array $subject, array $context): array { return []; }
        };
        $b = new class implements ProviderContract {
            public function key(): string { return 'b'; }
            public function supports(array $context): bool { return true; }
            public function fetch(array $subject, array $context): array { return []; }
        };

        $registry = new ProviderRegistry([$a, $b]);
        $matched = $registry->matched(['x' => 1]);

        $this->assertCount(2, $matched);
        $this->assertSame(['a', 'b'], array_map(fn (ProviderContract $p) => $p->key(), $matched));

        $matched2 = $registry->matched(['x' => 0]);
        $this->assertCount(1, $matched2);
        $this->assertSame('b', $matched2[0]->key());
    }
}
