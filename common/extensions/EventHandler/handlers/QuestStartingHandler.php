<?php

namespace common\extensions\EventHandler\handlers;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use Ratchet\ConnectionInterface;

class QuestStartingHandler implements SpecificMessageHandlerInterface
{

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

    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("QuestStartingHandler: handle for session {$sessionId}, client {$clientId}", $data);

        $payload = $data['payload'];
        $questId = $payload['questId'] ?? null;
        $questName = $payload['questName'] ?? 'Unknown';

        if ($questId === null) {
            $this->logger->log("QuestStartingHandler: Missing questId in data['payload'].", $data, 'warning');
            $errorDto = $this->messageFactory->createErrorMessage("Invalid quest starting announcement: questId missing within payload.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("QuestStartingHandler: handle");
            return;
        }

        $questStartedDto = $this->messageFactory->createQuestStartedMessage($sessionId, $questName);
        $this->broadcastService->broadcastToQuest($questId, $questStartedDto, $sessionId);

        $this->broadcastService->sendBack($from, 'ack', ['type' => 'quest-starting-processed', 'questId' => $questId, 'questName' => $questName]);

        $this->logger->logEnd("QuestStartingHandler: handle");
    }
}
