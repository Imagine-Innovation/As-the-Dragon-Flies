<?php

namespace common\extensions\EventHandler;

use Ratchet\ConnectionInterface;
// use common\extensions\EventHandler\LoggerService;
use common\extensions\EventHandler\NotificationService; // Import NotificationService
// use common\extensions\EventHandler\BroadcastService; // Placeholder

class ChatMessageHandler implements SpecificMessageHandlerInterface {

    private LoggerService $logger;
    private NotificationService $notificationService; // Use NotificationService
    private BroadcastService $broadcastService; // Updated

    public function __construct(
        LoggerService $logger,
        NotificationService $notificationService, // Inject NotificationService
        BroadcastService $broadcastService // Added
    ) {
        $this->logger = $logger;
        $this->notificationService = $notificationService;
        $this->broadcastService = $broadcastService;
    }

    /**
     * Handles chat messages.
     * Original logic from EventHandler::handleChatMessage
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("ChatMessageHandler: handle sessionId=[{$sessionId}], clientId=[{$clientId}]", $data);

        $messageText = $data['message'] ?? '';
        $playerId = $data['playerId'] ?? null;
        $questId = $data['questId'] ?? null;

        if (empty($messageText) || $playerId === null || $questId === null) {
            $this->logger->log("ChatMessageHandler: Missing message, playerId, or questId.", $data, 'warning');
            // $this->broadcastService->sendToClient($from, ['type' => 'error', 'message' => 'Invalid chat message data.']);
            $this->logger->log("ChatMessageHandler: Would send 'error' to client", ['clientId' => $clientId]);
            $this->logger->logEnd("ChatMessageHandler: handle sessionId=[{$sessionId}]");
            return;
        }

        // Use NotificationService to save the notification
        // $data already contains 'message', 'playerId', 'questId', and 'type' (implicitly 'chat')
        $notification = $this->notificationService->saveNotification((int)$playerId, (int)$questId, $data);

        if (!$notification) {
            $this->logger->log("ChatMessageHandler: Failed to save notification for chat message.", $data, 'error');
            // $this->broadcastService->sendToClient($from, ['type' => 'error', 'message' => 'Failed to process chat message.']);
            $this->logger->log("ChatMessageHandler: Would send 'error' to client due to notification save failure", ['clientId' => $clientId]);
            $this->logger->logEnd("ChatMessageHandler: handle sessionId=[{$sessionId}]");
            return;
        }
        
        $this->logger->log("ChatMessageHandler: Notification saved, ID: " . $notification->id);

        // Use NotificationService to prepare the message for broadcast
        $broadcastMessageJson = $this->notificationService->prepareChatMessage($notification, $sessionId);
        // The prepareChatMessage method already logs details and returns a JSON string.
        // For broadcasting, we'd typically decode it if the broadcast service expects an array,
        // or send JSON directly if it handles that.
        $broadcastMessageArray = json_decode($broadcastMessageJson, true); 
        
        if (json_last_error() !== JSON_ERROR_NONE) {
             $this->logger->log("ChatMessageHandler: Failed to decode prepared chat message for broadcasting.", ['json' => $broadcastMessageJson], 'error');
             // Decide on error handling, perhaps send an error to the original client
            $this->logger->logEnd("ChatMessageHandler: handle sessionId=[{$sessionId}]");
            return;
        }

        $this->logger->log("ChatMessageHandler: Prepared broadcast message.", $broadcastMessageArray);

        $this->broadcastService->broadcastToQuest((int)$questId, $broadcastMessageArray, $sessionId);

        // Send the structured message back to the original sender for UI consistency
        // $from->send(json_encode($broadcastMessageArray));
        $this->broadcastService->sendToConnection($from, $broadcastMessageJson); // Send the JSON string directly

        $this->logger->logEnd("ChatMessageHandler: handle sessionId=[{$sessionId}]");
    }
}
