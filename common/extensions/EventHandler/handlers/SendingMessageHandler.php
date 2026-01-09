<?php

namespace common\extensions\EventHandler\handlers;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use common\helpers\PayloadHelper;
use Ratchet\ConnectionInterface;

class SendingMessageHandler implements SpecificMessageHandlerInterface
{

    private LoggerService $logger;
    private BroadcastServiceInterface $broadcastService; // Corrected type hint
    private BroadcastMessageFactory $messageFactory;

    /**
     *
     * @param LoggerService $logger
     * @param BroadcastServiceInterface $broadcastService
     * @param BroadcastMessageFactory $messageFactory
     */
    public function __construct(
            LoggerService $logger,
            BroadcastServiceInterface $broadcastService, // Corrected type hint
            BroadcastMessageFactory $messageFactory
    ) {
        $this->logger = $logger;
        $this->broadcastService = $broadcastService;
        $this->messageFactory = $messageFactory;
    }

    /**
     * Handles chat messages by delegating to create and broadcast.
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $sessionId
     * @param array<string, mixed> $data
     * @return void
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("SendingMessageHandler: handle sessionId=[{$sessionId}], clientId=[{$clientId}]", $data);

        $questId = PayloadHelper::extractIntFromPayload('questId', $data);
        $playerName = PayloadHelper::extractStringFromPayload('playerName', $data);
        $messages = PayloadHelper::extractArrayFromPayload('message', $data);
        $messageText = implode(PHP_EOL, $messages);

        if (empty($messageText) || $questId === null) {
            $this->logger->log("SendingMessageHandler: Missing message or questId.", $data, 'warning');
            $errorDto = $this->messageFactory->createErrorMessage('Invalid chat message data: message or quest ID missing.');
            $this->broadcastService->sendToClient($clientId, $errorDto, false, $sessionId);
            $this->logger->logEnd("SendingMessageHandler: handle sessionId=[{$sessionId}]");
            return;
        }

        $newMessageDto = $this->messageFactory->createNewMessage($messageText, $playerName);
        $this->broadcastService->broadcastToQuest($questId, $newMessageDto, $sessionId);

        $this->broadcastService->sendBack($from, 'ack', ['type' => 'sending-message-processed', 'message' => $messageText]);

        $this->logger->logEnd("SendingMessageHandler: handle sessionId=[{$sessionId}]");
    }
}
