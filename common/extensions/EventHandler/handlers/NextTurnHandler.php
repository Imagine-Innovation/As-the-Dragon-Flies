<?php

namespace common\extensions\EventHandler\handlers;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use common\helpers\PayloadHelper;
use Ratchet\ConnectionInterface;

class NextTurnHandler implements SpecificMessageHandlerInterface
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
     * Handles game next-turn messages.
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $sessionId
     * @param array<string, mixed> $data
     * @return void
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("NextTurnHandler: handle from clientId={$clientId}, sessionId={$sessionId}", $data);

        /** @var array<string, mixed> */
        $payload = is_array($data['payload']) ? (array) $data['payload'] : [];
        $questId = PayloadHelper::getQuestId($payload, $data);

        /** @var array<string, mixed> */
        $detail = PayloadHelper::getDetail($payload);

        if ($questId === null || empty($detail)) {
            $this->logger->log("NextTurnHandler: Missing required data (questId, detail).", $data, 'warning');
            $errorDto = $this->messageFactory->createErrorMessage("Invalid next turn data provided.");
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("NextTurnHandler: handle");
            return;
        }

        $nextTurnDto = $this->messageFactory->createNextTurnMessage($detail);

        $this->broadcastService->broadcastToQuest($questId, $nextTurnDto, $sessionId);

        $this->logger->log("NextTurnHandler: NextTurnDto broadcasted", ['quest_id' => $questId, 'payload' => $payload]);
        $this->broadcastService->sendBack($from, 'ack', ['type' => 'next-turn_processed', 'detail' => $detail]);

        $this->logger->logEnd("NextTurnHandler: handle");
    }
}
