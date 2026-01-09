<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\dtos\NewMessageDto;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use common\models\Notification;
use common\helpers\JsonHelper;
use common\helpers\PayloadHelper;

class NotificationService
{

    private LoggerService $logger;
    private BroadcastServiceInterface $broadcastService;
    private BroadcastMessageFactory $messageFactory;

    /**
     *
     * @param LoggerService $logger
     * @param BroadcastServiceInterface $broadcastService
     * @param BroadcastMessageFactory $messageFactory
     */
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
     *
     * @param int $questId
     * @param array<string, mixed> $data
     * @param string|null $excludeSessionId
     * @param int|null $userId
     * @return void
     */
    public function broadcast(int $questId, array $data, ?string $excludeSessionId = null, ?int $userId = null): void {
        $this->logger->logStart("NotificationService: broadcast questId=[{$questId}], excludeSessionId=[{$excludeSessionId}], userId=[{$userId}]");

        // Assuming $userId is equivalent to player_id for the notification
        $playerId = $userId ?? PayloadHelper::extractIntFromPayload('playerId', $data);
        if (!$playerId) {
            $this->logger->log("NotificationService: Player ID not provided for notification.", $data, 'error');
            $this->logger->logEnd("NotificationService: broadcast");
            return;
        }

        $payload = PayloadHelper::extractPayloadFromData($data);
        $type = PayloadHelper::extractStringFromPayload('type', $data);
        if ($type === 'new-message') {
            $message = PayloadHelper::extractStringFromPayload('message', $payload, 'I had something to say, but I completely forgot!');
            $sender = PayloadHelper::extractStringFromPayload('sender', $payload);

            $this->logger->log("NotificationService: broadcast - createNewMessage({$message}, {$sender})");
            $messageDto = $this->messageFactory->createNewMessage($message, $sender);
        } else {
            $this->logger->log("NotificationService: create messageDto -> Notification type={$type}");
            $payload['sessionId'] = PayloadHelper::extractStringFromPayload('sessionId', $data); // ensure sessionId is in the payload
            $messageDto = $this->messageFactory->createMessage($type, $payload);
        }
        if (!$messageDto) {
            $this->logger->log("NotificationService: No message to broadcast.", $data, 'error');
            $this->logger->logEnd("NotificationService: broadcast");
            return;
        }
        $this->logger->log("NotificationService: Message DTO [{$type}] broadcasted for questId=[{$questId}]");
        $this->broadcastService->broadcastToQuest($questId, $messageDto, $excludeSessionId);

        $this->logger->logEnd("NotificationService: broadcast");
    }

    /**
     * Retrieves notifications for a quest.
     * Original logic from EventHandler::getNotifications
     *
     * @param int $questId
     * @param string $type
     * @param int $since
     * @return Notification[]
     */
    public function getNotifications(int $questId, string $type, int $since): array {
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
            $notifications = [];
            $this->logger->log("NotificationService: Exception while getting notifications. Error: " . $e->getMessage(), $e->getTraceAsString(), 'error');
        }

        $this->logger->logEnd("NotificationService: getNotifications");
        return $notifications;
    }

    /**
     * Prepares a NewMessageDto from a Notification model.
     *
     * @param Notification $notification
     * @param string $sessionId
     * @return NewMessageDto
     */
    public function prepareNewMessageDto(Notification $notification, string $sessionId): NewMessageDto {
        $this->logger->log("NotificationService: Preparing NewMessageDto for Notification ID: {$notification->id}, Session ID: {$sessionId}");

        $payload = JsonHelper::decode($notification->payload);
        $senderDisplayName = $notification->initiator->name;
        $message = PayloadHelper::extractStringFromPayload('message', $payload);
        // Message content can come from notification's message field or a specific field in payload

        $messageContent = empty($message) ? $notification->message : $message;

        return $this->messageFactory->createNewMessage($messageContent, $senderDisplayName ?? 'Unknown');
    }
}
