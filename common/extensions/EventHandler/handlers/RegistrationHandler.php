<?php

namespace common\extensions\EventHandler\handlers;

use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\LoggerService;
use common\extensions\EventHandler\BroadcastService;
use common\extensions\EventHandler\QuestSessionManager;
use Ratchet\ConnectionInterface;

// use common\extensions\EventHandler\BroadcastService; // Placeholder

class RegistrationHandler implements SpecificMessageHandlerInterface {

    private LoggerService $logger;
    private QuestSessionManager $questSessionManager; // Use QuestSessionManager
    private BroadcastService $broadcastService; // Updated

    public function __construct(
            LoggerService $logger,
            QuestSessionManager $questSessionManager, // Inject QuestSessionManager
            BroadcastService $broadcastService // Added
    ) {
        $this->logger = $logger;
        $this->questSessionManager = $questSessionManager;
        $this->broadcastService = $broadcastService;
    }

    /**
     * Handles attachment messages.
     * Original logic from EventHandler::handleAttachment
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("RegistrationHandler: handle clientId=[{$clientId}], sessionId=[{$sessionId}]", $data);

        // Use QuestSessionManager to register the session
        $playerId = $data['playerId'] ?? null;
        $questId = $data['questId'] ?? null; // Assuming questId might be part of $data
        $registered = $this->questSessionManager->registerSession($sessionId, $playerId, $questId, $clientId, $data);
        // $registered = !is_null($playerId); // Simplified placeholder logic
        $this->logger->log("RegistrationHandler: Session registration via QuestSessionManager. Result: " . ($registered ? 'Success' : 'Failure'));

        if ($registered) {
            $this->broadcastService->sendBack($from, 'connected', "Client ID [{$clientId}] attached to session [{$sessionId}]");
        } else {
            $this->logger->log("RegistrationHandler: Unable to register/find session Id [{$sessionId}] for clientId=[{$clientId}]", $data, 'warning');
            $this->broadcastService->sendBack($from, 'error', "Unable to find session Id [{$sessionId}]");
        }

        $this->logger->logEnd("RegistrationHandler: handle clientId=[{$clientId}]");
    }
}
