<?php

namespace common\extensions\EventHandler\handlers;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use Ratchet\ConnectionInterface;
use common\models\Quest;
use common\components\AppStatus;

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

    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void
    {
        $this->logger->logStart("QuestStartingHandler: handle for session {$sessionId}, client {$clientId}", $data);

        $payload = $data['payload'];
        $questId = (int) ($payload['questId'] ?? 0);

        if (!$questId) {
            $this->logger->log("QuestStartingHandler: Missing questId in data['payload'].", $data, 'warning');
            $errorDto = $this->messageFactory->createErrorMessage("Invalid quest starting announcement: questId missing within payload.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("QuestStartingHandler: handle");
            return;
        }

        $quest = Quest::findOne($questId);
        if (!$quest) {
            $this->logger->log("QuestStartingHandler: Quest not found for questId: {$questId}.", $data, 'error');
            $errorDto = $this->messageFactory->createErrorMessage("Quest not found.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("QuestStartingHandler: handle");
            return;
        }

        $quest->status = AppStatus::PLAYING->value;
        $quest->started_at = time();
        if (!$quest->save()) {
            $this->logger->log("QuestStartingHandler: Failed to save quest status for questId: {$questId}.", $quest->getErrors(), 'error');
            $errorDto = $this->messageFactory->createErrorMessage("Failed to start quest.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("QuestStartingHandler: handle");
            return;
        }

        $questStartedDto = $this->messageFactory->createQuestStartedMessage($quest->id, $quest->story->name);
        $this->broadcastService->broadcastToQuest($quest->id, $questStartedDto, $sessionId);

        $this->broadcastService->sendBack($from, 'ack', ['type' => 'quest_starting_processed', 'questId' => $quest->id, 'questName' => $quest->story->name]);

        $this->logger->logEnd("QuestStartingHandler: handle");
    }
}
