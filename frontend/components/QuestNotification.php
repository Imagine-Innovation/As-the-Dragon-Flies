<?php

namespace frontend\components;

use common\models\Notification;
use common\models\NotificationPlayer;
use Yii;
use yii\base\Component;
use yii\db\Expression;
use yii\web\HttpException;

class QuestNotification extends Component {

    const INTERVAL = 30000; // 30s

    public static function push($notifiationType, $questId, $playerId, $message, $dataId = null) {
        $notification = new Notification([
            'quest_id' => $questId,
            'player_id' => $playerId,
            'message' => $message,
            'created_at' => time(),
            'notification_type' => $notifiationType,
            'data_id' => $dataId,
        ]);

        if (!$notification->save()) {
            throw new HttpException('Could not create a new internal notification');
        }

        if (!self::addRecipients($notification)) {
            throw new HttpException('Could not add new recipient to the notification');
        }
        return true;
    }

    private static function addRecipients($notification) {
        $questPlayers = $notification->quest->questPlayers;
        foreach ($questPlayers as $questPlayer) {
            $notificationPlayer = new NotificationPlayer([
                'notification_id' => $notification->id,
                'player_id' => $questPlayer->player_id,
                'distributed_at' => time()
            ]);
            if (!$notificationPlayer->save()) {
                return false;
            }
        }
        return true;
    }

    public static function newNotificationCount($questId) {
        $count = Notification::find()
                ->where(['quest_id' => $questId, 'acknowledged' => false])
                ->count();
        return $count;
    }

    public static function newPlayerNotificationCount($playerId, $questId) {
        $count = NotificationPlayer::find()
                ->joinWith('notification')
                ->where([
                    'notification.quest_id' => $questId,
                    'notification_player.player_id' => $playerId,
                    'notification_player.is_read' => false
                ])
                ->count();
        return $count;
    }

    private static function makeJSNotifications($notifCount) {
        $row = [];
        $rows = [];
        foreach ($notifCount as $notif) {
            // [{"type":"new-player","count":4,"notificationPlayers":[]}]
            $row[0] = '"type":"' . $notif['type'] . '"';
            $row[1] = '"count":' . $notif['count'];
            $row[2] = '"dateFrom":' . $notif['date_from'];
            $rowStr = '{' . implode(', ', $row) . '}';
            Yii::debug("*** Debug *** makeJSNotifictions rowStr=$rowStr");
            $rows[] = $rowStr;
        }
        return $rows;
    }

    public static function getNewNotifications($playerId) {
        Yii::debug("*** Debug *** getNewNotifications playerId=$playerId");
        $notifCount = Notification::find()
                ->select([
                    'notification.notification_type as type',
                    'COUNT(*) as count',
                    'MIN(created_at) as date_from',
                ])
                ->joinWith('notificationPlayers')
                ->where([
                    'notification_player.player_id' => $playerId,
                    'notification_player.is_read' => false
                ])
                ->andWhere(['>=', 'notification_player.distributed_at', time() - self::INTERVAL])
                ->groupBy('notification.notification_type')
                ->asArray()
                ->all();

        return self::makeJSNotifications($notifCount);
    }

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

    public static function getPlayerNotifications($playerId, $questId, $onlyUnread = false) {
        Yii::debug("*** Debug *** getPlayerNotifications playerId=$playerId, questId=$questId, onlyUnread=" . $onlyUnread ? "True" : "False");
        $query = NotificationPlayer::find()
                ->joinWith('notification')
                ->where([
                    'notification.quest_id' => $questId,
                    'notification_player.player_id' => $playerId
        ]);

        if ($onlyUnread) {
            $query->andWhere(['notification_player.is_read' => false]);
        }

        return $query->orderBy(['notification.created_at' => SORT_DESC])->all();
    }

    public static function markPlayerNotificationsAsRead($playerId, $questId) {
        Yii::debug("*** Debug *** markPlayerNotificationsAsRead playerId=$playerId, questId=$questId");

        $notificationPlayers = self::getPlayerNotifications($playerId, $questId, true);

        foreach ($notificationPlayers as $notificationPlayer) {
            $update = NotificationPlayer::updateAll(
                    [
                        'is_read' => true,
                        'read_at' => new Expression('NOW()')
                    ],
                    [
                        'notification_id' => $notificationPlayer->notification_id,
                        'player_id' => $notificationPlayer->player_id
                    ]
            );
            if (!$update) {
                throw new HttpException('Could not mark player notification as read');
            }
        }
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

    public static function acknowledgeNotifications($notificationId) {
        return Notification::updateAll(
                        ['acknowledged' => true],
                        ['id' => $notificationId]
                );
    }

    public static function lastNotificationTS($questId) {
        $lastNotificationTS = Notification::find()
                ->where(['quest_id' => $questId])
                ->max('created_at');
        return $lastNotificationTS;
    }
}
