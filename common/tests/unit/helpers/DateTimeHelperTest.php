<?php

namespace common\tests\unit\helpers;

use common\helpers\DateTimeHelper;
use common\tests\UnitTester;
use Codeception\Test\Unit;
use ReflectionClass;

/**
 * Run with:
 *   php vendor/bin/codecept run common/tests/unit/helpers/DateTimeHelperTest.php
 */
final class DateTimeHelperTest extends Unit
{

    protected UnitTester $tester;

    // -------------------------------------------------------------------------
    // Zero / identity
    // -------------------------------------------------------------------------

    public function testReturnsZeroSecondsWhenBothTimestampsAreIdentical(): void
    {
        self::assertSame('0 seconds', DateTimeHelper::elapsedTime(1_000_000, 1_000_000));
    }

    public function testReturnsZeroSecondsWhenEndTimeDefaultsToNowAndStartTimeIsNow(): void
    {
        // Both time() calls happen within the same second — diff is 0.
        self::assertSame('0 seconds', DateTimeHelper::elapsedTime(time()));
    }

    // -------------------------------------------------------------------------
    // Direction invariance (start > end)
    // -------------------------------------------------------------------------

    public function testProducesSameOutputRegardlessOfArgumentOrder(): void
    {
        $base = 1_000_000;

        self::assertSame(
                DateTimeHelper::elapsedTime($base, $base + 3_661),
                DateTimeHelper::elapsedTime($base + 3_661, $base),
        );
    }

    // -------------------------------------------------------------------------
    // Singular vs plural labels
    // -------------------------------------------------------------------------

    /**
     * @dataProvider elapsedSecondsProvider
     *
     * @param int $seconds
     * @param string $expected
     * @return void
     */
    public function testHandlesSingularAndPluralCorrectly(int $seconds, string $expected): void
    {
        self::assertSame($expected, DateTimeHelper::elapsedTime(0, $seconds, 1));
    }

    /**
     *
     * @return array<string, array{int, string}>
     */
    public function elapsedSecondsProvider(): array
    {
        return [
            '1 second' => [1, '1 second'],
            '2 seconds' => [2, '2 seconds'],
            '1 minute' => [60, '1 minute'],
            '2 minutes' => [120, '2 minutes'],
            '1 hour' => [3_600, '1 hour'],
            '2 hours' => [7_200, '2 hours'],
            '1 day' => [86_400, '1 day'],
            '2 days' => [172_800, '2 days'],
        ];
    }

    // -------------------------------------------------------------------------
    // Precision parameter
    // -------------------------------------------------------------------------

    /**
     * @dataProvider respectsPrecisionProvider
     */
    public function testRespectsPrecision(int $precision, string $expected): void
    {
        $start = '2025-01-01 00:00:00';
        $end = '2026-02-09 01:01:01'; // = 2025-01-01 + 1 year + 1 month + 1 week + 1 day (to 2026-02-08) + 1 hour + 1 minute + 1 second
        $s = (int) strtotime($start);
        $e = (int) strtotime($end);

        self::assertSame($expected, DateTimeHelper::elapsedTime($s, $e, $precision));
    }

    /**
     *
     * @return array<string, array{int, string}>
     */
    public function respectsPrecisionProvider(): array
    {
        return [
            'Precision -1' => [-1, '1 year'],
            'Precision 0' => [0, '1 year'],
            'Precision 1' => [1, '1 year'],
            'Precision 2' => [2, '1 year, 1 month'],
            'Precision 3' => [3, '1 year, 1 month, 1 week'],
            'Precision 4' => [4, '1 year, 1 month, 1 week, 1 day'],
            'Precision 5' => [5, '1 year, 1 month, 1 week, 1 day, 1 hour'],
            'Precision 6' => [6, '1 year, 1 month, 1 week, 1 day, 1 hour, 1 minute'],
            'Precision 7' => [7, '1 year, 1 month, 1 week, 1 day, 1 hour, 1 minute, 1 second'],
            'Precision 8' => [8, '1 year, 1 month, 1 week, 1 day, 1 hour, 1 minute, 1 second'],
            'Precision PHP_INT_MAX' => [PHP_INT_MAX, '1 year, 1 month, 1 week, 1 day, 1 hour, 1 minute, 1 second'],
        ];
    }

