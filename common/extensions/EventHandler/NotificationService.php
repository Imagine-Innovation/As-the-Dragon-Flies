<?php

namespace common\extensions\EventHandler;

use common\models\Notification;
use common\models\Player;
use common\models\QuestChat;
use common\models\Quest;      // Required if directly interacting with Quest model like Quest::findOne
use common\models\Story;      // Required if accessing $quest->story

// use common\extensions\EventHandler\LoggerService; // Will be injected

class NotificationService {

    private LoggerService $logger;

    public function __construct(LoggerService $logger) {
        $this->logger = $logger;
    }

    /**
     * Saves a quest chat message.
     * Original logic from EventHandler::saveQuestChat
     * @return bool
     */
    public function saveQuestChat(int $playerId, int $questId, string $message, int $createdAt): bool {
        $this->logger->logStart("NotificationService: saveQuestChat playerId=[{$playerId}], questId=[{$questId}]", ['message' => $message, 'createdAt' => $createdAt]);
        $questChat = new QuestChat([
            'player_id' => $playerId,
            'quest_id' => $questId,
            'message' => $message,
            'created_at' => $createdAt
        ]);

        $saved = false;
        try {
            $saved = $questChat->save();
            if ($saved) {
                $this->logger->log("NotificationService: QuestChat saved successfully.");
            } else {
                $this->logger->log("NotificationService: Failed to save QuestChat. Errors: " . print_r($questChat->getErrors(), true), null, 'error');
            }
        } catch (\Exception $e) {
            $this->logger->log("NotificationService: Exception while saving QuestChat. Error: " . $e->getMessage(), $e->getTraceAsString(), 'error');
        }
        $this->logger->logEnd("NotificationService: saveQuestChat, returned value=" . ($saved ? "true" : "false"));
        return $saved;
    }

    /**
     * Saves a notification.
     * Original logic from EventHandler::saveNotification
     * @return Notification|null The saved Notification object or null on failure.
     */
    public function saveNotification(int $playerId, int $questId, array $data): ?Notification {
        $this->logger->logStart("NotificationService: saveNotification playerId=[{$playerId}], questId=[{$questId}]", $data);

        $message = $data['message'] ?? '';
        $type = $data['type'] ?? 'unknown';
        $createdAt = $data['timestamp'] ?? time(); // Use timestamp from data if available, fallback to now
        // If it's a chat type, first try to save the QuestChat entry
        if ($type === 'chat') {
            if (!$this->saveQuestChat($playerId, $questId, $message, $createdAt)) {
                $this->logger->log("NotificationService: Failed to save QuestChat, aborting notification save for chat.", $data, 'error');
                $this->logger->logEnd("NotificationService: saveNotification");
                return null;
            }
        }

        $sender = Player::findOne($playerId);
        if (!$sender) {
            $this->logger->log("NotificationService: Player not found for playerId=[{$playerId}] when creating payload.", null, 'warning');
            // Depending on requirements, you might still want to save a notification without sender details,
            // or return null as if it failed. For now, proceeding with "Unknown player".
            // If this is critical, return null here.
        }
        $playerName = $sender ? $sender->name : 'Unknown Player';

        // Construct payload. This might vary based on notification type in a more complex system.
        $payload = [
            'playerName' => $playerName,
            'playerId' => $playerId,
            'message' => $message, // Core message content
            'timestamp' => date('Y-m-d H:i:s', $createdAt), // Human-readable timestamp in payload
                // Add other type-specific details to payload as needed
        ];

        $title = $data['title'] ?? ($type === 'chat' ? "New chat message from " . $playerName : "Notification");

        $notification = new Notification([
            'player_id' => $playerId,
            'quest_id' => $questId,
            'notification_type' => $type,
            'title' => $title,
            'message' => $message, // Main message for the notification record itself
            'created_at' => $createdAt,
            'is_private' => $data['is_private'] ?? 0, // Default to public
            'payload' => json_encode($payload),
        ]);

        try {
            if (!$notification->save()) {
                $this->logger->log("NotificationService: Failed to save Notification. Errors: " . print_r($notification->getErrors(), true), $notification->getAttributes(), 'error');
                $this->logger->logEnd("NotificationService: saveNotification");
                return null;
            }
        } catch (\Exception $e) {
            $this->logger->log("NotificationService: Exception while saving Notification. Error: " . $e->getMessage(), $e->getTraceAsString(), 'error');
            $this->logger->logEnd("NotificationService: saveNotification");
            return null;
        }

        $this->logger->log("NotificationService: Notification saved successfully. ID: " . $notification->id);
        $this->logger->logEnd("NotificationService: saveNotification");
        return $notification;
    }

