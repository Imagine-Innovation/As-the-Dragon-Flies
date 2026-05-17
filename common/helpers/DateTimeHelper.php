<?php

namespace common\helpers;

use DateTime;
use Yii;

final class DateTimeHelper
{

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
     * Converts the difference between two Unix timestamps.
     *
     * @param int $startTime Unix timestamp for the start of the interval
     * @param int|null $endTime   Unix timestamp for the end of the interval (defaults to now)
     * @return \DateInterval
     */
    private static function getInterval(int $startTime, ?int $endTime = null): \DateInterval
    {
        $finalEndTime = ($endTime === 0 || $endTime === null) ? time() : $endTime;

        if ($finalEndTime < $startTime) {
            $finalStartTime = $finalEndTime;
            $finalEndTime = $startTime;
        } else {
            $finalStartTime = $startTime;
        }

        $startTimestamp = (new DateTime())->setTimestamp($finalStartTime);
        $endTimestamp = (new DateTime())->setTimestamp($finalEndTime);

        return $startTimestamp->diff($endTimestamp);
    }

    /**
     *
     * @param \DateInterval $interval
     * @return array<int>
     */
    private static function setUnits(\DateInterval $interval): array
    {
        return [
            'year' => $interval->y,
            'month' => $interval->m,
            'week' => intdiv($interval->d, 7),
            'day' => $interval->d % 7,
            'hour' => $interval->h,
            'minute' => $interval->i,
            'second' => $interval->s,
        ];
    }

    /**
     * Converts the difference between two Unix timestamps into a human-readable string.
     *
     * @param int $startTime Unix timestamp for the start of the interval
     * @param int|null $endTime   Unix timestamp for the end of the interval (defaults to now)
     * @param int $precision Maximum number of time units to include in the output
     *
     * @return non-empty-string e.g. "1 day", "2 hours, 35 minutes", "45 seconds"
     */
    public static function elapsedTime(int $startTime, ?int $endTime = null, int $precision = 2): string
    {
        $interval = self::getInterval($startTime, $endTime);
        $units = self::setUnits($interval);

        /** @var list<string> $parts */
        $parts = [];
        $partCount = 0;

        foreach ($units as $label => $value) {
            if ($value > 0) {
                $parts[$partCount++] = $value . ' ' . $label . ($value > 1 ? 's' : '');
            }
            if ($partCount >= $precision) {
                break;
            }
        }

        return $parts !== [] ? implode(', ', $parts) : '0 seconds';
    }
}
