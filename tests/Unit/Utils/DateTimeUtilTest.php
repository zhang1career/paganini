<?php

namespace Tests\Unit\Utils;

use DateTime;
use Exception;
use Paganini\Exceptions\IllegalArgumentException;
use Paganini\Utils\DateTimeUtil;
use Tests\TestCase;

class DateTimeUtilTest extends TestCase
{
    public function test_getDatetimeOfTimestamp()
    {
        $timestamp = 1704112496;
        $result = DateTimeUtil::getDatetimeOfTimestamp($timestamp);

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals($timestamp, $result->getTimestamp());
    }

    public function test_getDatetimeOfDatetimeStr()
    {
        $datetimeStr = '2024-01-01 12:34:56';
        $result = DateTimeUtil::getDatetimeOfDatetimeStr($datetimeStr);

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2024-01-01 12:34:56', $result->format('Y-m-d H:i:s'));
    }

    public function test_getDatetimeOfDateStr()
    {
        $dateStr = '2024-01-01';
        $result = DateTimeUtil::getDatetimeOfDateStr($dateStr);

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2024-01-01', $result->format('Y-m-d'));
    }

    public function test_getDatetimeOfDateStr_throws_exception_for_invalid_format()
    {
        $this->expectException(IllegalArgumentException::class);
        DateTimeUtil::getDatetimeOfDateStr('invalid-date');
    }

    public function test_getDatetimeOfTCompactStr()
    {
        $tStr = '20240101T123456';
        $result = DateTimeUtil::getDatetimeOfTCompactStr($tStr);

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2024-01-01 12:34:56', $result->format('Y-m-d H:i:s'));
    }

    public function test_getDatetimeOfTExpandStr()
    {
        $tStr = '2024-01-01T12:34:56';
        $result = DateTimeUtil::getDatetimeOfTExpandStr($tStr);

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2024-01-01 12:34:56', $result->format('Y-m-d H:i:s'));
    }

    public function test_getDatetimeStrOfTimestamp()
    {
        $timestamp = 1388534400;
        $result = DateTimeUtil::getDatetimeStrOfTimestamp($timestamp);

        $this->assertEquals('2014-01-01 00:00:00', $result);
    }

    public function test_getDatetimeStrOfDatetime()
    {
        $datetime = new DateTime('2024-01-01 12:34:56');
        $result = DateTimeUtil::getDatetimeStrOfDatetime($datetime);

        $this->assertEquals('2024-01-01 12:34:56', $result);
    }

    public function test_getDateTimeStrOfDateStr()
    {
        $dateStr = '2024-01-01';
        $result = DateTimeUtil::getDateTimeStrOfDateStr($dateStr);

        $this->assertEquals('2024-01-01 00:00:00', $result);
    }

    public function test_getDateTimeStrOfDateStr_throws_exception_for_invalid_format()
    {
        $this->expectException(Exception::class);
        DateTimeUtil::getDateTimeStrOfDateStr('invalid-date');
    }

