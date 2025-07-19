<?php

namespace frontend\components;

use common\models\Notification;
use common\models\NotificationPlayer;
use Yii;
use yii\base\Component;

class QuestNotification extends Component {

    const INTERVAL = 30000; // 30s

    public static function getCount($playerId) {
        Yii::debug("*** Debug *** getCount playerId=$playerId");
        $count = NotificationPlayer::find()
                ->where([
                    'player_id' => $playerId,
                    'is_read' => false
                ])
                ->andWhere(['>', 'distributed_at', time() - self::INTERVAL])
                ->count();
        Yii::debug("*** Debug *** getCount count=$count");
        return $count;
    }

    public static function getList($playerId, $dateFrom) {
        Yii::debug("*** Debug *** getList playerId=$playerId, dateFrom=$dateFrom");
        $notifications = Notification::find()
                ->where(['>=', 'created_at', $dateFrom])
                ->andWhere([
                    'or',
                    ['is_broadcast' => true],
                    ['id' => NotificationPlayer::find()
                        ->select('notification_id')
                        ->where(['player_id' => $playerId])
                    ]
                ])
                ->orderBy(['created_at' => SORT_DESC])
                ->all();
        return $notifications;
    }

    public static function markNotificationsAsRead($playerId) {
        Yii::debug("*** Debug *** markNotificationsAsRead playerId=$playerId");
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
