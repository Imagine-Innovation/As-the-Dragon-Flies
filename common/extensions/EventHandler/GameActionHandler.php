<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface; // Updated
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use Ratchet\ConnectionInterface;

class GameActionHandler implements SpecificMessageHandlerInterface {

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
     * Handles game action messages.
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("GameActionHandler: handle from clientId={$clientId}, sessionId={$sessionId}", $data);

        if (!isset($data['action_type'], $data['details'], $data['quest_id'])) {
            $this->logger->log("GameActionHandler: Missing required data (action_type, details, quest_id).", $data, 'warning');
            $errorDto = $this->messageFactory->createErrorMessage("Invalid game action data provided.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("GameActionHandler: handle");
            return;
        }

        $gameActionDto = $this->messageFactory->createGameActionMessage(
            (string)$data['action_type'],
            (array)$data['details']
        );

        $this->broadcastService->broadcastToQuest(
            (int)$data['quest_id'],
            $gameActionDto,
            $sessionId // Exclude the sender from this broadcast
        );
        
        $this->logger->log("GameActionHandler: GameActionDto broadcasted", ['quest_id' => $data['quest_id'], 'action_type' => $data['action_type']]);

        // Send an acknowledgement back to the sender client
        $this->broadcastService->sendBack($from, 'action_ack', ['status' => 'success', 'action_type' => $data['action_type']]);

        $this->logger->logEnd("GameActionHandler: handle");
    }
}
