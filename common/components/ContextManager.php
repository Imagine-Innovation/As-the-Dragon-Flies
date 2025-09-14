<?php

namespace common\components;

use common\models\Player;
use common\models\Quest;
use common\helpers\Utilities;
use Yii;
use yii\base\Component;

class ContextManager extends Component
{

    public static function initContext() {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $user = Yii::$app->user->identity;
        Yii::$app->session->set('user', $user);
        Yii::$app->session->set('userId', $user->id);

        self::updatePlayerContext($user->current_player_id);
    }

    private static function setSessionId() {
        if (Yii::$app->session->get('sessionId') === null) {
            $sessionId = Utilities::newUUID();
            Yii::$app->session->set('sessionId', $sessionId);
        }
    }

    public static function updatePlayerContext(int|null $playerId = null): void {
        if (Yii::$app->user->isGuest) {
            return;
        }

        Yii::debug("*** debug *** - updatePlayerContext playerId=" . ($playerId ?? 'null'));
        self::setSessionId();

        $currentPlayer = $playerId ? Player::findOne(['id' => $playerId]) : null;

        if ($currentPlayer) {
            Yii::$app->session->set('hasPlayerSelected', true);
            Yii::$app->session->set('playerId', $currentPlayer->id);
            Yii::$app->session->set('playerName', $currentPlayer->name);
            Yii::$app->session->set('avatar', $currentPlayer->image->file_name);
            Yii::$app->session->set('currentPlayer', $currentPlayer);
            self::updateQuestContext($currentPlayer->quest_id);
        } else {
            Yii::$app->session->set('hasPlayerSelected', false);
            Yii::$app->session->set('playerId', null);
            Yii::$app->session->set('playerName', null);
            Yii::$app->session->set('avatar', null);
            Yii::$app->session->set('currentPlayer', null);
            self::updateQuestContext(null);
        }
    }

    public static function updateQuestContext(int|null $questId = null): void {
        if (Yii::$app->user->isGuest) {
            return;
        }

        Yii::debug("*** debug *** - updateQuestContext questId=" . ($questId ?? 'null'));
        self::setSessionId();

        $quest = $questId ? Quest::findOne(['id' => $questId]) : null;

        if ($quest) {
            Yii::$app->session->set('inQuest', true);
            Yii::$app->session->set('questId', $quest->id);
            Yii::$app->session->set('questName', $quest->name);
            Yii::$app->session->set('currentQuest', $quest);
        } else {
            Yii::$app->session->set('inQuest', false);
            Yii::$app->session->set('questId', null);
            Yii::$app->session->set('questName', null);
            Yii::$app->session->set('currentQuest', null);
        }
    }
}
