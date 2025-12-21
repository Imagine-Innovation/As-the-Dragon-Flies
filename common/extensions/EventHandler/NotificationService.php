<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\dtos\NewMessageDto;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use common\models\Notification;

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

    public function broadcast(int $questId, array $data, ?string $excludeSessionId = null, ?int $userId = null): void {
        $this->logger->logStart("NotificationService: broadcast questId=[{$questId}], excludeSessionId=[{$excludeSessionId}], userId=[{$userId}]");

        // Assuming $userId is equivalent to player_id for the notification
        $playerId = $userId ?? ($data['playerId'] ?? null);
        if (!$playerId) {
            $this->logger->log("NotificationService: Player ID not provided for notification.", $data, 'error');
            return;
        }

        $payload = $data['payload'];
        if ($data['type'] === 'new-message') {
            $message = $data['message'] ?? ($payload['message'] ?? 'I had something to say, but I completely forgot!');
            $sender = $data['sender_name'] ?? ($payload['playerName'] ?? 'Unknown Sender');
            $this->logger->log("NotificationService: broadcast - createNewMessage({$message}, {$sender})");
            $messageDto = $this->messageFactory->createNewMessage($message, $sender);
        } else {
            $this->logger->log("NotificationService: create messageDto -> Notification type={$data['type']}");
            $payload['sessionId'] = $data['sessionId']; // ensure sessionId is in the payload
            $messageDto = $this->messageFactory->createMessage($data['type'], $payload);
        }
        $this->logger->log("NotificationService: Message DTO [{$data['type']}] broadcasted for questId=[{$questId}]");
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
            $notifications = null;
            $this->logger->log("NotificationService: Exception while getting notifications. Error: " . $e->getMessage(), $e->getTraceAsString(), 'error');
        }

        $this->logger->logEnd("NotificationService: getNotifications");
        return $notifications;
    }

    /**
     * Prepares a NewMessageDto from a Notification model.
     */
    public function prepareNewMessageDto(Notification $notification, string $sessionId): NewMessageDto {
        $this->logger->log("NotificationService: Preparing NewMessageDto for Notification ID: {$notification->id}, Session ID: {$sessionId}");

        $payload = json_decode($notification->payload, true);
        $senderDisplayName = $notification->initiator->name; // Adjust according to your Player model's name attribute
        // Message content can come from notification's message field or a specific field in payload
        $messageContent = $payload['message'] ?? $notification->message;

        return $this->messageFactory->createNewMessage($messageContent, $senderDisplayName);
    }
}
