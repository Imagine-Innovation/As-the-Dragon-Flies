<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\dtos\MessageSentDto;
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

        $record = $this->prepareNotificationRecord($playerId, $questId, $playerName, $data);

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

    private function prepareNotificationRecord(int $playerId, int $questId, string $playerName, array $data): array {
        $this->logger->logStart("NotificationService: prepareNotification playerId=[{$playerId}], questId=[{$questId}], playerName=[{$playerName}]");

        // Construct payload. This might vary based on notification type in a more complex system.
        $type = $data['type'] ?? 'unknown';
        $title = $data['title'] ?? (($type === 'message-sent' || $type === 'sending-message') ? "New {$type} from " . $playerName : "Notification");
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

        $this->logger->logEnd("NotificationService: prepareNotification", $record);
        return $record;
    }

    public function broadcast(int $questId, array $data, ?string $excludeSessionId = null, ?int $userId = null): void {
        $this->logger->logStart("NotificationService: broadcast questId=[{$questId}]", ['data' => $data, 'excludeSessionId' => $excludeSessionId, 'userId' => $userId]);

        // Assuming $userId is equivalent to player_id for the notification
        $playerId = $userId ?? ($data['playerId'] ?? null);
        if (!$playerId) {
            $this->logger->log("NotificationService: Player ID not provided for notification.", $data, 'error');
            return;
        }

        $payload = $data['payload'];
        if ($data['type'] === 'sending-message') {
            $message = $data['message'] ?? ($payload['message'] ?? 'I had something to say, but I completely forgot!');
            $sender = $data['sender_name'] ?? ($payload['playerName'] ?? 'Unknown Sender');
            $this->logger->log("NotificationService: broadcast - ccreateMessageSent({$message}, {$sender})");
            $messageDto = $this->messageFactory->createMessageSent($message, $sender);
        } else {
            $this->logger->log("NotificationService: create messageDto -> Notification type={$data['type']}, payload=", $data['payload']);
            $payload['sessionId'] = $data['sessionId']; // ensure sessionId is in the payload
            $messageDto = $this->messageFactory->createMessage($data['type'], $payload);
        }
        $this->logger->log("NotificationService: Message DTO [{$data['type']}] broadcasted for questId=[{$questId}]", $messageDto);
        $this->broadcastService->broadcastToQuest($questId, $messageDto, $excludeSessionId);

        $this->logger->logEnd("NotificationService: broadcast");
    }

    /**
     * Retrieves notifications for a quest.
     * Original logic from EventHandler::getNotifications
     * @return array|null An array of Notification objects.
     */
    public function getNotifications(int $questId, string $type, int $since): ?array {
        $this->logger->logStart("NotificationService: getNotifications for questId=[{$questId}], type=[{$type}], since=[{$since}]");

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
     * Prepares a MessageSentDto from a Notification model.
     */
    public function prepareMessageSentDto(Notification $notification, string $sessionId): MessageSentDto {
        $this->logger->log("NotificationService: Preparing MessageSentDto for Notification ID: {$notification->id}, Session ID: {$sessionId}");

        $payload = json_decode($notification->payload, true);
        $senderDisplayName = $notification->initiator->name; // Adjust according to your Player model's name attribute
        // Message content can come from notification's message field or a specific field in payload
        $messageContent = $payload['message'] ?? $notification->message;

        return $this->messageFactory->createMessageSent($messageContent, $senderDisplayName);
    }
}
