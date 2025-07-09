<?php

namespace common\extensions\EventHandler\handlers;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\NotificationService;
use common\extensions\EventHandler\LoggerService;
use Ratchet\ConnectionInterface;

class ChatMessageHandler implements SpecificMessageHandlerInterface {

    private LoggerService $logger;
    private NotificationService $notificationService;
    private BroadcastServiceInterface $broadcastService; // Corrected type hint
    private BroadcastMessageFactory $messageFactory;

    public function __construct(
            LoggerService $logger,
            NotificationService $notificationService,
            BroadcastServiceInterface $broadcastService, // Corrected type hint
            BroadcastMessageFactory $messageFactory
    ) {
        $this->logger = $logger;
        $this->notificationService = $notificationService;
        $this->broadcastService = $broadcastService;
        $this->messageFactory = $messageFactory;
    }

    /**
     * Handles chat messages by delegating to NotificationService to create and broadcast.
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("ChatMessageHandler: handle sessionId=[{$sessionId}], clientId=[{$clientId}]", $data);

        $messageText = $data['message'] ?? '';
        // Assuming player_id is passed in $data, consistent with createNotificationAndBroadcast
        // If 'sender_name' is also available in $data, NotificationService can use it.
        $userId = $data['player_id'] ?? ($data['playerId'] ?? null); // Use player_id or playerId from $data
        $questId = $data['quest_id'] ?? ($data['questId'] ?? null); // Use quest_id or questId from $data

        if (empty($messageText) || $userId === null || $questId === null) {
            $this->logger->log("ChatMessageHandler: Missing message, player_id/playerId, or quest_id/questId.", $data, 'warning');
            // Optionally send an error DTO back to the client
            $errorDto = $this->messageFactory->createErrorMessage('Invalid chat message data: message, player ID, or quest ID missing.');
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("ChatMessageHandler: handle sessionId=[{$sessionId}]");
            return;
        }

        // Ensure 'type' is set to 'chat' if NotificationService relies on it within $data
        $data['type'] = 'chat';
        // Pass 'sender_name' if available, otherwise NotificationService will try to determine it.
        // $data['sender_name'] = $data['sender_name'] ?? 'Player ' . $userId; // Example if needed
        // Delegate to NotificationService. It now handles DTO creation and broadcasting.
        $notificationModel = $this->notificationService->createNotificationAndBroadcast(
                (int) $questId,
                $data, // Pass the whole $data array, NotificationService will extract what it needs
                'chat', // Explicitly pass type
                $sessionId, // excludeSessionId
                (int) $userId
        );

        if ($notificationModel) {
            $this->logger->log("ChatMessageHandler: Chat message processed and broadcasted via NotificationService.", ['notificationId' => $notificationModel->id]);
            // Send an ack or the DTO back to the original sender if needed
            // This depends on whether createNotificationAndBroadcast already handles sending to originator
            // For now, we assume broadcastToQuest does not send to excludeSessionId, so sender needs explicit confirmation.
            // Create a DTO for the original sender for consistency (optional, depends on client needs)
            // This step might be redundant if client optimistically displays message.
            // However, sending the confirmed DTO ensures data consistency.
            $senderName = $data['sender_name'] ?? $notificationModel->player->name ?? 'You';
            $chatDto = $this->messageFactory->createChatMessage(
                    $notificationModel->message,
                    $senderName // Or a specific "You" marker if client handles it
            );
            $this->broadcastService->sendToConnection($from, $chatDto->toJson());
        } else {
            $this->logger->log("ChatMessageHandler: Failed to process chat message via NotificationService.", $data, 'error');
            $errorDto = $this->messageFactory->createErrorMessage('Failed to process chat message.');
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
        }

        $this->logger->logEnd("ChatMessageHandler: handle sessionId=[{$sessionId}]");
    }
}
