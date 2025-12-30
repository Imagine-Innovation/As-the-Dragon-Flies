<?php

namespace common\extensions\EventHandler\handlers;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use Ratchet\ConnectionInterface;

class PlayerQuittingHandler implements SpecificMessageHandlerInterface
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
     * Handles player-quitting messages.
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $sessionId
     * @param array<string, mixed> $data
     * @return void
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("PlayerQuittingHandler: handle for session {$sessionId}, client {$clientId}", $data);

        $payload = $data['payload'];
        $playerName = array_key_exists('playerName', $payload) ? $payload['playerName'] : 'Unknown';
        $questId = array_key_exists('questId', $payload) ? (int) $payload['questId'] : null;
        $questName = array_key_exists('questName', $payload) ? $payload['questName'] : 'Unknown';
        $reason = array_key_exists('reason', $payload) ? $payload['reason'] : 'Unknown';

        if ($questId === null || $questName === 'Unknown') {
            $this->logger->log("PlayerQuittingHandler: Missing questId, or questName in data['payload'].", $data, 'warning');
            $errorDto = $this->messageFactory->createErrorMessage("Invalid player leaving announcement: questId, or questName missing within payload.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("PlayerQuittingHandler: handle");
            return;
        }

        $PlayerQuitDto = $this->messageFactory->createPlayerQuitMessage($playerName, $sessionId, $questName, $reason);
        $this->broadcastService->broadcastToQuest($questId, $PlayerQuitDto, $sessionId);

        $this->broadcastService->sendBack($from, 'ack', ['type' => 'player-quitting_processed', 'playerName' => $playerName, 'questId' => $questId, 'questName' => $questName, 'reason' => $reason]);

        $this->logger->logEnd("PlayerQuittingHandler: handle");
    }
}
