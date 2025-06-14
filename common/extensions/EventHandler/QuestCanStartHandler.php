<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use Ratchet\ConnectionInterface;

class QuestCanStartHandler implements SpecificMessageHandlerInterface {

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
        $this->logger->logStart("QuestCanStartHandler: handle for session {$sessionId}, client {$clientId}", $data);

        $payload = $data['payload'];
        $questId = (int) $payload['questId'] ?? null;
        $questName = (string) $payload['questName'] ?? 'Unknown';

        if ($questId === null || $questId === '' || $questName === 'Unknown') {
            $this->logger->log("QuestCanStartHandler: Missing questId, or questName in data['payload'].", $data, 'warning');
            $errorDto = $this->messageFactory->createErrorMessage("Invalid player join announcement: questId, or questName missing within payload.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("QuestCanStartHandler: handle");
            return;
        }

        $questCanStartDto = $this->messageFactory->createQuestCanStartMessage($sessionId, $questName);
        $this->broadcastService->broadcastToQuest($questId, $questCanStartDto, $sessionId);

        $this->broadcastService->sendBack($from, 'ack', ['type' => 'quest_can_start', 'questId' => $questId, 'questName' => $questName]);

        $this->logger->logEnd("QuestCanStartHandler: handle");
    }
}
