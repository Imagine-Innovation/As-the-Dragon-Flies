<?php

namespace common\extensions\EventHandler\handlers;

use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\NotificationService;
use common\extensions\EventHandler\LoggerService;
use Ratchet\ConnectionInterface;

class SendingMessageHandler implements SpecificMessageHandlerInterface
{

    private LoggerService $logger;
    private NotificationService $notificationService;
    private BroadcastServiceInterface $broadcastService; // Corrected type hint
    private BroadcastMessageFactory $messageFactory;

    public function __construct(
            LoggerService $logger,
            NotificationService $notificationService,
            BroadcastServiceInterface $broadcastService, // Corrected type hint
            BroadcastMessageFactory $messageFactory
    ) {
        $this->logger = $logger;
        $this->notificationService = $notificationService;
        $this->broadcastService = $broadcastService;
        $this->messageFactory = $messageFactory;
    }

    /**
     * Handles chat messages by delegating to NotificationService to create and broadcast.
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("SendingMessageHandler: handle sessionId=[{$sessionId}], clientId=[{$clientId}]", $data);

        $messageText = $data['message'] ?? '';
        $playerName = $data['playerName'] ?? 'Unknown';
        $questId = $data['questId'] ?? null;

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
