<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\dtos\ChatMessageDto;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService; // Ensured LoggerService is used
use common\models\Notification;
use common\models\Player;
use common\models\QuestChat;
use frontend\components\QuestMessages;

class NotificationService {

    private LoggerService $logger;
    private BroadcastServiceInterface $broadcastService;
    private BroadcastMessageFactory $messageFactory;

    public function __construct(
            LoggerService $logger,
            BroadcastServiceInterface $broadcastService,
            BroadcastMessageFactory $messageFactory
    ) {
        $this->logger = $logger;
        $this->broadcastService = $broadcastService;
        $this->messageFactory = $messageFactory;
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

        $sender = Player::findOne($playerId);
        if (!$sender) {
            $this->logger->log("NotificationService: Player not found for playerId=[{$playerId}] when creating payload.", null, 'warning');
            // Depending on requirements, you might still want to save a notification without sender details,
            // or return null as if it failed. For now, proceeding with "Unknown player".
            // If this is critical, return null here.
        }
        $playerName = $sender ? $sender->name : 'Unknown Player';

        // Construct payload. This might vary based on notification type in a more complex system.
        $payload = QuestMessages::payload($sender, $message);
        $title = $data['title'] ?? ($type === 'chat' ? "New chat message from " . $playerName : "Notification");

        $notification = new Notification([
            'initiator_id' => $playerId,
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
     * Example of adapting an existing method that creates a notification and then broadcasts it.
     * The exact signature and logic depend on your existing `createNotificationAndBroadcast` method.
     * This is a hypothetical adaptation.
     *
     * @param int $questId
     * @param array $data Contains message details, including potentially 'sender_name'
     * @param string $type Notification type, e.g., 'chat'
     * @param string|null $excludeSessionId Session to exclude from broadcast
     * @param int|null $userId User initiating the action
     * @return Notification|null
     */
    public function createNotificationAndBroadcast(int $questId, array $data, string $type, ?string $excludeSessionId = null, ?int $userId = null): ?Notification {
        $this->logger->logStart("NotificationService: createNotificationAndBroadcast questId=[{$questId}], type=[{$type}]", ['data' => $data, 'excludeSessionId' => $excludeSessionId, 'userId' => $userId]);

        // Assuming $userId is equivalent to player_id for the notification
        $playerId = $userId ?? ($data['player_id'] ?? null);
        if (!$playerId) {
            $this->logger->log("NotificationService: Player ID not provided for notification.", $data, 'error');
            // Depending on requirements, might create an error DTO and send back to originator if possible
            return null;
        }

        // Create and save the notification model
        // This reuses the saveNotification method, which already handles logging for that part.
        $notificationModel = $this->saveNotification($playerId, $questId, $data);

        if (!$notificationModel) {
            $this->logger->log("NotificationService: Failed to save notification model, aborting broadcast.", $data, 'error');
            $this->logger->logEnd("NotificationService: createNotificationAndBroadcast");
            return null;
        }

        // If notification type is 'chat' (or other types you want to broadcast using DTOs)
        if ($notificationModel->notification_type === 'chat') {
            // $data is assumed to have a 'sender_name' key from the original message context
            // Or, derive senderName from $notificationModel->player if relation is loaded and preferred.
            $senderName = $data['sender_name'] ?? $notificationModel->player->name ?? 'Unknown Sender';

            $chatDto = $this->messageFactory->createChatMessage(
                    $notificationModel->message,
                    $senderName
            );

            $this->broadcastService->broadcastToQuest((int) $notificationModel->quest_id, $chatDto, $excludeSessionId);
            $this->logger->log("NotificationService: Chat DTO broadcasted for questId=[{$notificationModel->quest_id}]", ['type' => $chatDto->getType()]);
        } else {
            // Handle other notification types or non-DTO broadcasts if necessary
            $this->logger->log("NotificationService: Notification type [{$notificationModel->notification_type}] not handled by DTO broadcast in this example.", ['notificationId' => $notificationModel->id]);
        }

        $this->logger->logEnd("NotificationService: createNotificationAndBroadcast");
        return $notificationModel;
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
     * Prepares a ChatMessageDto from a Notification model.
     */
    public function prepareChatMessageDto(Notification $notification, string $sessionId): ChatMessageDto {
        $this->logger->log("NotificationService: Preparing ChatMessageDto for Notification ID: {$notification->id}, Session ID: {$sessionId}");

        $payload = json_decode($notification->payload, true);
        $senderDisplayName = $notification->initiator->name; // Adjust according to your Player model's name attribute
        // Message content can come from notification's message field or a specific field in payload
        $messageContent = $payload['message'] ?? $notification->message;

        return $this->messageFactory->createChatMessage($messageContent, $senderDisplayName);
    }
}
