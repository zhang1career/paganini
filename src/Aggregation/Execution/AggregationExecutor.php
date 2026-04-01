<?php

namespace Paganini\Aggregation\Execution;

use Paganini\Aggregation\Contracts\DegradePolicyContract;
use Paganini\Aggregation\DTO\AggregationResult;
use Paganini\Capability\ProviderContract;
use RuntimeException;
use Throwable;

class AggregationExecutor
{
    /**
     * @param array<ProviderContract> $providers
     * @param array<string, mixed> $subject
     * @param array<string, mixed> $context
     * @param callable(array<callable(): array>): array|null $parallelRunner
     */
    public function execute(
        array $providers,
        array $subject,
        array $context,
        DegradePolicyContract $degradePolicy,
        string $mode = 'serial',
        ?callable $parallelRunner = null,
    ): AggregationResult {
        if ($mode === 'parallel') {
            return $this->executeParallel($providers, $subject, $context, $degradePolicy, $parallelRunner);
        }

        return $this->executeSerial($providers, $subject, $context, $degradePolicy);
    }

    /**
     * @param array<ProviderContract> $providers
     */
    private function executeSerial(
        array $providers,
        array $subject,
        array $context,
        DegradePolicyContract $degradePolicy,
    ): AggregationResult {
        $biz = [];
        $degradedKeys = [];
        $keysUsed = [];

        foreach ($providers as $p) {
            $key = $p->key();
            $keysUsed[] = $key;
            try {
                $biz[$key] = $p->fetch($subject, $context);
            } catch (Throwable $e) {
                if ($degradePolicy->shouldFailFast()) {
                    throw $e;
                }
                $degradedKeys[] = $key;
                $biz[$key] = $degradePolicy->degradedValue($key);
            }
        }

        return new AggregationResult($biz, $degradedKeys, $keysUsed);
    }

    /**
     * @param array<ProviderContract> $providers
     * @param callable(array<callable(): array>): array|null $parallelRunner
     */
    private function executeParallel(
        array $providers,
        array $subject,
        array $context,
        DegradePolicyContract $degradePolicy,
        ?callable $parallelRunner = null,
    ): AggregationResult {
        if ($parallelRunner === null) {
            return $this->executeSerial($providers, $subject, $context, $degradePolicy);
        }

        $tasks = [];
        $keysUsed = [];
        foreach ($providers as $p) {
            $k = $p->key();
            $keysUsed[] = $k;

            $tasks[] = static function () use ($p, $k, $subject, $context): array {
                try {
                    return ['key' => $k, 'failed' => false, 'data' => $p->fetch($subject, $context)];
                } catch (Throwable) {
                    return ['key' => $k, 'failed' => true, 'data' => null];
                }
            };
        }

        $rows = $parallelRunner($tasks);
        $biz = [];
        $degradedKeys = [];
        foreach ($rows as $row) {
            $key = $row['key'];
            if (($row['failed'] ?? false) === true) {
                if ($degradePolicy->shouldFailFast()) {
                    throw new RuntimeException('Provider failed in parallel mode: ' . $key);
                }
                $degradedKeys[] = $key;
                $biz[$key] = $degradePolicy->degradedValue($key);

                continue;
            }
            $biz[$key] = $row['data'];
        }

        return new AggregationResult($biz, $degradedKeys, $keysUsed);
    }
}
