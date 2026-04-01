<?php

namespace Tests\Unit\Aggregation\Policies;

use Paganini\Aggregation\Policies\DefaultDegradePolicy;
use Tests\TestCase;

class DefaultDegradePolicyTest extends TestCase
{
    public function test_mask_null_strategy(): void
    {
        $policy = new DefaultDegradePolicy(DefaultDegradePolicy::STRATEGY_MASK_NULL);
        $this->assertFalse($policy->shouldFailFast());
        $this->assertNull($policy->degradedValue('coupon'));
    }

    public function test_mask_error_object_strategy(): void
    {
        $policy = new DefaultDegradePolicy(DefaultDegradePolicy::STRATEGY_MASK_ERROR_OBJECT, 'downstream down');
        $value = $policy->degradedValue('coupon');

        $this->assertIsArray($value);
        $this->assertSame('coupon', $value['key']);
        $this->assertSame('downstream down', $value['message']);
    }

    public function test_fail_fast_strategy(): void
    {
        $policy = new DefaultDegradePolicy(DefaultDegradePolicy::STRATEGY_FAIL_FAST);
        $this->assertTrue($policy->shouldFailFast());
    }
}
