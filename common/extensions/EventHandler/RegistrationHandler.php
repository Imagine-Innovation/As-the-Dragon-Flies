<?php

namespace common\extensions\EventHandler;

use Ratchet\ConnectionInterface;
// use common\extensions\EventHandler\LoggerService;
use common\extensions\EventHandler\QuestSessionManager; // Import QuestSessionManager
// use common\extensions\EventHandler\BroadcastService; // Placeholder
// use common\extensions\EventHandler\NotificationService; // Placeholder for recoverMessageHistory

class RegistrationHandler implements SpecificMessageHandlerInterface {

    private LoggerService $logger;
    private QuestSessionManager $questSessionManager; // Use QuestSessionManager
    private BroadcastService $broadcastService; // Updated
    // private NotificationService $notificationService; // Placeholder - recoverMessageHistory is in BroadcastService now

    public function __construct(
        LoggerService $logger,
        QuestSessionManager $questSessionManager, // Inject QuestSessionManager
        BroadcastService $broadcastService // Added
        /*, NotificationService $notificationService */
    ) {
        $this->logger = $logger;
        $this->questSessionManager = $questSessionManager;
        $this->broadcastService = $broadcastService;
        // $this->notificationService = $notificationService;
    }

    /**
     * Handles registration messages.
     * Original logic from EventHandler::handleRegistration
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("RegistrationHandler: handle clientId=[{$clientId}], sessionId=[{$sessionId}]", $data);

        $playerId = $data['playerId'] ?? null;
        if (!$playerId) {
            // $this->broadcastService->sendToClient($from, ['type' => 'error', 'message' => 'Missing playerId in registration']);
            $this->logger->log("RegistrationHandler: Missing playerId in registration data for clientId=[{$clientId}]. Would send error.", $data, 'warning');
            $this->logger->logEnd("RegistrationHandler: handle clientId=[{$clientId}]");
            return;
        }

        $questId = $data['questId'] ?? null;

        // Use QuestSessionManager to register the session
        $this->questSessionManager->registerSession($sessionId, $playerId, $questId, $clientId, $data);
        $this->logger->log("RegistrationHandler: Session registration via QuestSessionManager.", ['sessionId' => $sessionId, 'playerId' => $playerId, 'questId' => $questId, 'clientId' => $clientId]);

        if ($questId) {
            $this->broadcastService->broadcastToQuest((int)$questId, $data, $sessionId);
            $this->broadcastService->recoverMessageHistory($sessionId);
        }

        // Send acknowledgment using sendToConnection via BroadcastService
        $ackMessage = json_encode([
            'type' => 'connected',
            'playerId' => $playerId,
            'timestamp' => time()
        ]);
        if (json_last_error() === JSON_ERROR_NONE) {
            $this->broadcastService->sendToConnection($from, $ackMessage);
        } else {
            $this->logger->log("RegistrationHandler: Failed to encode 'connected' ack message", ['playerId' => $playerId], 'error');
        }
        $this->logger->logEnd("RegistrationHandler: handle clientId=[{$clientId}]");
    }
}
