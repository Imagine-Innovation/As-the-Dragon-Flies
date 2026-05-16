<?php

use common\helpers\DateTimeHelper;
use common\tests\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestWith;

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
            '1 week' => [604_800, '1 week'],
            '2 weeks' => [1_209_600, '2 weeks'],
            '1 month' => [2_592_000, '1 month'],
            '2 months' => [5_184_000, '2 months'],
            '1 year' => [31_536_000, '1 year'],
            '2 years' => [63_072_000, '2 years'],
        ];
    }

    // -------------------------------------------------------------------------
    // Precision parameter
    // -------------------------------------------------------------------------

    public function testRespectsDefaultPrecisionOfTwo(): void
    {
        // 1 hour + 1 minute + 1 second — default precision = 2
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
        // Exactly 1 hour — only one unit can ever match, regardless of precision.
        self::assertSame('1 hour', DateTimeHelper::elapsedTime(0, 3_600, PHP_INT_MAX));
    }

    public function testOutputsAllUnitsWhenPrecisionIsMaxAndDiffSpansEverything(): void
    {
        // 1y + 1mo + 1w + 1d + 1h + 1m + 1s
        $diff = 31_536_000 + 2_592_000 + 604_800 + 86_400 + 3_600 + 60 + 1;

        self::assertSame(
                '1 year, 1 month, 1 week, 1 day, 1 hour, 1 minute, 1 second',
                DateTimeHelper::elapsedTime(0, $diff, PHP_INT_MAX),
        );
    }

    // -------------------------------------------------------------------------
    // Unit boundary / exact thresholds
    // -------------------------------------------------------------------------

    /** @return array<string, array{int, string}> */
    public static function exactBoundaryProvider(): array
    {
        return [
            '1 second below 1 minute' => [59, '59 seconds'],
            'exactly 1 minute' => [60, '1 minute'],
            '1 second below 1 hour' => [3_599, '59 minutes, 59 seconds'],
            'exactly 1 hour' => [3_600, '1 hour'],
            '1 second below 1 day' => [86_399, '23 hours, 59 minutes'],
            'exactly 1 day' => [86_400, '1 day'],
            '1 second below 1 week' => [604_799, '6 days, 23 hours'],
            'exactly 1 week' => [604_800, '1 week'],
            '1 second below 1 month' => [2_591_999, '4 weeks, 1 day'],
            'exactly 1 month' => [2_592_000, '1 month'],
            '1 second below 1 year' => [31_535_999, '11 months, 4 weeks'],
            'exactly 1 year' => [31_536_000, '1 year'],
        ];
    }

    /**
     * @dataProvider exactBoundaryProvider
     *
     * @param int $seconds
     * @param string $expected
     * @return void
     */
    public function testHandlesExactUnitBoundariesCorrectly(int $seconds, string $expected): void
    {
        self::assertSame($expected, DateTimeHelper::elapsedTime(0, $seconds));
    }

    // -------------------------------------------------------------------------
    // Large values
    // -------------------------------------------------------------------------

    public function testHandlesMultipleYearsCorrectly(): void
    {
        self::assertSame('10 years', DateTimeHelper::elapsedTime(0, 31_536_000 * 10, 1));
    }

    public function testHandlesVeryLargeDiffWithoutOverflow(): void
    {
        // 100 years — well within int range on 64-bit PHP.
        self::assertSame('100 years', DateTimeHelper::elapsedTime(0, 31_536_000 * 100, 1));
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
        $cases = [0, 1, 59, 60, 3_600, 86_400, 31_536_000];

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