    public function test_getDateOfDatetimeStr()
    {
        $datetimeStr = '2024-01-01 12:34:56';
        $result = DateTimeUtil::getDateOfDatetimeStr($datetimeStr);

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2024-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function test_getDateOfDatetime()
    {
        $datetime = new DateTime('2024-01-01 12:34:56');
        $result = DateTimeUtil::getDateOfDatetime($datetime);

        $this->assertEquals('2024-01-01 00:00:00', $result->format('Y-m-d H:i:s'));
    }

    public function test_getDateCompactStrOfDatetime()
    {
        $datetime = new DateTime('2024-01-01 12:34:56');
        $result = DateTimeUtil::getDateCompactStrOfDatetime($datetime);

        $this->assertEquals('20240101', $result);
    }

    public function test_getDateExpandStrOfDatetime()
    {
        $datetime = new DateTime('2024-01-01 12:34:56');
        $result = DateTimeUtil::getDateExpandStrOfDatetime($datetime);

        $this->assertEquals('2024-01-01', $result);
    }

    public function test_getDateCompactStrOfDateTimeStr()
    {
        $datetimeStr = '2024-01-01 12:34:56';
        $result = DateTimeUtil::getDateCompactStrOfDateTimeStr($datetimeStr);

        $this->assertEquals('20240101', $result);
    }

    public function test_getDateCompactStrOfDateExpandStr()
    {
        $dateStr = '2024-01-01';
        $result = DateTimeUtil::getDateCompactStrOfDateExpandStr($dateStr);

        $this->assertEquals('20240101', $result);
    }

    public function test_getDateExpandStrOfTExpandStr()
    {
        $tStr = '2024-01-01T12:34:56';
        $result = DateTimeUtil::getDateExpandStrOfTExpandStr($tStr);

        $this->assertEquals('2024-01-01', $result);
    }

    public function test_getDateIntOfDatetime()
    {
        $datetime = new DateTime('2024-01-01 12:34:56');
        $result = DateTimeUtil::getDateIntOfDatetime($datetime);

        $this->assertEquals(20240101, $result);
    }

    public function test_getTCompactStrOfDatetime()
    {
        $datetime = new DateTime('2024-01-01 12:34:56');
        $result = DateTimeUtil::getTCompactStrOfDatetime($datetime);

        $this->assertEquals('20240101T1234', $result);
    }

    public function test_getTExpandStrOfDatetime()
    {
        $datetime = new DateTime('2024-01-01 12:34:56');
        $result = DateTimeUtil::getTExpandStrOfDatetime($datetime);

        $this->assertEquals('2024-01-01T12:34:56', $result);
    }

    public function test_now()
    {
        $result = DateTimeUtil::now();

        $this->assertInstanceOf(DateTime::class, $result);
    }

    public function test_today()
    {
        $result = DateTimeUtil::today();

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('00:00:00', $result->format('H:i:s'));
    }

    public function test_yesterday()
    {
        $result = DateTimeUtil::yesterday();

        $this->assertInstanceOf(DateTime::class, $result);
        $expected = (new DateTime('yesterday'))->format('Y-m-d');
        $this->assertEquals($expected, $result->format('Y-m-d'));
    }

    public function test_daysDiffNow()
    {
        $result = DateTimeUtil::daysDiffNow(5);

        $this->assertInstanceOf(DateTime::class, $result);
        $expected = (new DateTime())->modify('+5 days');
        $this->assertEquals($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }

    public function test_daysDiffNow_negative()
    {
        $result = DateTimeUtil::daysDiffNow(-3);

        $this->assertInstanceOf(DateTime::class, $result);
        $expected = (new DateTime())->modify('-3 days');
        $this->assertEquals($expected->format('Y-m-d'), $result->format('Y-m-d'));
    }

    public function test_daysDiff()
    {
        $originDate = new DateTime('2024-01-01');
        $result = DateTimeUtil::daysDiff($originDate, 5);

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2024-01-06', $result->format('Y-m-d'));
    }

    public function test_daysDiff_negative()
    {
        $originDate = new DateTime('2024-01-10');
        $result = DateTimeUtil::daysDiff($originDate, -3);

        $this->assertInstanceOf(DateTime::class, $result);
        $this->assertEquals('2024-01-07', $result->format('Y-m-d'));
    }

    public function test_checkWeekday()
    {
        $monday = new DateTime('2024-01-01'); // Monday
        $this->assertTrue(DateTimeUtil::checkWeekday($monday));

        $friday = new DateTime('2024-01-05'); // Friday
        $this->assertTrue(DateTimeUtil::checkWeekday($friday));

        $saturday = new DateTime('2024-01-06'); // Saturday
        $this->assertFalse(DateTimeUtil::checkWeekday($saturday));

        $sunday = new DateTime('2024-01-07'); // Sunday
        $this->assertFalse(DateTimeUtil::checkWeekday($sunday));
    }

    public function test_workdaysDiffNow()
    {
        $result = DateTimeUtil::workdaysDiffNow(1, []);

        $this->assertInstanceOf(DateTime::class, $result);
    }

    public function test_workdaysDiff()
    {
        $originDate = new DateTime('2024-01-05'); // Friday
        $result = DateTimeUtil::workdaysDiff($originDate, 1, []);

        $this->assertInstanceOf(DateTime::class, $result);
    }

    public function test_getNatualRangeOfDatetime()
    {
        $datetime = '2024-01-01 12:34:56';
        $result = DateTimeUtil::getNatualRangeOfDatetime($datetime);

        $this->assertArrayHasKey('startOfDay', $result);
        $this->assertArrayHasKey('startOfNextDay', $result);
        $this->assertIsInt($result['startOfDay']);
        $this->assertIsInt($result['startOfNextDay']);

        $startOfDay = new DateTime('@' . $result['startOfDay']);
        $this->assertEquals('2024-01-01 00:00:00', $startOfDay->format('Y-m-d H:i:s'));
    }

    public function test_split()
    {
        $start = new DateTime('2024-01-01 00:00:00');
        $stop = new DateTime('2024-01-01 00:05:00');
        $interval = 120; // 2 minutes

        $result = DateTimeUtil::split($start, $stop, $interval);

        $this->assertIsArray($result);
        $this->assertGreaterThan(0, count($result));
        $this->assertArrayHasKey('start', $result[0]);
        $this->assertArrayHasKey('stop', $result[0]);
    }

    public function test_split_with_start_after_stop()
    {
        $start = new DateTime('2024-01-01 00:05:00');
        $stop = new DateTime('2024-01-01 00:00:00');

        $result = DateTimeUtil::split($start, $stop, 120);

        $this->assertEquals([], $result);
    }

    public function test_split_with_interval_larger_than_diff()
    {
        $start = new DateTime('2024-01-01 00:00:00');
        $stop = new DateTime('2024-01-01 00:01:00');
        $interval = 120; // 2 minutes

        $result = DateTimeUtil::split($start, $stop, $interval);

        $this->assertCount(1, $result);
        $this->assertEquals($start->getTimestamp(), $result[0]['start']->getTimestamp());
        $this->assertEquals($stop->getTimestamp(), $result[0]['stop']->getTimestamp());
    }

    public function test_splitDatetimeStr()
    {
        $startStr = '2024-01-01 00:00:00';
        $stopStr = '2024-01-01 00:05:00';
        $interval = 120;

        $result = DateTimeUtil::splitDatetimeStr($startStr, $stopStr, $interval);

        $this->assertIsArray($result);
        if (count($result) > 0) {
            $this->assertArrayHasKey('start', $result[0]);
            $this->assertArrayHasKey('stop', $result[0]);
            $this->assertIsString($result[0]['start']);
        }
    }

    public function test_getWorkdays()
    {
        $date1 = '2024-01-01';
        $date2 = '2024-01-10';

        $result = DateTimeUtil::getWorkdays($date1, $date2);

        $this->assertIsInt($result);
        $this->assertGreaterThan(0, $result);
    }

    public function test_getWorkdays_with_workSat()
    {
        $date1 = '2024-01-01';
        $date2 = '2024-01-10';

        $result = DateTimeUtil::getWorkdays($date1, $date2, true);

        $this->assertIsInt($result);
    }
}

