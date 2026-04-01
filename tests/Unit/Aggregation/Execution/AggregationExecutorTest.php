<?php

namespace Tests\Unit\Aggregation\Execution;

use Paganini\Aggregation\Execution\AggregationExecutor;
use Paganini\Aggregation\Policies\DefaultDegradePolicy;
use Paganini\Capability\ProviderContract;
use RuntimeException;
use Tests\TestCase;

class AggregationExecutorTest extends TestCase
{
    public function test_execute_serial_with_partial_degrade(): void
    {
        $executor = new AggregationExecutor();
        $policy = new DefaultDegradePolicy(DefaultDegradePolicy::STRATEGY_MASK_NULL);

        $ok = new class implements ProviderContract {
            public function key(): string { return 'ok'; }
            public function supports(array $context): bool { return true; }
            public function fetch(array $subject, array $context): array { return ['uid' => $subject['id'] ?? null]; }
        };
        $fail = new class implements ProviderContract {
            public function key(): string { return 'fail'; }
            public function supports(array $context): bool { return true; }
            public function fetch(array $subject, array $context): array { throw new RuntimeException('boom'); }
        };

        $result = $executor->execute([$ok, $fail], ['id' => 7], [], $policy, 'serial');

        $this->assertSame(['uid' => 7], $result->biz['ok']);
        $this->assertNull($result->biz['fail']);
        $this->assertSame(['fail'], $result->degradedKeys);
        $this->assertTrue($result->hasDegraded());
    }

    public function test_execute_parallel_uses_runner_and_collects_results(): void
    {
        $executor = new AggregationExecutor();
        $policy = new DefaultDegradePolicy(DefaultDegradePolicy::STRATEGY_MASK_ERROR_OBJECT, 'degraded');

        $ok = new class implements ProviderContract {
            public function key(): string { return 'profile'; }
            public function supports(array $context): bool { return true; }
            public function fetch(array $subject, array $context): array { return ['name' => 'mini']; }
        };
        $fail = new class implements ProviderContract {
            public function key(): string { return 'coupon'; }
            public function supports(array $context): bool { return true; }
            public function fetch(array $subject, array $context): array { throw new RuntimeException('down'); }
        };

        $runner = static function (array $tasks): array {
            $rows = [];
            foreach ($tasks as $task) {
                $rows[] = $task();
            }

            return $rows;
        };

        $result = $executor->execute([$ok, $fail], ['id' => 1], [], $policy, 'parallel', $runner);

        $this->assertSame(['name' => 'mini'], $result->biz['profile']);
        $this->assertSame('coupon', $result->biz['coupon']['key']);
        $this->assertSame(['coupon'], $result->degradedKeys);
    }
}
