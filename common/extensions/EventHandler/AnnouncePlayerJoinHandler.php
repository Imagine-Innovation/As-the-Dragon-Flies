<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface; // Updated
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use Ratchet\ConnectionInterface;

class AnnouncePlayerJoinHandler implements SpecificMessageHandlerInterface {

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
     * Handles announce_player_join messages.
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("AnnouncePlayerJoinHandler: handle for session {$sessionId}, client {$clientId}", $data);
        
        // $data is the top-level message received. 'playerName' and 'questId' should be direct keys in $data.
        // If they are nested under a 'payload' key, adjust $data['payload']['playerName'] accordingly.
        // Based on the new handle method in the prompt, 'playerName' and 'questId' are expected in $data directly.
        // $playerName = $data['playerName'] ?? null; // Old way
        // $questId = $data['questId'] ?? null; // Old way

        if (empty($data['payload']['playerName']) || 
            !isset($data['payload']['questId']) || $data['payload']['questId'] === '' ||
            empty($data['payload']['questName'])) {
            $this->logger->log("AnnouncePlayerJoinHandler: Missing playerName, questId, or questName in data['payload'].", $data, 'warning');
            $errorDto = $this->messageFactory->createErrorMessage("Invalid player join announcement: playerName, questId, or questName missing within payload.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("AnnouncePlayerJoinHandler: handle");
            return;
        }

        $playerName = (string)$data['payload']['playerName'];
        $questId = (int)$data['payload']['questId'];
        $questName = (string)$data['payload']['questName'];

        $playerJoinedDto = $this->messageFactory->createPlayerJoinedMessage(
            $playerName,
            $sessionId,
            $questName
        );

        $this->broadcastService->broadcastToQuest(
            $questId,
            $playerJoinedDto,
            $sessionId // Exclude the sender's session from this specific broadcast
        );
        
        $this->logger->log("AnnouncePlayerJoinHandler: PlayerJoinedDto broadcasted", ['questId' => $questId, 'playerName' => $playerName, 'sessionId' => $sessionId, 'questName' => $questName]);

        // --- HISTORY RECOVERY for the joining client ---
        // This part remains crucial for the joining client to get recent messages.
        if (is_numeric($questId)) { // ensure questId is valid before attempting recovery
            $this->logger->log("AnnouncePlayerJoinHandler: Attempting to recover message history for session [{$sessionId}] in quest [{$questId}].", $data, 'info');
            $this->broadcastService->recoverMessageHistory($sessionId);
        } else {
            // This case should ideally not be reached if questId is validated above.
            $this->logger->log("AnnouncePlayerJoinHandler: Skipping message history recovery for session [{$sessionId}] due to invalid questId (numeric check failed).", $data, 'warning');
        }
        // --- END HISTORY RECOVERY ---

        // Send an acknowledgement back to the sender client
        $this->broadcastService->sendBack($from, 'ack', ['type' => 'announce_player_join_processed', 'playerName' => $playerName, 'questId' => $questId, 'questName' => $questName]);

        $this->logger->logEnd("AnnouncePlayerJoinHandler: handle");
    }
}
