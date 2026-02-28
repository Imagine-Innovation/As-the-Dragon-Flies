<?php

namespace backend\helpers;

class KpiHelper
{

    public const KPI = [
        'users' => [
            'title' => 'Active Users',
            'icon' => 'bi-people-fill',
            'backgroundStyle' => 'bg-primary',
            'containerName' => 'active-users',
            'api' => 'kpi/active-users',
            'response' => 'activeUsers',
        ],
        'players' => [
            'title' => 'Active Players',
            'icon' => 'bi-controller',
            'backgroundStyle' => 'bg-success',
            'containerName' => 'active-players',
            'api' => 'kpi/active-players',
            'response' => 'activePlayers',
        ],
        'stories' => [
            'title' => 'Active Stories',
            'icon' => 'bi-book-half',
            'backgroundStyle' => 'bg-info',
            'containerName' => 'active-stories',
            'api' => 'kpi/active-stories',
            'response' => 'activeStories',
        ],
        'quests' => [
            'title' => 'Active quests',
            'icon' => 'dnd-action-fight',
            'backgroundStyle' => 'bg-danger',
            'containerName' => 'active-quests',
            'api' => 'kpi/active-quests',
            'response' => 'activeQuests',
        ],
    ];

    /**
     *
     * @return int
     */
    public static function count(): int
    {
        return count(self::KPI);
    }

    /**
     *
     * @param string $breakpoint
     * @return int
     */
    private static function rowColumns(string $breakpoint): int
    {
        $maxColumns = match ($breakpoint) {
            'xxl' => 6,
            'lg' => 4,
            'md' => 2,
            default => 1
        };

        return min(self::count(), $maxColumns);
    }

    /**
     *
     * @return int
     */
    public static function xxlBreakpoint(): int
    {
        return self::rowColumns('xxl');
    }

    /**
     *
     * @return int
     */
    public static function lgBreakpoint(): int
    {
        return self::rowColumns('lg');
    }

    /**
     *
     * @return int
     */
    public static function mdBreakpoint(): int
    {
        return self::rowColumns('md');
    }
}
