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
     */
    public function testHandlesSingularAndPluralCorrectly(int $seconds, string $expected): void
    {
        self::assertSame($expected, DateTimeHelper::elapsedTime(0, $seconds, 1));
    }

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

    public function testRespectsDefaultPrecisionOfTwo(): void
    {
        self::assertSame('1 hour, 1 minute', DateTimeHelper::elapsedTime(0, 3_661));
    }

    public function testRespectsExplicitPrecisionOfOne(): void
    {
        self::assertSame('1 hour', DateTimeHelper::elapsedTime(0, 3_661, 1));
    }

    public function testRespectsExplicitPrecisionOfThree(): void
    {
        self::assertSame('1 hour, 1 minute, 1 second', DateTimeHelper::elapsedTime(0, 3_661, 3));
    }

    public function testDoesNotExceedAvailableUnitsWhenPrecisionIsVeryHigh(): void
    {
        self::assertSame('1 hour', DateTimeHelper::elapsedTime(0, 3_600, PHP_INT_MAX));
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
        self::assertMatchesRegularExpression(
                '/^1 hour(, \d+ seconds?)?$/',
                DateTimeHelper::elapsedTime($startTime)
        );
    }

    public function testUsesCurrentTimeWhenEndTimeIsExplicitlyPassedAsNull(): void
    {
        $startTime = time() - 60; // 1 minute ago
        self::assertMatchesRegularExpression(
                '/^1 minute(, \d+ seconds?)?$/',
                DateTimeHelper::elapsedTime($startTime, null)
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
