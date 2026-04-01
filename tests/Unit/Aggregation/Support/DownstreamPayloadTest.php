<?php

namespace Tests\Unit\Aggregation\Support;

use Paganini\Aggregation\Exceptions\DownstreamServiceException;
use Paganini\Aggregation\Support\DownstreamPayload;
use Tests\TestCase;

class DownstreamPayloadTest extends TestCase
{
    public function test_extract_data_success(): void
    {
        $payload = [
            'errorCode' => 0,
            'data' => ['id' => 1, 'name' => 'mini'],
            'message' => '',
        ];

        $ret = DownstreamPayload::extractData($payload, 'app_user');
        $this->assertSame(['id' => 1, 'name' => 'mini'], $ret);
    }

    public function test_extract_data_throws_when_error_code_non_zero(): void
    {
        $this->expectException(DownstreamServiceException::class);
        DownstreamPayload::extractData([
            'errorCode' => 1001,
            'data' => ['id' => 1],
            'message' => 'token invalid',
        ], 'app_user');
    }

    public function test_extract_data_throws_when_data_missing(): void
    {
        $this->expectException(DownstreamServiceException::class);
        DownstreamPayload::extractData([
            'errorCode' => 0,
            'message' => '',
        ], 'biz_service');
    }
}
