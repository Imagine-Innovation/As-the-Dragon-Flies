<?php

namespace common\components;

use common\models\Notification;
use common\models\Player;
use common\helpers\Utilities;
use Yii;

class QuestMessages
{

    const DEFAULT_LIMIT = 20;
    const CHAT_NOTIFICATION_TYPE = 'new-message';
    const ROUNDED_SECONDS = 60;  // rounded to the same minute by default

    /**
     * Get every notification of a specific type for a specific quest.
     *
     * @param int $questId ID of quest
     * @param string $type notification type ("chat", "action"...)
     * @param ?int $since optional parameter to get the notifications since a specific date
     * @param ?int $limit optional parameter to limit the number of returned values
     * @return common\models\Notification
     */
    private static function getQuestNotifications(int $questId, string $type, ?int $since = null, ?int $limit = null): ?array {

        Yii::debug("*** Debug *** getQuestNotifications - questId={$questId}, type={$type}, since=" . ($since ? Utilities::formatDate($since) : "null") . ", limit=" . ($limit ?? "null"));
        $query = Notification::find()
                ->where(['quest_id' => $questId])
                ->andWhere(['notification_type' => $type]);

        if ($since) {
            $query->andWhere(['>=', 'created_at', $since]);
        }

        $notifications = $query->orderBy(['created_at' => SORT_DESC])
                ->limit($limit ?? self::DEFAULT_LIMIT)
                ->all();

        $n = count($notifications);
        Yii::debug("*** Debug *** getQuestNotifications - returns {$n} records");
        return $notifications;
    }

    /**
     *
     * @param int $questId
     * @param ?int $limit
     * @return array|null
     */
    public static function getRecentChatMessages(int $questId, ?int $limit = null): ?array {

        $chatNotifications = self::getQuestNotifications($questId, self::CHAT_NOTIFICATION_TYPE, null, $limit);

        if (!$chatNotifications) {
            return null;
        }

        $chatMessages = [];
        foreach ($chatNotifications as $chatNotification) {
            $chatMessages[] = self::newChatEntry($chatNotification);
        }

        return $chatMessages;
    }

    /**
     *
     * @param ?int $time
     * @return int
     */
    public static function roundedTime(?int $time = null): int {
        $timestamp = $time ?? time();
        return floor($timestamp / self::ROUNDED_SECONDS) * self::ROUNDED_SECONDS;
    }

    /**
     * Initialize a chat message payload
     *
     * @param Player $player
     * @param string $message
     * @return array
     */
    public static function payload(Player $player, string $message): array {
        $timestamp = time();

        return [
            'playerName' => $player->name ?? 'Someone',
            'playerId' => $player->id,
            'avatar' => $player->image ? $player->image->file_name : 'human-male-1.png',
            'questId' => $player->quest_id,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s', $timestamp),
            'roundedTime' => self::roundedTime($timestamp),
        ];
    }

    /**
     * Prepare a new entry for the char message array based on the cha event payload
     *
     * @param Notification $chatNotification
     * @param int $playerId
     * @return array|null
     */
    private static function newChatEntry(Notification $chatNotification, int $playerId): ?array {
        if ($chatNotification->notification_type !== self::CHAT_NOTIFICATION_TYPE) {
            return null;
        }

        $payload = json_decode($chatNotification->payload, true);

        Yii::debug($chatNotification->payload);
        Yii::debug($payload);
        $roundedTime = $payload['roundedTime'] ?? self::roundedTime(time());
        return [
            'isAuthor' => ($chatNotification->initiator_id == $playerId), // defines is the current player is the one who initiate the chat message
            'displayedDateTime' => Utilities::formatDate($roundedTime),
            'sender' => Utilities::encode($payload['playerName']),
            'messages' => [Utilities::encode($payload['message'])], // first entry of the message array
            'avatar' => $payload['avatar'] ?? 'human-male-1.png',
            'roundedTime' => $roundedTime,
        ];
    }

    /**
     *
     * @param int $questId
     * @param int $playerId
     * @param ?int $since
     * @param ?int $limit
     * @return array|null
     */
    public static function getLastMessages(int $questId, int $playerId, ?int $since = null, ?int $limit = null): array {
        $chatNotifications = self::getQuestNotifications($questId, self::CHAT_NOTIFICATION_TYPE, $since, $limit);

        if (!$chatNotifications) {
            return []; // returns an empty array
        }

        $chatMessages = [];
        $previousRoundedTime = 0;
        $previousInitiatorId = 0;
        $i = 0;
        foreach ($chatNotifications as $chatNotification) {
            $roundedTime = self::roundedTime($chatNotification->created_at);

            if (($roundedTime !== $previousRoundedTime) || ($chatNotification->initiator_id !== $previousInitiatorId)) {
                // If the message is not sent during the same minute by the same user then create a new entry
                $chatMessages[++$i] = self::newChatEntry($chatNotification, $playerId);
            } else {
                // otherwise, append the chat message to the current entry
                $chatMessages[$i]['messages'][] = Utilities::encode($chatNotification->message);
            }

            $previousRoundedTime = $roundedTime;
            $previousInitiatorId = $chatNotification->initiator_id;
        }
        //return array_reverse($chatMessages);
        return $chatMessages;
    }
}
