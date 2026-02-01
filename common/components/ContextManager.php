<?php

namespace common\components;

use common\components\AppStatus;
use common\helpers\Utilities;
use common\models\Player;
use common\models\Quest;
use common\models\User;
use Yii;
use yii\base\Component;

class ContextManager extends Component
{

    /**
     *
     * @return User
     */
    private static function getUser(): User
    {
        return Yii::$app->user->identity;
    }

    /**
     *
     * @return void
     */
    public static function initContext(): void
    {
        if (Yii::$app->user->isGuest) {
            return;
        }
        Yii::debug("*** debug *** ContextManager - initContext");

        $user = self::getUser();
        Yii::$app->session->set('user', $user);
        Yii::$app->session->set('userId', $user->id);

        self::updatePlayerContext($user->current_player_id);
    }

    /**
     *
     * @return void
     */
    private static function setSessionId(): void
    {
        if (Yii::$app->session->get('sessionId') === null) {
            $sessionId = Utilities::newUUID();
            Yii::$app->session->set('sessionId', $sessionId);
        }
    }

    /**
     *
     * @param int|null $playerId
     * @return void
     */
    public static function updatePlayerContext(?int $playerId = null): void
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        Yii::debug("*** debug *** ContextManager - updatePlayerContext playerId=" . ($playerId ?? 'null'));
        self::setSessionId();

        $currentPlayer = $playerId ? Player::findOne(['id' => $playerId]) : null;

        if ($currentPlayer) {
            Yii::$app->session->set('hasPlayerSelected', true);
            Yii::$app->session->set('playerId', $currentPlayer->id);
            Yii::$app->session->set('playerName', $currentPlayer->name);
            Yii::$app->session->set('avatar', $currentPlayer->image?->file_name);
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

    /**
     *
     * @param int|null $questId
     * @return void
     */
    public static function updateQuestContext(?int $questId = null): void
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        Yii::debug("*** debug *** ContextManager - updateQuestContext questId=" . ($questId ?? 'null'));
        self::setSessionId();

        $quest = $questId ? Quest::findOne(['id' => $questId]) : null;

        if ($quest &&
                ($quest->status === AppStatus::WAITING->value ||
                $quest->status === AppStatus::PLAYING->value)) {
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

    /**
     *
     * @return array<string, mixed>
     */
    public static function getContext(): array
    {
        $user = self::getUser();
        return [
            'isGuest' => Yii::$app->user->isGuest,
            'isAdmin' => $user->is_admin,
            'isDesigner' => $user->is_designer,
            'userId' => Yii::$app->session->get('userId'),
            'sessionId' => Yii::$app->session->get('sessionId'),
            'hasPlayerSelected' => Yii::$app->session->get('hasPlayerSelected'),
            'playerId' => Yii::$app->session->get('playerId'),
            'playerName' => Yii::$app->session->get('playerName'),
            'avatar' => Yii::$app->session->get('avatar'),
            'inQuest' => Yii::$app->session->get('inQuest'),
            'questId' => Yii::$app->session->get('questId'),
            'questName' => Yii::$app->session->get('questName'),
        ];
    }
}
