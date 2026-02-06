<?php

namespace common\extensions\EventHandler\handlers;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use common\helpers\PayloadHelper;
use Ratchet\ConnectionInterface;

class GameActionHandler implements SpecificMessageHandlerInterface
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
        BroadcastMessageFactory $messageFactory,
    ) {
        $this->logger = $logger;
        $this->broadcastService = $broadcastService;
        $this->messageFactory = $messageFactory;
    }

    /**
     * Handles game action messages.
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $sessionId
     * @param array<string, mixed> $data
     * @return void
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void
    {
        $this->logger->logStart("GameActionHandler: handle from clientId={$clientId}, sessionId={$sessionId}", $data);

        $payload = PayloadHelper::extractPayloadFromData($data);
        $questId = PayloadHelper::extractIntFromPayload('questId', $payload, $data);
        $playerName = PayloadHelper::extractStringFromPayload('playerName', $payload);
        $action = PayloadHelper::extractStringFromPayload('action', $payload);

        /** @var array<string, mixed> */
        $detail = PayloadHelper::extractArrayFromPayload('detail', $payload);

        if ($questId === null || $action === 'Unknown') {
            $this->logger->log(
                'GameActionHandler: Missing required data (questId, playerName, action).',
                $data,
                'warning',
            );
            $errorDto = $this->messageFactory->createErrorMessage('Invalid game action data provided.');
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd('GameActionHandler: handle');
            return;
        }

        $gameActionDto = $this->messageFactory->createGameActionMessage($playerName, $action, $detail);

        $this->broadcastService->broadcastToQuest($questId, $gameActionDto, $sessionId);

        $this->logger->log('GameActionHandler: GameActionDto broadcasted', [
            'quest_id' => $questId,
            'payload' => $payload,
        ]);
        $this->broadcastService->sendBack($from, 'ack', [
            'type' => 'game-action_processed',
            'playerName' => $playerName,
            'action' => $action,
            'detail' => $detail,
        ]);

        $this->logger->logEnd('GameActionHandler: handle');
    }
}
