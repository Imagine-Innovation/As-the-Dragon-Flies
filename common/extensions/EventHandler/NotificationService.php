<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\dtos\ChatMessageDto;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use common\models\Notification;
use common\models\Player;
use Yii;

class NotificationService
{

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

        $sender = Player::findOne($playerId);
        if (!$sender) {
            $this->logger->log("NotificationService: Player not found for playerId=[{$playerId}] when creating payload.", null, 'warning');
            // Depending on requirements, you might still want to save a notification without sender details,
            // or return null as if it failed. For now, proceeding with "Unknown player".
            // If this is critical, return null here.
        }
        $playerName = $sender ? $sender->name : 'Unknown Player';

        // Construct payload. This might vary based on notification type in a more complex system.
        $type = $data['type'] ?? 'unknown';
        $title = $data['title'] ?? (($type === 'sending-message' || $type === 'new-message') ? "New {$type} from " . $playerName : "Notification");
        //$title = $data['title'] ?? "Notification";
        $message = $data['message'] ?? $title;
        //$payload = QuestMessages::payload($sender, $message);
        $payload = $data['payload'] ?? [];
        $createdAt = $data['timestamp'] ?? time(); // Use timestamp from data if available, fallback to now


        $record = [
            'initiator_id' => $playerId ?? 1,
            'quest_id' => $questId,
            'notification_type' => $type,
            'title' => $title ?? 'Unknown',
            'message' => $message,
            'created_at' => $createdAt,
            'is_private' => $data['is_private'] ?? 0, // Default to public
            'payload' => json_encode($payload),
        ];

        $this->logger->log("NotificationService: saveNotification new record ", $record);
        Yii::debug("*** debug *** NotificationService: saveNotification new record " . print_r($record, true));
        $notification = new Notification($record);

        try {
            $notification->save();
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
     * @param string|null $excludeSessionId Session to exclude from broadcast
     * @param int|null $userId User initiating the action
     * @return Notification|null
     */
    public function xxxcreateNotificationAndBroadcast(int $questId, array $data, ?string $excludeSessionId = null, ?int $userId = null): ?Notification {
        $this->logger->logStart("NotificationService: createNotificationAndBroadcast questId=[{$questId}]", ['data' => $data, 'excludeSessionId' => $excludeSessionId, 'userId' => $userId]);

        // Assuming $userId is equivalent to player_id for the notification
        $playerId = $userId ?? ($data['playerId'] ?? null);
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

        if ($notificationModel->notification_type === 'new-message') {
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
            //$this->logger->log("NotificationService: Notification type [{$notificationModel->notification_type}] not handled by DTO broadcast in this example (['notificationId' => {$notificationModel->id}])");
            $this->logger->log("NotificationService: From notification model, Notification type={$notificationModel->notification_type}, payload)", $notificationModel->payload);
            $this->logger->log("NotificationService: create massageDto -> Notification type={$data['type']}, payload=", $data['payload']);
            $payload = $data['payload'];
            $payload['sessionId'] = $data['sessionId']; // ensure sessionId is in the payload
            $messageDto = $this->messageFactory->createMessage($data['type'], $payload);

            $this->broadcastService->broadcastToQuest((int) $notificationModel->quest_id, $messageDto, $excludeSessionId);
            $this->logger->log("NotificationService: Message DTO broadcasted for questId=[{$notificationModel->quest_id}]", ['type' => $messageDto->getType()]);
        }

        $this->logger->logEnd("NotificationService: createNotificationAndBroadcast");
        return $notificationModel;
    }

    public function createNotificationAndBroadcast(int $questId, array $data, ?string $excludeSessionId = null, ?int $userId = null): void {
        $this->logger->logStart("NotificationService: createNotificationAndBroadcast questId=[{$questId}]", ['data' => $data, 'excludeSessionId' => $excludeSessionId, 'userId' => $userId]);

        // Assuming $userId is equivalent to player_id for the notification
        $playerId = $userId ?? ($data['playerId'] ?? null);
        if (!$playerId) {
            $this->logger->log("NotificationService: Player ID not provided for notification.", $data, 'error');
            // Depending on requirements, might create an error DTO and send back to originator if possible
            return;
        }

        $payload = $data['payload'];
        if ($data['type'] === 'new-message') {
            $messageDto = $this->messageFactory->createChatMessage(
                    $data['message'] ?? $payload['message'],
                    $data['sender_name'] ?? $payload['playerName'] ?? 'Unknown Sender'
            );
        } else {
            // Handle other notification types or non-DTO broadcasts if necessary
            $this->logger->log("NotificationService: create massageDto -> Notification type={$data['type']}, payload=", $data['payload']);
            $payload['sessionId'] = $data['sessionId']; // ensure sessionId is in the payload
            $messageDto = $this->messageFactory->createMessage($data['type'], $payload);
        }
        $this->broadcastService->broadcastToQuest($questId, $messageDto, $excludeSessionId);
        $this->logger->log("NotificationService: Message DTO broadcasted for questId=[{$questId}]", ['type' => $messageDto->getType()]);

        $this->logger->logEnd("NotificationService: createNotificationAndBroadcast");
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
