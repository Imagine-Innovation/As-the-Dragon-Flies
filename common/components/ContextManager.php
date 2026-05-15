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
        /** @var \yii\web\User|null $userComponent */
        //$userComponent = Yii::$app->get('user', false);
        //return $userComponent?->getIdentity(false);
    }

    /**
     * @return void
     */
    private static function isGuest(): bool
    {
        /** @var \yii\web\User|null $userComponent */
        $userComponent = Yii::$app->get('user', false);
        return $userComponent?->getIdentity(false) === null;
    }

    /**
     * @param User|null $user
     * @return void
     */
    public static function initContext(?User $user = null): void
    {
        $loggedUser = $user ?? self::getUser();
        if (!$loggedUser) {
            return;
        }

        Yii::$app->session->set('user', $loggedUser);
        Yii::$app->session->set('userId', $loggedUser->id);

        self::updatePlayerContext($loggedUser->current_player_id);
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
     * @param Player $currentPlayer
     * @return void
     */
    private static function setPlayerContext(Player $currentPlayer): void
    {
        Yii::$app->session->set('hasPlayerSelected', true);
        Yii::$app->session->set('playerId', $currentPlayer->id);
        Yii::$app->session->set('playerName', $currentPlayer->name);
        Yii::$app->session->set('avatar', $currentPlayer->image?->file_name);
        Yii::$app->session->set('currentPlayer', $currentPlayer);
        self::updateQuestContext($currentPlayer->quest_id);
    }

    /**
     *
     * @return void
     */
    private static function clearPlayerContext(): void
    {
        Yii::$app->session->set('hasPlayerSelected', false);
        Yii::$app->session->set('playerId', null);
        Yii::$app->session->set('playerName', null);
        Yii::$app->session->set('avatar', null);
        Yii::$app->session->set('currentPlayer', null);
        self::updateQuestContext(null);
    }

    /**
     *
     * @param int|null $playerId
     * @return void
     */
    public static function updatePlayerContext(?int $playerId = null): void
    {
        if (self::isGuest()) {
            return;
        }

        Yii::debug('*** debug *** ContextManager - updatePlayerContext playerId=' . ($playerId ?? 'null'));
        self::setSessionId();

        $currentPlayer = $playerId ? Player::findOne(['id' => $playerId]) : null;

        if ($currentPlayer) {
            self::setPlayerContext($currentPlayer);
        } else {
            self::clearPlayerContext();
        }
    }

    /**
     *
     * @param Quest $quest
     * @return void
     */
    private static function setQuestContext(Quest $quest): void
    {
        Yii::$app->session->set('inQuest', true);
        Yii::$app->session->set('questId', $quest->id);
        Yii::$app->session->set('questName', $quest->name);
        Yii::$app->session->set('currentQuest', $quest);
    }

    /**
     *
     * @return void
     */
    private static function clearQuestContext(): void
    {
        Yii::$app->session->set('inQuest', false);
        Yii::$app->session->set('questId', null);
        Yii::$app->session->set('questName', null);
        Yii::$app->session->set('currentQuest', null);
    }

    /**
     *
     * @param int|null $questId
     * @return void
     */
    public static function updateQuestContext(?int $questId = null): void
    {
        if (self::isGuest()) {
            return;
        }

        Yii::debug('*** debug *** ContextManager - updateQuestContext questId=' . ($questId ?? 'null'));
        self::setSessionId();

        $quest = $questId ? Quest::findOne(['id' => $questId]) : null;

        if ($quest && ($quest->status === AppStatus::WAITING->value || $quest->status === AppStatus::PLAYING->value)) {
            self::setQuestContext($quest);
        } else {
            self::clearQuestContext();
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
            'isGuest' => self::isGuest(),
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
