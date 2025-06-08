<?php

namespace common\extensions\EventHandler;

use Ratchet\ConnectionInterface;
// use common\extensions\EventHandler\LoggerService;
// use common\extensions\EventHandler\BroadcastService; // Placeholder
// use common\extensions\EventHandler\NotificationService; // Placeholder for recoverMessageHistory

class AnnouncePlayerJoinHandler implements SpecificMessageHandlerInterface {

    private LoggerService $logger;
    private BroadcastService $broadcastService; // Updated
    // private NotificationService $notificationService; // Placeholder - recoverMessageHistory is in BroadcastService

    public function __construct(
        LoggerService $logger,
        BroadcastService $broadcastService // Added
        /*, NotificationService $notificationService */
    ) {
        $this->logger = $logger;
        $this->broadcastService = $broadcastService;
        // $this->notificationService = $notificationService;
    }

    /**
     * Handles announce_player_join messages.
     * Original logic from EventHandler::handleAnnouncePlayerJoin
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("AnnouncePlayerJoinHandler: handle from clientId=[{$clientId}], sessionId=[{$sessionId}]", $data);
        
        $payload = $data['payload'] ?? null;
        $questId = $payload['questId'] ?? null;

        if (!$payload || !is_numeric($questId)) {
            $this->logger->log("AnnouncePlayerJoinHandler: Missing payload or invalid questId.", $data, 'warning');
            // $this->broadcastService->sendToClient($from, ['type' => 'error', 'message' => 'Invalid announce_player_join message: missing payload or questId.']);
            $this->logger->log("AnnouncePlayerJoinHandler: Would send 'error' for invalid message", ['clientId' => $clientId]);
            $this->logger->logEnd("AnnouncePlayerJoinHandler: handle");
            return;
        }

        // The client sends data like {'playerId': ..., 'playerName': ..., 'questId': ..., 'questName': ..., 'joinedAt': ...}
        // This entire structure is in $payload.
        $broadcastMessage = [
            'type' => 'new_player_joined', // This is the WebSocket message type clients will receive
            'notificationId' => 'event_' . uniqid(), // A unique ID for this event instance
            'triggerSessionId' => $sessionId, // The sessionId of the player whose client triggered this announcement
            'triggerPlayerId' => $payload['playerId'] ?? null, // The playerId of the new player
            'questId' => (int) $questId,
            'timestamp' => time(),
            'payload' => $payload // This nested payload contains playerName, questName, joinedAt etc.
        ];

        $this->logger->log("AnnouncePlayerJoinHandler: Broadcasting 'new_player_joined' event for questId=[{$questId}] triggered by sessionId=[{$sessionId}]", $broadcastMessage);
        $this->broadcastService->broadcastToQuest((int)$questId, $broadcastMessage, $sessionId);

        // --- HISTORY RECOVERY ---
        if ($questId) {
            $this->logger->log("AnnouncePlayerJoinHandler: Attempting to recover message history for session [{$sessionId}] in quest [{$questId}].", $payload, 'info');
            $this->broadcastService->recoverMessageHistory($sessionId);
        } else {
            $this->logger->log("AnnouncePlayerJoinHandler: Skipping message history recovery for session [{$sessionId}] due to missing questId in payload.", $payload, 'warning');
        }
        // --- END HISTORY RECOVERY ---

        // Send an acknowledgement back to the sender client
        $this->broadcastService->sendBack($from, 'ack', ['type' => 'announce_player_join_processed', 'originalPayload' => $payload]);

        $this->logger->logEnd("AnnouncePlayerJoinHandler: handle");
    }
}
