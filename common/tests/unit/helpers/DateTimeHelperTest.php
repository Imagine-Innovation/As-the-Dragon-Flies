<?php

namespace common\tests\unit\helpers;

use common\helpers\DateTimeHelper;
use common\tests\UnitTester;
use Codeception\Test\Unit;

class DateTimeHelperTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    public function testElapsedTime()
    {
        // 1s shy of a year (365 days)
        $this->assertEquals("11 months, 4 weeks", DateTimeHelper::elapsedTime(0, 31535999, 2));
        $this->assertEquals("11 months, 4 weeks, 2 days", DateTimeHelper::elapsedTime(0, 31535999, 3));

        // Exactly 1 year (non-leap)
        $this->assertEquals("1 year", DateTimeHelper::elapsedTime(0, 31536000, 2));

        // 0 difference
        $this->assertEquals("0 seconds", DateTimeHelper::elapsedTime(100, 100, 2));

        // Weeks
        $this->assertEquals("2 weeks, 3 days", DateTimeHelper::elapsedTime(0, 604800 * 2 + 86400 * 3, 2));

        // Negative difference (reversed order)
        $this->assertEquals("11 months, 4 weeks", DateTimeHelper::elapsedTime(31535999, 1, 2));
    }

    public function testElapsedTimeLeapYear()
    {
        // Leap year (2024 is leap year)
        $leapStart = strtotime('2024-02-01');
        $leapEnd = strtotime('2024-03-01');
        $this->assertEquals("1 month", DateTimeHelper::elapsedTime($leapStart, $leapEnd, 2));

        // Standard month (Feb 2023)
        $nonLeapStart = strtotime('2023-02-01');
        $nonLeapEnd = strtotime('2023-03-01');
        $this->assertEquals("1 month", DateTimeHelper::elapsedTime($nonLeapStart, $nonLeapEnd, 2));
    }
}
