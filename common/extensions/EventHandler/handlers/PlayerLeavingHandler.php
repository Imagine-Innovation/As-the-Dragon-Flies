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
     * Handles player-leaving messages.
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("PlayerLeavingHandler: handle for session {$sessionId}, client {$clientId}", $data);

        $payload = $data['payload'];
        $playerName = array_key_exists('playerName', $payload) ? $payload['playerName'] : 'Unknown';
        $questId = array_key_exists('questId', $payload) ? (int) $payload['questId'] : null;
        $questName = array_key_exists('questName', $payload) ? $payload['questName'] : 'Unknown';
        $reason = array_key_exists('reason', $payload) ? $payload['reason'] : 'Unknown';

        if ($questId === null || $questId === '' || $questName === 'Unknown') {
            $this->logger->log("PlayerLeavingHandler: Missing questId, or questName in data['payload'].", $data, 'warning');
            $errorDto = $this->messageFactory->createErrorMessage("Invalid player leaving announcement: questId, or questName missing within payload.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("PlayerLeavingHandler: handle");
            return;
        }

        $playerLeftDto = $this->messageFactory->createPlayerLeftMessage($playerName, $sessionId, $questName, $reason);
        $this->broadcastService->broadcastToQuest($questId, $playerLeftDto, $sessionId);

        $this->broadcastService->sendBack($from, 'ack', ['type' => 'player-leaving_processed', 'playerName' => $playerName, 'questId' => $questId, 'questName' => $questName, 'reason' => $reason]);

        $this->logger->logEnd("PlayerLeavingHandler: handle");
    }
}
