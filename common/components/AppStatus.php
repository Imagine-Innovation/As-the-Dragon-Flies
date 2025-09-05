<?php

namespace common\components;

use Yii;

enum AppStatus: int
{

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
    // QuestPlayer status
    case ONLINE = 300;
    case OFFLINE = 301;
    case LEFT = 302;

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
            // QuestPlayer status
            self::ONLINE => 'Online',
            self::OFFLINE => 'Offline',
            self::LEFT => 'No longer in the game',
            default => 'Unknown Status',
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
            // QuestPlayer status
            self::ONLINE => ['icon' => 'bi-play-circle', 'tooltip' => 'Online'],
            self::OFFLINE => ['icon' => 'bi-pause-circle', 'tooltip' => 'Offline'],
            self::LEFT => ['icon' => 'bi-x-circle', 'tooltip' => 'No longer in the game'],
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

    public static function getValuesForQuestPlayer(): array {
        return [
            self::ONLINE->value,
            self::OFFLINE->value,
            self::LEFT->value,
        ];
    }

    public static function isValidForEntity(string $entityName, int $statusValue): bool {
        Yii::debug("*** Debug *** - isValidForEntity entityName={$entityName}, statusValue={$statusValue}");
        $folders = explode("\\", $entityName);
        $className = end($folders);
        Yii::debug("*** Debug *** - isValidForEntity className={$className}");
        $validStatusesForEntity = match ($className) {
            'User' => self::getValuesForUser(),
            'Player' => self::getValuesForPlayer(),
            'Quest' => self::getValuesForQuest(),
            'Story' => self::getValuesForStory(),
            'QuestPlayer' => self::getValuesForQuestPlayer(),
            default => [],
        };
        Yii::debug($validStatusesForEntity);
        return in_array($statusValue, $validStatusesForEntity);
    }
}
