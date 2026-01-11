<?php

namespace Tests\Unit\Utils;

use Paganini\Exceptions\IllegalArgumentException;
use Paganini\Utils\StringUtil;
use Tests\TestCase;

class StringUtilTest extends TestCase
{
    public function test_isBlank_with_null()
    {
        $this->assertTrue(StringUtil::isBlank(null));
    }

    public function test_isBlank_with_empty_string()
    {
        $this->assertTrue(StringUtil::isBlank(''));
    }

    public function test_isBlank_with_whitespace()
    {
        $this->assertTrue(StringUtil::isBlank('   '));
        $this->assertTrue(StringUtil::isBlank("\t\n\r"));
    }

    public function test_isBlank_with_non_blank_string()
    {
        $this->assertFalse(StringUtil::isBlank('hello'));
        $this->assertFalse(StringUtil::isBlank('  hello  '));
    }

    public function test_isNotBlank()
    {
        $this->assertFalse(StringUtil::isNotBlank(null));
        $this->assertFalse(StringUtil::isNotBlank(''));
        $this->assertFalse(StringUtil::isNotBlank('   '));
        $this->assertTrue(StringUtil::isNotBlank('hello'));
    }

    public function test_checkBlank_with_valid_string()
    {
        $result = StringUtil::checkBlank('hello');
        $this->assertEquals('hello', $result);
    }

    public function test_checkBlank_trims_string()
    {
        $result = StringUtil::checkBlank('  hello  ');
        $this->assertEquals('hello', $result);
    }

    public function test_checkBlank_throws_exception_for_empty_string()
    {
        $this->expectException(IllegalArgumentException::class);
        StringUtil::checkBlank('');
    }

    public function test_checkBlank_throws_exception_for_whitespace()
    {
        $this->expectException(IllegalArgumentException::class);
        StringUtil::checkBlank('   ');
    }

    public function test_truncate_with_short_string()
    {
        $result = StringUtil::truncate('hello', 10);
        $this->assertEquals('hello', $result);
    }

    public function test_truncate_with_long_string()
    {
        $result = StringUtil::truncate('hello world', 8);
        $this->assertEquals('hello...', $result);
    }

    public function test_truncate_with_custom_tail()
    {
        $result = StringUtil::truncate('hello world', 8, '***');
        $this->assertEquals('hello***', $result);
    }

    public function test_truncate_with_exact_length()
    {
        $result = StringUtil::truncate('hello', 5);
        $this->assertEquals('hello', $result);
    }

    public function test_truncate_with_length_shorter_than_tail()
    {
        $result = StringUtil::truncate('hello world', 3, '...');
        // When length is shorter than tail, it will still truncate
        $this->assertEquals('...', $result);
    }
}