    /**
     * Retrieves notifications for a quest.
     * Original logic from EventHandler::getNotifications
     * @return array An array of Notification objects.
     */
    public function getNotifications(int $questId, string $type, int $since): array {
        $this->logger->logStart("NotificationService: getNotifications for questId=[{$questId}], type=[{$type}], since=[{$since}]");

        $notifications = [];
        try {
            $query = Notification::find()
                    ->where(['quest_id' => $questId])
                    ->andWhere(['notification_type' => $type]);

            if ($since > 0) { // Only add time condition if 'since' is meaningful
                $query->andWhere(['>', 'created_at', $since]);
            }

            $notifications = $query->orderBy(['created_at' => SORT_ASC])->all();

            $notificationCount = count($notifications);
            $this->logger->log("NotificationService: {$notificationCount} notification(s) found.");
        } catch (\Exception $e) {
            $this->logger->log("NotificationService: Exception while getting notifications. Error: " . $e->getMessage(), $e->getTraceAsString(), 'error');
        }

        $this->logger->logEnd("NotificationService: getNotifications");
        return $notifications;
    }

    /**
     * Prepares a chat message from a notification for broadcasting.
     * Original logic from EventHandler::prepareChatMessage
     * @return string JSON encoded string representing the chat message.
     */
    public function prepareChatMessage(Notification $notification, string $sessionId): string {
        // It's assumed $notification is a valid Notification object.
        $this->logger->logStart("NotificationService: prepareChatMessage for notificationId=[{$notification->id}], sessionId=[{$sessionId}]");

        $payload = json_decode($notification->payload, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->log("NotificationService: Failed to decode notification payload for notificationId=[{$notification->id}]. Using empty payload.", $notification->payload, 'warning');
            $payload = []; // Default to empty array if payload is invalid JSON
        }

        $player = $notification->player; // Access via relation
        $playerName = $player ? $player->name : ($payload['playerName'] ?? 'Unknown Player');

        $quest = $notification->quest; // Access via relation
        $questName = $quest && $quest->story ? $quest->story->name : ($payload['questName'] ?? 'Unknown Quest');

        $array = [
            'type' => $notification->notification_type, // Should be 'chat'
            'notificationId' => $notification->id,
            'sessionId' => $sessionId, // The session of the recipient, for context if needed by client
            'playerId' => $notification->player_id,
            'player' => $playerName,
            'questId' => $notification->quest_id,
            'quest' => $questName,
            'timestamp' => $notification->created_at,
            'message' => $payload['message'] ?? $notification->message ?? '', // Prioritize message from payload
            'payload' => $payload // Original full payload
        ];

        $jsonMessage = json_encode($array);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->log("NotificationService: Failed to encode chat message for notificationId=[{$notification->id}]", $array, 'error');
            // Fallback to a generic error message or empty JSON?
            return json_encode(['type' => 'error', 'message' => 'Failed to prepare chat message.']);
        }

        $this->logger->logEnd("NotificationService: prepareChatMessage");
        return $jsonMessage;
    }

    /**
     * Recovers message history for a specific session.
     * This method might be called by RegistrationHandler or AnnouncePlayerJoinHandler after a player connects/reconnects.
     * It would typically involve fetching relevant notifications (e.g., recent chat messages for the quest)
     * and then using something like BroadcastService to send them to the specific client associated with the session.
     *
     * @param string $sessionId The ID of the session for which to recover history.
     * @param int $questId The ID of the quest to recover history for.
     * @param int $lastTimestamp The timestamp after which messages should be recovered.
     * @param ConnectionInterface $clientConnection The specific client connection to send messages to.
     */
    public function recoverMessageHistoryForSession(string $sessionId, int $questId, int $lastTimestamp, /* ConnectionInterface $clientConnection, BroadcastService $broadcastService */): void {
        $this->logger->logStart("NotificationService: recoverMessageHistory for sessionId=[{$sessionId}], questId=[{$questId}], since=[{$lastTimestamp}]");

        $chatNotifications = $this->getNotifications($questId, 'chat', $lastTimestamp);
        $notificationCount = count($chatNotifications);
        $this->logger->log("NotificationService: Processing {$notificationCount} 'chat' notifications for history recovery for sessionId=[{$sessionId}].");

        $recoveredMessagesCount = 0;
        foreach ($chatNotifications as $notification) {
            $this->logger->log("NotificationService: Preparing historical chat message: Notification.id=[{$notification->id}]");
            $chatMessageJson = $this->prepareChatMessage($notification, $sessionId);

            // In a real scenario, you would use BroadcastService here to send to the specific client
            // $broadcastService->sendToClient($clientConnection, json_decode($chatMessageJson, true));
            // For now, just log what would happen:
            $this->logger->log("NotificationService: Would send historical message to client for session [{$sessionId}]", ['message' => json_decode($chatMessageJson, true)]);
            $recoveredMessagesCount++;
        }

        $this->logger->log("NotificationService: Finished recovering history for session [{$sessionId}]. Sent {$recoveredMessagesCount} message(s).");
        $this->logger->logEnd("NotificationService: recoverMessageHistory for sessionId=[{$sessionId}]");
    }
}
