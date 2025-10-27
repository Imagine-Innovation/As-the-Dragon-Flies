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
    // Mission/Progress status
    case PENDING = 400;
    case IN_PROGRESS = 401;
    case TERMINATED = 402;
    // Action status used for bitwise comparison as a status bit mask
    case SUCCESS = 2;       // Binary: 010=2
    case PARTIAL = 1;       // Binary: 001=1
    case FAILURE = 4;       // Binary: 100=4
    case NOT_FAILED = 3;    // Binary: 011=3 (2 | 1)
    case NOT_SUCCEEDED = 5; // Binary: 101=5 (1 | 4)
    case ANY = 7;           // Binary: 111=7 (2 | 1 | 4)

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
            // Mission/Progress status
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In progress',
            self::TERMINATED => 'Terminated',
            // Action status
            self::SUCCESS => 'Sucess',
            self::PARTIAL => 'Partial success',
            self::FAILURE => 'Failure',
            self::NOT_FAILED => 'Total or partial success',
            self::NOT_SUCCEEDED => 'Partial success or failure',
            self::ANY => 'Any outcome',
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
            // Mission/Progress status
            self::PENDING => ['icon' => 'bi-hourglass-split', 'tooltip' => 'Waiting to start'],
            self::IN_PROGRESS => ['icon' => 'dnd-d20', 'tooltip' => 'In progress'],
            self::TERMINATED => ['icon' => 'dnd-diamond', 'tooltip' => 'Terminated'],
            // Action status
            self::SUCCESS => ['icon' => 'dnd-badge', 'tooltip' => 'Success'],
            self::PARTIAL => ['icon' => 'bi-star-half', 'tooltip' => 'Partial success'],
            self::FAILURE => ['icon' => 'dnd-danger', 'tooltip' => 'Failure'],
            self::NOT_FAILED => ['icon' => 'bi-star-fill', 'tooltip' => 'Total or partial success'],
            self::NOT_SUCCEEDED => ['icon' => 'bi-star', 'tooltip' => 'Partial success or failure'],
            self::ANY => ['icon' => 'bi-stars', 'tooltip' => 'Any outcome'],
            default => ['icon' => 'bi-exclamation-square', 'tooltip' => 'Undefined'],
        };
    }

    public function getActionStatusFilter(): array {
        return match ($this) {
            self::SUCCESS => [self::SUCCESS->value],
            self::PARTIAL => [self::PARTIAL->value],
            self::FAILURE => [self::FAILURE->value],
            self::NOT_FAILED => [self::SUCCESS->value, self::PARTIAL->value],
            self::NOT_SUCCEEDED => [self::PARTIAL->value, self::FAILURE->value],
            self::ANY => [self::SUCCESS->value, self::PARTIAL->value, self::FAILURE->value],
            default => [self::SUCCESS->value, self::PARTIAL->value, self::FAILURE->value, null],
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
            self::SUCCESS->value,
            self::FAILURE->value,
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

    public static function getValuesForQuestProgress(): array {
        return [
            self::PENDING->value,
            self::IN_PROGRESS->value,
            self::TERMINATED->value,
        ];
    }

    public static function getValuesForAction(): array {
        return [
            self::SUCCESS->value,
            self::PARTIAL->value,
            self::FAILURE->value,
            self::NOT_FAILED->value,
            self::NOT_SUCCEEDED->value,
            self::ANY->value,
        ];
    }

    public static function getActionStatus(): array {
        return [
            self::SUCCESS->value => self::SUCCESS->getLabel(),
            self::PARTIAL->value => self::PARTIAL->getLabel(),
            self::FAILURE->value => self::FAILURE->getLabel(),
            self::NOT_FAILED->value => self::NOT_FAILED->getLabel(),
            self::NOT_SUCCEEDED->value => self::NOT_SUCCEEDED->getLabel(),
            self::ANY->value => self::ANY->getLabel(),
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
            'QuestLog' => self::getValuesForQuest(),
            'Story' => self::getValuesForStory(),
            'QuestPlayer' => self::getValuesForQuestPlayer(),
            'QuestProgress' => self::getValuesForQuestProgress(),
            'QuestTurn' => self::getValuesForProgress(),
            'QuestAction' => self::getValuesForAction(),
            'ActionFlow' => self::getValuesForAction(),
            default => [],
        };
        Yii::debug($validStatusesForEntity);
        return in_array($statusValue, $validStatusesForEntity);
    }
}
