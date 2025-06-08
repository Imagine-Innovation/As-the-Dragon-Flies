<?php

namespace common\components;

enum AppStatus: int {

    // Global/User/Player statuses
    case DELETED = 0;
    case INACTIVE = 9;
    case ACTIVE = 10;
    // Quest statuses
    case WAITING = 100;
    case PLAYING = 101;
    case PAUSED = 102;
    case COMPLETED = 103;
    case ABORTED = 109;
    // Story statuses
    case DRAFT = 200;
    case PUBLISHED = 201;
    case ARCHIVED = 202;

    public function getLabel(): string {
        return match ($this) {
            self::DELETED => 'Deleted',
            self::INACTIVE => 'Inactive',
            self::ACTIVE => 'Active',
            // Quest statuses
            self::WAITING => 'Waiting',
            self::PLAYING => 'Playing',
            self::PAUSED => 'Paused',
            self::COMPLETED => 'Completed',
            self::ABORTED => 'Aborted',
            // Story statuses
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::ARCHIVED => 'Archived',
            default => 'Unknown Status', // Not strictly needed for backed enums if all cases covered
        };
    }

    public function getIcon(): array {
        return match ($this) {
            self::DELETED => ['icon' => 'bi-x-square', 'tooltip' => 'Deleted, only adminstrators can restore'],
            self::INACTIVE => ['icon' => 'bi-code-square', 'tooltip' => 'Inactive. Need to be validated to be used'],
            self::ACTIVE => ['icon' => 'bi-caret-right-square', 'tooltip' => 'Validated and active'],
            // Quest statuses
            self::WAITING => ['icon' => 'bi-hourglass-split', 'tooltip' => 'Waiting for other players'],
            self::PLAYING => ['icon' => 'bi-action-fight', 'tooltip' => 'The adventure has begun'],
            self::PAUSED => ['icon' => 'bi-alarm', 'tooltip' => 'The adventure continues after a short break'],
            self::COMPLETED => ['icon' => 'bi-trophy', 'tooltip' => 'The adventure is now finished'],
            self::ABORTED => ['icon' => 'bi-sign-stop', 'tooltip' => 'The adventure is aborted'],
            // Story statuses
            self::DRAFT => ['icon' => 'bi-journal-code', 'tooltip' => 'Draft'],
            self::PUBLISHED => ['icon' => 'bi-journal-check', 'tooltip' => 'Published'],
            self::ARCHIVED => ['icon' => 'bi-journal-x', 'tooltip' => 'Archived'],
            default => ['icon' => 'bi-exclamation-square', 'tooltip' => 'Undefined'],
        };
    }

    // Helper to get an array of values for a specific entity type for validation rules
    public static function getValuesForUser(): array {
        return [self::ACTIVE->value, self::INACTIVE->value, self::DELETED->value];
    }

    public static function getValuesForPlayer(): array {
        return [self::ACTIVE->value, self::INACTIVE->value, self::DELETED->value];
    }

    public static function getValuesForQuest(): array {
        return [
            self::WAITING->value,
            self::PLAYING->value,
            self::PAUSED->value,
            self::COMPLETED->value,
            self::ABORTED->value,
        ];
    }

    public static function getValuesForStory(): array {
        return [
            self::DRAFT->value,
            self::PUBLISHED->value,
            self::ARCHIVED->value,
        ];
    }

    public static function isValidForEntity(string $entityName, int $statusValue): bool {
        $validStatusesForEntity = match (strtolower($entityName)) {
            'user' => self::getValuesForUser(),
            'player' => self::getValuesForPlayer(),
            'quest' => self::getValuesForQuest(),
            'story' => self::getValuesForStory(),
            // Add other entity types here if they have specific status sets
            default => [], // Or throw an exception for unknown entity type
        };

        return in_array($statusValue, $validStatusesForEntity);
    }
}
