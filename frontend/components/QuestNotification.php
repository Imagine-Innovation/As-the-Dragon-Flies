<?php

namespace frontend\components;

use common\models\Notification;
use common\models\NotificationPlayer;
use Yii;
use yii\base\Component;

class QuestNotification extends Component
{

    const INTERVAL = 30000; // 30s

    /**
     *
     * @param int $playerId
     * @return int
     */


    /**
     *
     * @param int $playerId
     * @param int $dateFrom
     * @return Notification[]
     */


    /**
     *
     * @param int $playerId
     * @return int
     */
    public static function markNotificationsAsRead(int $playerId): int {
        Yii::debug("*** Debug *** markNotificationsAsRead playerId={$playerId}");
        return NotificationPlayer::updateAll(
                        [
                            'is_read' => true,
                            'read_at' => time()
                        ],
                        [
                            'is_read' => false,
                            'player_id' => $playerId
                        ]
                );
    }
}
