<?php

namespace common\helpers;

use Yii;

final class DateTimeHelper
{

    /**
     * Time units mapped to their equivalent value in seconds, ordered from largest to smallest.
     *
     * @var array<string, int>
     */
    private const UNITS = [
        'year' => 31_536_000,
        'month' => 2_592_000,
        'week' => 604_800,
        'day' => 86_400,
        'hour' => 3_600,
        'minute' => 60,
        'second' => 1,
    ];

    // Utility class — disallow instantiation.
    private function __construct()
    {

    }

    /**
     * Formats a datetime value according to the user's browser language
     *
     * Detects browser language preference between English and French,
     * then formats the date accordingly using Yii's formatter.
     *
     * @param mixed $dateTime The datetime value to format
     * @return string Formatted datetime string in 'short' format
     */
    public static function formatDate($dateTime)
    {
        // Get browser language preference (supports en-US or fr-FR)
        $browserLanguage = Yii::$app->request->getPreferredLanguage(['en-US', 'fr-FR']);

        // Configure formatter locale based on browser language
        Yii::$app->formatter->locale = $browserLanguage;

        return Yii::$app->formatter->asDateTime($dateTime, 'short');
    }

    /**
     * Converts the difference between two Unix timestamps into a human-readable string.
     *
     * @param int $startTime Unix timestamp for the start of the interval
     * @param int $endTime   Unix timestamp for the end of the interval (defaults to now)
     * @param int $precision Maximum number of time units to include in the output
     *
     * @return non-empty-string e.g. "1 day", "2 hours, 35 minutes", "45 seconds"
     */
    public static function elapsedTime(int $startTime, int $endTime = 0, int $precision = 2): string
    {
        $diff = abs(($endTime === 0 ? time() : $endTime) - $startTime);

        if ($diff === 0) {
            return '0 seconds';
        }

        /** @var list<string> $parts */
        $parts = [];

        foreach (self::UNITS as $label => $seconds) {
            if ($diff < $seconds) {
                continue;
            }

            $value = intdiv($diff, $seconds);
            $diff = $diff % $seconds;
            $parts[] = $value . ' ' . $label . ($value > 1 ? 's' : '');

            if (count($parts) === $precision) {
                break;
            }
        }

        return $parts !== [] ? implode(', ', $parts) : '0 seconds';
    }
}
