<?php

namespace common\extensions\EventHandler\handlers;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use common\helpers\PayloadHelper;
use Ratchet\ConnectionInterface;

class NextMissionHandler implements SpecificMessageHandlerInterface
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
     * Handles game next-mission messages.
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $sessionId
     * @param array<string, mixed> $data
     * @return void
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void
    {
        $this->logger->logStart("NextMissionHandler: handle from clientId={$clientId}, sessionId={$sessionId}", $data);

        $payload = PayloadHelper::extractPayloadFromData($data);
        $questId = PayloadHelper::extractIntFromPayload('questId', $payload, $data);
        $detail = PayloadHelper::extractArrayFromPayload('detail', $payload);

        if ($questId === null || empty($detail)) {
            $this->logger->log('NextMissionHandler: Missing required data (questId, detail).', $data, 'warning');
            $errorDto = $this->messageFactory->createErrorMessage('Invalid next mission data provided.');
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd('NextMissionHandler: handle');
        }

        $nextMissionDto = $this->messageFactory->createNextMissionMessage($detail);

        $this->broadcastService->broadcastToQuest((int) $questId, $nextMissionDto, $sessionId);

        $this->logger->log('NextMissionHandler: NextMissionDto broadcasted', [
            'quest_id' => $questId,
            'payload' => $payload,
        ]);
        $this->broadcastService->sendBack($from, 'ack', ['type' => 'next-mission_processed', 'detail' => $detail]);

        $this->logger->logEnd('NextMissionHandler: handle');
    }
}
