<?php

namespace common\extensions\EventHandler\handlers;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use Ratchet\ConnectionInterface;

class PlayerJoiningHandler implements SpecificMessageHandlerInterface
{

    private LoggerService $logger;
    private BroadcastServiceInterface $broadcastService;
    private BroadcastMessageFactory $messageFactory;

    /**
     *
     * @param LoggerService $logger
     * @param BroadcastServiceInterface $broadcastService
     * @param BroadcastMessageFactory $messageFactory
     */
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
     * Handles player-joining messages.
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $sessionId
     * @param array<string, mixed> $data
     * @return void
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("PlayerJoiningHandler: handle for session {$sessionId}, client {$clientId}", $data);

        $payload = $data['payload'];
        $playerName = $payload['playerName'] ?? 'Unknown';
        $questId = $payload['questId'] ?? null;
        $questName = $payload['questName'] ?? 'Unknown';

        if ($questId === null || $questId === '' || $questName === 'Unknown') {
            $this->logger->log("PlayerJoiningHandler: Missing questId, or questName in data['payload'].", $data, 'warning');
            $errorDto = $this->messageFactory->createErrorMessage("Invalid player join announcement: questId, or questName missing within payload.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("PlayerJoiningHandler: handle");
            return;
        }

        $playerJoinedDto = $this->messageFactory->createPlayerJoinedMessage($playerName, $sessionId, $questName);
        $this->broadcastService->broadcastToQuest($questId, $playerJoinedDto, $sessionId);

        $this->broadcastService->recoverMessageHistory($sessionId);

        $this->broadcastService->sendBack($from, 'ack', ['type' => 'player-joining_processed', 'playerName' => $playerName, 'questId' => $questId, 'questName' => $questName]);

        $this->logger->logEnd("PlayerJoiningHandler: handle");
    }
}
