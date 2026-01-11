<?php

namespace Tests\Unit\Utils;

use Paganini\Utils\CodecUtil;
use Tests\TestCase;

class CodecUtilTest extends TestCase
{
    public function test_comma_encode()
    {
        $array = ['a', 'b', 'c'];
        $result = CodecUtil::comma_encode($array);

        $this->assertEquals('a,b,c', $result);
    }

    public function test_comma_encode_with_empty_array()
    {
        $result = CodecUtil::comma_encode([]);
        $this->assertEquals('', $result);
    }

    public function test_comma_encode_with_single_element()
    {
        $result = CodecUtil::comma_encode(['single']);
        $this->assertEquals('single', $result);
    }

    public function test_comma_encode_with_numbers()
    {
        $array = [1, 2, 3];
        $result = CodecUtil::comma_encode($array);

        $this->assertEquals('1,2,3', $result);
    }

    public function test_comma_decode()
    {
        $str = 'a,b,c';
        $result = CodecUtil::comma_decode($str);

        $this->assertEquals(['a', 'b', 'c'], $result);
    }

    public function test_comma_decode_with_empty_string()
    {
        $result = CodecUtil::comma_decode('');
        $this->assertEquals([''], $result);
    }

    public function test_comma_decode_with_single_element()
    {
        $result = CodecUtil::comma_decode('single');
        $this->assertEquals(['single'], $result);
    }

    public function test_comma_decode_with_numbers()
    {
        $result = CodecUtil::comma_decode('1,2,3');
        $this->assertEquals(['1', '2', '3'], $result);
    }

    public function test_comma_encode_decode_roundtrip()
    {
        $original = ['a', 'b', 'c', 'd'];
        $encoded = CodecUtil::comma_encode($original);
        $decoded = CodecUtil::comma_decode($encoded);

        $this->assertEquals($original, $decoded);
    }
}

