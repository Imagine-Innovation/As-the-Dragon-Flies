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
    case SKIPPED = 403;
    // Action status used for bitwise comparison as a status bit mask
    case PARTIAL = 1; // Binary: 001=1
    case SUCCESS = 2; // Binary: 010=2
    case FAILURE = 4; // Binary: 100=4
    case NOT_FAILED = 3; // Binary: 011=3 (2 | 1)
    case NOT_SUCCEEDED = 5; // Binary: 101=5 (4 | 1)
    case ANY_OUTCOME = 7; // Binary: 111=7 (4 | 2 | 1)
    case ITEM_MISSING = 8;

    /**
     *
     * @return string
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::DELETED => Yii::t('app', 'Deleted'),
            self::INACTIVE => Yii::t('app', 'Inactive'),
            self::ACTIVE => Yii::t('app', 'Active'),
            // Quest statuses
            self::WAITING => Yii::t('app', 'Waiting'),
            self::PLAYING => Yii::t('app', 'Playing'),
            self::PAUSED => Yii::t('app', 'Paused'),
            self::COMPLETED => Yii::t('app', 'Completed'),
            self::ABORTED => Yii::t('app', 'Aborted'),
            // Story statuses
            self::DRAFT => Yii::t('app', 'Draft'),
            self::PUBLISHED => Yii::t('app', 'Published'),
            self::ARCHIVED => Yii::t('app', 'Archived'),
            // QuestPlayer status
            self::ONLINE => Yii::t('app', 'Online'),
            self::OFFLINE => Yii::t('app', 'Offline'),
            self::LEFT => Yii::t('app', 'No longer in the game'),
            // Mission/Progress status
            self::PENDING => Yii::t('app', 'Pending'),
            self::IN_PROGRESS => Yii::t('app', 'In progress'),
            self::TERMINATED => Yii::t('app', 'Terminated'),
            self::SKIPPED => Yii::t('app', 'Skipped'),
            // Action status
            self::PARTIAL => Yii::t('app', 'Partial success'),
            self::SUCCESS => Yii::t('app', 'Success'),
            self::FAILURE => Yii::t('app', 'Failure'),
            self::NOT_FAILED => Yii::t('app', 'Total or partial success'),
            self::NOT_SUCCEEDED => Yii::t('app', 'Partial success or failure'),
            self::ANY_OUTCOME => Yii::t('app', 'Any outcome'),
            self::ITEM_MISSING => Yii::t('app', 'A required item is missing'),
//            default => 'Unknown Status',
        };
    }

    /**
     *
     * @return array{
     *     icon: string,
     *     tooltip: string
     * }
     */
    public function getIcon(): array
    {
        return match ($this) {
            self::DELETED => ['icon' => 'bi-x-square', 'tooltip' => Yii::t('app', 'Deleted, only adminstrators can restore')],
            self::INACTIVE => ['icon' => 'bi-code-square', 'tooltip' => Yii::t('app', 'Inactive. Need to be validated to be used')],
            self::ACTIVE => ['icon' => 'bi-caret-right-square', 'tooltip' => Yii::t('app', 'Validated and active')],
            // Quest statuses
            self::WAITING => ['icon' => 'bi-hourglass-split', 'tooltip' => Yii::t('app', 'Waiting for other players')],
            self::PLAYING => ['icon' => 'bi-action-fight', 'tooltip' => Yii::t('app', 'The adventure has begun')],
            self::PAUSED => ['icon' => 'bi-alarm', 'tooltip' => Yii::t('app', 'The adventure continues after a short break')],
            self::COMPLETED => ['icon' => 'bi-trophy', 'tooltip' => Yii::t('app', 'The adventure is now finished')],
            self::ABORTED => ['icon' => 'bi-sign-stop', 'tooltip' => Yii::t('app', 'The adventure is aborted')],
            // Story statuses
            self::DRAFT => ['icon' => 'bi-journal-code', 'tooltip' => Yii::t('app', 'Draft')],
            self::PUBLISHED => ['icon' => 'bi-journal-check', 'tooltip' => Yii::t('app', 'Published')],
            self::ARCHIVED => ['icon' => 'bi-journal-x', 'tooltip' => Yii::t('app', 'Archived')],
            // QuestPlayer status
            self::ONLINE => ['icon' => 'bi-play-circle', 'tooltip' => Yii::t('app', 'Online')],
            self::OFFLINE => ['icon' => 'bi-pause-circle', 'tooltip' => Yii::t('app', 'Offline')],
            self::LEFT => ['icon' => 'bi-x-circle', 'tooltip' => Yii::t('app', 'No longer in the game')],
            // Mission/Progress status
            self::PENDING => ['icon' => 'bi-hourglass-split', 'tooltip' => Yii::t('app', 'Waiting to start')],
            self::IN_PROGRESS => ['icon' => 'dnd-d20', 'tooltip' => Yii::t('app', 'In progress')],
            self::TERMINATED => ['icon' => 'dnd-diamond', 'tooltip' => Yii::t('app', 'Terminated')],
            self::SKIPPED => ['icon' => 'bi-skip-forward', 'tooltip' => Yii::t('app', 'Skipped')],
            // Action status
            self::PARTIAL => ['icon' => 'bi-star-half', 'tooltip' => Yii::t('app', 'Partial success')],
            self::SUCCESS => ['icon' => 'dnd-badge', 'tooltip' => Yii::t('app', 'Success')],
            self::FAILURE => ['icon' => 'dnd-danger', 'tooltip' => Yii::t('app', 'Failure')],
            self::NOT_FAILED => ['icon' => 'bi-star-fill', 'tooltip' => Yii::t('app', 'Total or partial success')],
            self::NOT_SUCCEEDED => ['icon' => 'bi-star', 'tooltip' => Yii::t('app', 'Partial success or failure')],
            self::ANY_OUTCOME => ['icon' => 'bi-stars', 'tooltip' => Yii::t('app', 'Any outcome')],
            self::ITEM_MISSING => ['icon' => 'dnd-key2', 'tooltip' => Yii::t('app', 'You\'re missing an item')],
//            default => ['icon' => 'bi-exclamation-square', 'tooltip' => 'Undefined'],
        };
    }

    /**
     *
     * @return list<int|null>
     */
    public function getActionStatusFilter(): array
    {
        return match ($this) {
            self::PARTIAL => [self::PARTIAL->value],
            self::SUCCESS => [self::SUCCESS->value],
            self::FAILURE => [self::FAILURE->value],
            self::NOT_FAILED => [self::SUCCESS->value, self::PARTIAL->value],
            self::NOT_SUCCEEDED => [self::PARTIAL->value, self::FAILURE->value],
            self::ANY_OUTCOME => [self::SUCCESS->value, self::PARTIAL->value, self::FAILURE->value],
            default => [self::SUCCESS->value, self::PARTIAL->value, self::FAILURE->value, null],
        };
    }

    /**
     *
     * @return string
     */
    public function getActionAdjective(): string
    {
        return match ($this) {
            self::PARTIAL => 'partialy succeeded',
            self::SUCCESS => 'succeeded',
            self::FAILURE => 'failed',
            default => 'did something, but I don\'t know what',
        };
    }

    /**
     * Helper to get an array of values for a specific entity type for validation rules
     *
     * @return array<int>
     */
    public static function getValuesForUser(): array
    {
        return [self::ACTIVE->value, self::INACTIVE->value, self::DELETED->value];
    }

    /**
     *
     * @return array<int>
     */
    public static function getValuesForPlayer(): array
    {
        return [self::ACTIVE->value, self::INACTIVE->value, self::DELETED->value];
    }

    /**
     *
     * @return array<int>
     */
    public static function getValuesForQuest(): array
    {
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

    /**
     *
     * @return array<int>
     */
    public static function getValuesForStory(): array
    {
        return [
            self::DRAFT->value,
            self::PUBLISHED->value,
            self::ARCHIVED->value,
        ];
    }

    /**
     *
     * @return array<int>
     */
    public static function getValuesForQuestPlayer(): array
    {
        return [
            self::ONLINE->value,
            self::OFFLINE->value,
            self::LEFT->value,
        ];
    }

    /**
     *
     * @return array<int>
     */
    public static function getValuesForQuestProgress(): array
    {
        return [
            self::PENDING->value,
            self::IN_PROGRESS->value,
            self::TERMINATED->value,
            self::SKIPPED->value,
        ];
    }

    /**
     *
     * @return array<int>
     */
    public static function getValuesForAction(): array
    {
        return [
            self::SUCCESS->value,
            self::PARTIAL->value,
            self::FAILURE->value,
            self::NOT_FAILED->value,
            self::NOT_SUCCEEDED->value,
            self::ANY_OUTCOME->value,
            self::ITEM_MISSING->value,
        ];
    }

    /**
     *
     * @return array<int, string>
     */
    public static function getActionStatus(): array
    {
        return [
            self::SUCCESS->value => self::SUCCESS->getLabel(),
            self::PARTIAL->value => self::PARTIAL->getLabel(),
            self::FAILURE->value => self::FAILURE->getLabel(),
            self::NOT_FAILED->value => self::NOT_FAILED->getLabel(),
            self::NOT_SUCCEEDED->value => self::NOT_SUCCEEDED->getLabel(),
            self::ANY_OUTCOME->value => self::ANY_OUTCOME->getLabel(),
            self::ITEM_MISSING->value => self::ITEM_MISSING->getLabel(),
        ];
    }

    /**
     *
     * @param string $entityName
     * @param int $statusValue
     * @return bool
     */
    public static function isValidForEntity(string $entityName, int $statusValue): bool
    {
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
            'QuestTurn' => self::getValuesForQuestProgress(),
            'QuestAction' => self::getValuesForAction(),
            'ActionFlow' => self::getValuesForAction(),
            default => [],
        };
        Yii::debug($validStatusesForEntity);
        return in_array($statusValue, $validStatusesForEntity);
    }
}
