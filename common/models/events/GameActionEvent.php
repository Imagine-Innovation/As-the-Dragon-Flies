<?php

declare(strict_types=1);

namespace common\models\events;

use common\components\AppStatus;
use common\models\Player;
use common\models\Quest;
use common\models\QuestLog;
use Yii;

/**
 * Event for game actions
 */
class GameActionEvent extends Event
{
    /** @var string The action type */
    public $action;

    /** @var array<string, mixed> Additional action data */
    public $detail;

    /**
     * Constructor
     *
     * @param Player $player The player who performed the action
     * @param Quest $quest The quest context
     * @param string $action The action type
     * @param array<string, mixed> $detail Additional action data
     */
    public function __construct(string $sessionId, Player $player, Quest $quest, string $action, array $detail = [])
    {
        parent::__construct($sessionId, $player, $quest);
        $this->action = $action;
        $this->detail = $detail;
    }

    /**
     * Helper to get outcome conclusions.
     *
     * @return string
     */
    public function getOutcomeConclusion(): string
    {
        $conclusions = [];
        $outcomes = $this->detail['outcomes'] ?? [];
        if (is_array($outcomes)) {
            foreach ($outcomes as $outcome) {
                if (is_array($outcome)) {
                    $name = $outcome['name'] ?? '';
                    $desc = $outcome['description'] ?? '';
                } elseif (is_object($outcome)) {
                    $name = isset($outcome->name) ? $outcome->name : '';
                    $desc = isset($outcome->description) ? $outcome->description : '';
                } else {
                    $name = '';
                    $desc = '';
                }

                $nameStr = is_string($name) ? $name : '';
                $descStr = is_string($desc) ? $desc : '';

                if ($nameStr !== '' || $descStr !== '') {
                    $cleanDesc = strip_tags($descStr);
                    if ($nameStr !== '' && $cleanDesc !== '') {
                        $conclusions[] = "{$nameStr}: {$cleanDesc}";
                    } elseif ($nameStr !== '') {
                        $conclusions[] = $nameStr;
                    } else {
                        $conclusions[] = $cleanDesc;
                    }
                }
            }
        }
        return implode('; ', $conclusions);
    }

    /**
     * Helper to get localized description.
     *
     * @param string $storyLanguage
     * @return string
     */
    public function getLocalizedDescription(string $storyLanguage): string
    {
        $playerName = $this->player->name ?? 'Unknown';
        $actionName = $this->action;
        $conclusion = $this->getOutcomeConclusion();

        /** @var AppStatus $status */
        $status = $this->detail['status'] ?? AppStatus::FAILURE;

        switch ($status->value) {
            case AppStatus::SUCCESS->value:
                return Yii::t('game', '{playerName} tried {actionName} and succeeded. {outcomeConclusion}', [
                    'playerName' => $playerName,
                    'actionName' => $actionName,
                    'outcomeConclusion' => $conclusion,
                ], $storyLanguage);

            case AppStatus::PARTIAL->value:
                return Yii::t('game', '{playerName} tried {actionName} and partially succeeded. {outcomeConclusion}', [
                    'playerName' => $playerName,
                    'actionName' => $actionName,
                    'outcomeConclusion' => $conclusion,
                ], $storyLanguage);

            case AppStatus::FAILURE->value:
                return Yii::t('game', '{playerName} tried {actionName} and failed. {outcomeConclusion}', [
                    'playerName' => $playerName,
                    'actionName' => $actionName,
                    'outcomeConclusion' => $conclusion,
                ], $storyLanguage);

            case AppStatus::ITEM_MISSING->value:
                return Yii::t('game', '{playerName} tried {actionName} but was missing a required item. {outcomeConclusion}', [
                    'playerName' => $playerName,
                    'actionName' => $actionName,
                    'outcomeConclusion' => $conclusion,
                ], $storyLanguage);

            default:
                return Yii::t('game', '{playerName} tried {actionName}. {outcomeConclusion}', [
                    'playerName' => $playerName,
                    'actionName' => $actionName,
                    'outcomeConclusion' => $conclusion,
                ], $storyLanguage);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getType(): string
    {
        return 'game-action';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'New action';
    }

    /**
     * {@inheritdoc}
     *
     * @return string
     */
    public function getMessage(): string
    {
        /** @var AppStatus */
        $status = $this->detail['status'];
        Yii::debug("*** debug *** GameActionEvent->getMessage status={$status->getLabel()}");
        return match ($status->value) {
            AppStatus::SUCCESS->value => "{$this->player->name} successfully completed the “{$this->action}” action",
            AppStatus::PARTIAL->value => "{$this->player->name} partially completed the “{$this->action}” action",
            AppStatus::FAILURE->value => "{$this->player->name} failed to complete the “{$this->action}” action",
            default => "{$this->player->name} completed the “{$this->action}” action with an unknown status",
        };
    }

    /**
     * {@inheritdoc}
     *
     * @return array<string, mixed>
     */
    public function getPayload(): array
    {
        $detail = $this->detail;
        $detail['timestamp'] = $this->timestamp;
        return [
            'playerName' => $this->player->name,
            'action' => $this->action,
            'questName' => $this->quest->name,
            'detail' => $detail,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function process(): void
    {
        Yii::debug('*** Debug *** GameActionEvent - process');
        $notification = $this->createNotification();

        $this->savePlayerNotifications($notification->id);

        $this->broadcast();

        // Populate the quest_log table
        $storyLanguage = $this->quest->story->language ?? 'en';

        /** @var \common\models\QuestProgress|null $progress */
        $progress = $this->quest->currentQuestProgress;
        $chapterName = 'Unknown';
        $missionName = 'Unknown';
        if ($progress !== null) {
            $chapterName = $progress->mission->chapter->name ?? 'Unknown';
            $missionName = $progress->mission->name ?? 'Unknown';
        }

        $description = $this->getLocalizedDescription($storyLanguage);

        // Calculate the next round
        // We find the max round number for this quest in the quest_log table
        $maxRoundVal = QuestLog::find()->where(['quest_id' => $this->quest->id])->max('round');
        $maxRound = is_numeric($maxRoundVal) ? (int)$maxRoundVal : 0;
        $nextRound = $maxRound + 1;

        $questLog = new QuestLog([
            'quest_id' => $this->quest->id,
            'player_id' => $this->player->id,
            'round' => $nextRound,
            'chapter_name' => $chapterName,
            'mission_name' => $missionName,
            'description' => $description,
        ]);

        if (!$questLog->save()) {
            Yii::error('Failed to save QuestLog: ' . print_r($questLog->getErrors(), true));
        }

        // Dungeon master says hello
        $dungeonMaster = Player::findOne(1);
        if ($dungeonMaster) {
            $message = $this->getMessage();
            $sendingMessageEvent = new SendingMessageEvent($this->sessionId, $dungeonMaster, $this->quest, $message);
            $sendingMessageEvent->process();
        }
    }
}