    // -------------------------------------------------------------------------
    // Real date boundaries
    // -------------------------------------------------------------------------

    /**
     * @dataProvider dateBoundaryProvider
     */
    public function testHandlesDateBoundariesCorrectly(string $start, string $end, string $expected, int $precision = 2): void
    {
        $s = (int) strtotime($start);
        $e = (int) strtotime($end);
        self::assertSame($expected, DateTimeHelper::elapsedTime($s, $e, $precision));
    }

    /**
     *
     * @return array<string, array{string, string, string, int?}>
     */
    public function dateBoundaryProvider(): array
    {
        return [
            // 31-day month (Jan 2026)
            '31-day month range' => ['2026-01-01 00:00:00', '2026-01-31 00:00:00', '4 weeks, 2 days'],
            'exactly 1 month' => ['2026-01-01 00:00:00', '2026-02-01 00:00:00', '1 month'],
            '1 month 1 second' => ['2026-01-01 00:00:00', '2026-02-01 00:00:01', '1 month, 1 second'],
            // 28-day month (Feb 2026)
            '28-day month range' => ['2026-02-01 00:00:00', '2026-03-01 00:00:00', '1 month'],
            '28-day month partial' => ['2026-02-01 00:00:00', '2026-02-28 00:00:00', '3 weeks, 6 days'],
            // Leap year (2024)
            'leap year month' => ['2024-02-01 00:00:00', '2024-03-01 00:00:00', '1 month'],
            'leap year full' => ['2024-01-01 00:00:00', '2025-01-01 00:00:00', '1 year'],
            // The original problematic case (approx 1 year)
            '1 second below 1 year' => ['1970-01-01 00:00:00', '1970-12-31 23:59:59', '11 months, 4 weeks'],
            'exactly 1 year' => ['1970-01-01 00:00:00', '1971-01-01 00:00:00', '1 year'],
        ];
    }

    // -------------------------------------------------------------------------
    // Default $endTime = now
    // -------------------------------------------------------------------------

    public function testUsesCurrentTimeWhenEndTimeIsOmitted(): void
    {
        $startTime = time() - 3_600; // 1 hour ago
        // Allow a 1-second drift for slow CI runners.
        self::assertMatchesRegularExpression(
                '/^1 hour(, \d+ seconds?)?$/',
                DateTimeHelper::elapsedTime($startTime),
        );
    }

    public function testUsesCurrentTimeWhenEndTimeIsExplicitlyPassedAsZero(): void
    {
        $startTime = time() - 60; // 1 minute ago

        self::assertMatchesRegularExpression(
                '/^1 minute(, \d+ seconds?)?$/',
                DateTimeHelper::elapsedTime($startTime, 0),
        );
    }

    // -------------------------------------------------------------------------
    // Return type contract (non-empty-string)
    // -------------------------------------------------------------------------

    public function testNeverReturnsAnEmptyString(): void
    {
        $cases = [0, 1, 59, 60, 3_600, 86_400];
        foreach ($cases as $diff) {
            self::assertNotSame('', DateTimeHelper::elapsedTime(0, $diff));
        }
    }

    // -------------------------------------------------------------------------
    // Instantiation is forbidden
    // -------------------------------------------------------------------------

    public function testCannotBeInstantiatedFromOutsideTheClass(): void
    {
        $reflection = new ReflectionClass(DateTimeHelper::class);
        $constructor = $reflection->getConstructor();

        self::assertNotNull($constructor);
        self::assertTrue($constructor->isPrivate());
    }
}
