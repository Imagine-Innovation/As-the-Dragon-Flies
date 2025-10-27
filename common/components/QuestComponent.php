<?php

namespace common\components;

use common\components\AppStatus;
use common\components\NarrativeComponent;
use common\models\Action;
use common\models\ActionFlow;
use common\models\Mission;
use common\models\Quest;
use common\models\QuestAction;
use common\models\QuestPlayer;
use common\models\QuestProgress;
use common\models\QuestTurn;
use Yii;
use yii\base\Component;
use Yii\helpers\ArrayHelper;

class QuestComponent extends Component
{

    public ?Quest $quest = null;
    public ?QuestProgress $questProgress = null;

    public function __construct($config = []) {
        // Call the parent's constructor
        parent::__construct($config);

        if ($this->questProgress) {
            $this->quest = $this->questProgress->quest;
        }
    }

    public function initQuestProgress(): bool {

        $chapter = $this->quest->currentChapter;

        $questProgress = $this->addQuestProgress($chapter->first_mission_id);
        $this->addQuestActions($questProgress->id, $chapter->first_mission_id);
        $this->endCurrentTurn($questProgress->id);
        $newQuestTurn = $this->addNewTurn($questProgress);

        return ($newQuestTurn !== null);
    }

    private function getNextPlayerId(QuestPlayer|null $currentQuestPlayer = null): int|null {
        $nextPlayer = null;
        // If the current player is defined, search for the next active player
        if ($currentQuestPlayer) {
            $currentTurn = $currentQuestPlayer->player_turn;

            // Find the next player
            $nextPlayer = QuestPlayer::find()
                    ->where(['quest_id' => $this->quest->id])
                    ->andWhere(['<>', 'status', AppStatus::LEFT->value])
                    ->andWhere(['>', 'player_turn', $currentTurn])
                    ->orderBy(['player_turn' => SORT_ASC])
                    ->one();
        }
        // If no current player or no next player found,
        // loop back to the first active player
        if (!$nextPlayer) {
            $nextPlayer = QuestPlayer::find()
                    ->where(['quest_id' => $this->quest->id])
                    ->andWhere(['<>', 'status', AppStatus::LEFT->value])
                    ->orderBy(['player_turn' => SORT_ASC])
                    ->one();
        }
        $nextPlayerId = $nextPlayer?->player_id;
        Yii::debug("*** debug *** getNextPlayerId returns {$nextPlayerId}");
        return $nextPlayerId;
    }

    private static function getNextSequence(int $questProgressId): int {
        $nextSequence = QuestTurn::find()
                        ->where(['quest_progress_id' => $questProgressId])
                        ->max('sequence') + 1;
        return $nextSequence;
    }

    public function addNewTurn(QuestProgress &$questProgress): QuestTurn|null {
        $currentQuestPlayer = $questProgress->questPlayer;

        // The quest has no more active players, no new turns can be added.
        if (!$currentQuestPlayer) {
            return null;
        }

        $nextSequence = $this->getNextSequence($questProgress->id);

        $questTurn = new QuestTurn([
            'player_id' => $currentQuestPlayer->player_id,
            'quest_progress_id' => $questProgress->id,
            'sequence' => $nextSequence,
            'status' => AppStatus::IN_PROGRESS->value,
            'started_at' => time()
        ]);

        if (!$questTurn->save()) {
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($questTurn->errors, 0, false)));
        }
        return $questTurn;
    }

    public function endCurrentTurn(int $questProgressId) {
        QuestTurn::updateAll(
                ['status' => AppStatus::TERMINATED->value],
                ['status' => AppStatus::IN_PROGRESS->value, 'quest_progress_id' => $questProgressId]
        );
    }

    public function gameOver(int $status) {

    }

    public function addQuestProgress(int $missionId): ?QuestProgress {
        $mission = Mission::findOne($missionId);
        $nextPlayerId = $this->getNextPlayerId();
        if (!$nextPlayerId) {
            $this->gameOver(AppStatus::FAILURE->value);
            return null;
        }
        $narrative = new NarrativeComponent(['mission' => $mission]);
        $questProgress = new QuestProgress([
            'quest_id' => $this->quest->id,
            'mission_id' => $missionId,
            'current_player_id' => $nextPlayerId,
            'description' => $narrative->renderDescription(),
            'status' => AppStatus::IN_PROGRESS->value,
            'started_at' => time(),
        ]);

        if (!$questProgress->save()) {
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($questProgress->errors, 0, false)));
        }
        return $questProgress;
    }

    public function addQuestActions(int $questProgressId, int $missionId) {
        $actions = Action::findAll(['mission_id' => $missionId]);

        $actionComponent = new ActionComponent();
        foreach ($actions as $action) {
            if ($actionComponent->isActionEligible($action, $questProgressId)) {
                $actionComponent->addQuestAction($action->id, $questProgressId);
            }
        }
    }

    public function getEligibleActions(int $playerId): array|null {
        if (!$this->questProgress) {
            return null;
        }
        $eligibleQuestActions = [];
        foreach ($this->questProgress->questActions as $questAction) {
            if ($questAction->eligible) {
                $eligibleQuestActions[] = $questAction;
            }
        }

        return $eligibleQuestActions;
    }
}
