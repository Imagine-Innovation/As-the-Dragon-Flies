<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface; // Updated
use Ratchet\ConnectionInterface;
// Assuming LoggerService, QuestSessionManager, BroadcastService will be in this namespace or properly aliased
// use common\extensions\EventHandler\LoggerService;
use common\extensions\EventHandler\QuestSessionManager; // Import QuestSessionManager
// use common\extensions\EventHandler\BroadcastService; // Placeholder

class AttachmentHandler implements SpecificMessageHandlerInterface {

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
        $this->logger->logStart("AttachmentHandler: handle clientId=[{$clientId}], sessionId=[{$sessionId}]", $data);

        // Use QuestSessionManager to register the session
        $playerId = $data['playerId'] ?? null;
        $questId = $data['questId'] ?? null; // Assuming questId might be part of $data
        $registered = $this->questSessionManager->registerSession($sessionId, $playerId, $questId, $clientId, $data);
        // $registered = !is_null($playerId); // Simplified placeholder logic
        $this->logger->log("AttachmentHandler: Session registration via QuestSessionManager. Result: " . ($registered ? 'Success' : 'Failure'));


        if ($registered) {
            $this->broadcastService->sendBack($from, 'connected', "Client ID [{$clientId}] attached to session [{$sessionId}]");
        } else {
            $this->logger->log("AttachmentHandler: Unable to register/find session Id [{$sessionId}] for clientId=[{$clientId}]", $data, 'warning');
            $this->broadcastService->sendBack($from, 'error', "Unable to find session Id [{$sessionId}]");
        }

        $this->logger->logEnd("AttachmentHandler: handle clientId=[{$clientId}]");
    }
}
