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

        $payload = $data['payload'];
        $playerName = (string) $payload['playerName'] ?? 'Unknown';
        $questId = (int) $payload['questId'] ?? null;
        $questName = (string) $payload['questName'] ?? 'Unknown';

        if ($questId === null || $questId === '' || $questName === 'Unknown') {
            $this->logger->log("AnnouncePlayerJoinHandler: Missing questId, or questName in data['payload'].", $data, 'warning');
            $errorDto = $this->messageFactory->createErrorMessage("Invalid player join announcement: questId, or questName missing within payload.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("AnnouncePlayerJoinHandler: handle");
            return;
        }

        $playerJoinedDto = $this->messageFactory->createPlayerJoinedMessage($playerName, $sessionId, $questName);
        $this->broadcastService->broadcastToQuest($questId, $playerJoinedDto, $sessionId);

        $this->broadcastService->recoverMessageHistory($sessionId);

        $this->broadcastService->sendBack($from, 'ack', ['type' => 'announce_player_join_processed', 'playerName' => $playerName, 'questId' => $questId, 'questName' => $questName]);

        $this->logger->logEnd("AnnouncePlayerJoinHandler: handle");
    }
}
