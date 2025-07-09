<?php

namespace common\extensions\EventHandler\handlers;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use Ratchet\ConnectionInterface;

class PlayerLeavingHandler implements SpecificMessageHandlerInterface {

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
     * Handles player_joining messages.
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("PlayerLeavingHandler: handle for session {$sessionId}, client {$clientId}", $data);

        $payload = $data['payload'];
        $playerName = (string) $payload['playerName'] ?? 'Unknown';
        $questId = (int) $payload['questId'] ?? null;
        $questName = (string) $payload['questName'] ?? 'Unknown';

        if ($questId === null || $questId === '' || $questName === 'Unknown') {
            $this->logger->log("PlayerLeavingHandler: Missing questId, or questName in data['payload'].", $data, 'warning');
            $errorDto = $this->messageFactory->createErrorMessage("Invalid player join announcement: questId, or questName missing within payload.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("PlayerLeavingHandler: handle");
            return;
        }

        $playerLeftDto = $this->messageFactory->createPlayerJoinedMessage($playerName, $sessionId, $questName);
        $this->broadcastService->broadcastToQuest($questId, $playerLeftDto, $sessionId);

        $this->broadcastService->sendBack($from, 'ack', ['type' => 'player_leaving_processed', 'playerName' => $playerName, 'questId' => $questId, 'questName' => $questName]);

        $this->logger->logEnd("PlayerLeavingHandler: handle");
    }
}
