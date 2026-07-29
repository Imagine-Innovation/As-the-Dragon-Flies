<?php

declare(strict_types=1);

namespace common\models\events;

use common\components\AppStatus;
use common\models\Player;
use common\models\Quest;
use common\models\QuestLog;
use common\models\Outcome;
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
     * Extracts name, description and ID from a single outcome.
     *
     * @param mixed $outcome
     * @return array{id: int, name: string, desc: string}
     */
    protected function extractOutcomeDetails(mixed $outcome): array
    {
        if (is_array($outcome)) {
            $id = $outcome['id'] ?? 0;
            $name = $outcome['name'] ?? '';
            $desc = $outcome['description'] ?? '';
        } elseif (is_object($outcome)) {
            $id = isset($outcome->id) ? $outcome->id : 0;
            $name = isset($outcome->name) ? $outcome->name : '';
            $desc = isset($outcome->description) ? $outcome->description : '';
        } else {
            $id = 0;
            $name = '';
            $desc = '';
        }

        return [
            'id' => (int)$id,
            'name' => is_string($name) ? $name : '',
            'desc' => is_string($desc) ? $desc : '',
        ];
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
        if (!is_array($outcomes)) {
            return '';
        }

        foreach ($outcomes as $outcome) {
            $details = $this->extractOutcomeDetails($outcome);
            $name = $details['name'];
            $desc = strip_tags($details['desc']);

            if ($name !== '' && $desc !== '') {
                $conclusions[] = "{$name}: {$desc}";
            } elseif ($name !== '') {
                $conclusions[] = $name;
            } elseif ($desc !== '') {
                $conclusions[] = $desc;
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
        /** @var AppStatus $status */
        $status = $this->detail['status'] ?? AppStatus::FAILURE;

        $templates = [
            AppStatus::SUCCESS->value => '{playerName} tried {actionName} and succeeded. {outcomeConclusion}',
            AppStatus::PARTIAL->value => '{playerName} tried {actionName} and partially succeeded. {outcomeConclusion}',
            AppStatus::FAILURE->value => '{playerName} tried {actionName} and failed. {outcomeConclusion}',
            AppStatus::ITEM_MISSING->value => '{playerName} tried {actionName} but was missing a required item. {outcomeConclusion}',
        ];

        $template = $templates[$status->value] ?? '{playerName} tried {actionName}. {outcomeConclusion}';

        return Yii::t('game', $template, [
            'playerName' => $this->player->name ?? 'Unknown',
            'actionName' => $this->action,
            'outcomeConclusion' => $this->getOutcomeConclusion(),
        ], $storyLanguage);
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

        // Extract outcome ID, DC, action success, and final action name
        $outcomes = $this->detail['outcomes'] ?? [];
        $firstOutcomeId = null;
        $dc = 0;
        $actionName = $this->action;

        if (is_array($outcomes) && !empty($outcomes)) {
            $first = $outcomes[0];
            $details = $this->extractOutcomeDetails($first);
            $firstOutcomeId = $details['id'];

            if ($first instanceof Outcome) {
                /** @var \common\models\Action|null $outcomeAction */
                $outcomeAction = $first->action;
                if ($outcomeAction !== null) {
                    $dc = $outcomeAction->dc;
                    $actionName = $outcomeAction->name;
                }
            }
        }

        // Fallback if outcome_id is still missing
        if ($firstOutcomeId === null || $firstOutcomeId === 0) {
            $missionId = $progress ? $progress->mission_id : null;
            /** @var \common\models\Action|null $actionModel */
            $actionModel = \common\models\Action::findOne([
                'name' => $this->action,
                'mission_id' => $missionId,
            ]);

            if ($actionModel !== null) {
                $dc = $actionModel->dc;
                $actionName = $actionModel->name;
                /** @var Outcome|null $fallbackOutcome */
                $fallbackOutcome = Outcome::findOne(['action_id' => $actionModel->id]);
                if ($fallbackOutcome !== null) {
                    $firstOutcomeId = $fallbackOutcome->id;
                }
            }
        }

        // Ultimate fallback to satisfy DB required rules
        if ($firstOutcomeId === null || $firstOutcomeId === 0) {
            $firstOutcomeId = 1;
        }

        /** @var AppStatus $status */
        $status = $this->detail['status'] ?? AppStatus::FAILURE;

        $questLog = new QuestLog([
            'quest_id' => $this->quest->id,
            'player_id' => $this->player->id,
            'outcome_id' => $firstOutcomeId,
            'round' => $nextRound,
            'chapter_name' => $chapterName,
            'mission_name' => $missionName,
            'action_name' => $actionName,
            'dc' => $dc,
            'action_success' => $status->value,
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
