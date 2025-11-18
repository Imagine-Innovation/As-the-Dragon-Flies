<?php

namespace common\services;

use common\models\events\EventFactory;
use common\models\Notification;
use common\models\Player;
use common\models\Quest;
use Yii;

class EventService {

    /**
     * Process an event by type
     *
     * @param string $type Event type
     * @param string $sessionId Id of the current browser tab
     * @param Player $player Player who triggered the event
     * @param Quest $quest Quest context
     * @param array $data Additional event data
     * @return bool Whether the event was processed successfully
     */
    public function processEvent(string $type, string $sessionId, Player $player, Quest $quest, array $data = []): bool {
        try {
            $event = EventFactory::createEvent($type, $sessionId, $player, $quest, $data);
            $event->process();
            return true;
        } catch (\Exception $e) {
            Yii::error('Error processing event: ' . $e->getMessage(), 'event');
            return false;
        }
    }

    /**
     * Get unread notifications for a player
     *
     * @param Player $player The player
     * @param int $limit Maximum number of notifications to return
     * @return array Unread notifications
     */
    public function getUnreadNotifications(Player $player, $limit = 20) {
        $notifications = Notification::find()
                ->innerJoin('notification_player', 'notification.id = notification_player.notification_id')
                ->where(['notification_player.player_id' => $player->id])
                ->andWhere(['notification_player.is_read' => 0])
                ->orderBy(['notification.created_at' => SORT_DESC])
                ->limit($limit)
                ->all();

        return $notifications;
    }
}
