<?php

namespace common\extensions\EventHandler\handlers;

use common\components\AppStatus;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\BroadcastService;
use common\extensions\EventHandler\LoggerService;
use common\extensions\EventHandler\QuestSessionManager;
use common\models\QuestPlayer;
use Ratchet\ConnectionInterface;

// use common\extensions\EventHandler\BroadcastService; // Placeholder

class RegistrationHandler implements SpecificMessageHandlerInterface
{

    private LoggerService $logger;
    private QuestSessionManager $questSessionManager;
    private BroadcastService $broadcastService;
    private BroadcastMessageFactory $messageFactory;

    /**
     *
     * @param LoggerService $logger
     * @param QuestSessionManager $questSessionManager
     * @param BroadcastService $broadcastService
     * @param BroadcastMessageFactory $messageFactory
     */
    public function __construct(
            LoggerService $logger,
            QuestSessionManager $questSessionManager,
            BroadcastService $broadcastService,
            BroadcastMessageFactory $messageFactory
    ) {
        $this->logger = $logger;
        $this->questSessionManager = $questSessionManager;
        $this->broadcastService = $broadcastService;
        $this->messageFactory = $messageFactory;
    }

    /**
     *
     * @param int|null $questId
     * @param int|null $playerId
     * @param string $sessionId
     * @return void
     */
    private function updateQuestPlayerStatus(?int $questId, ?int $playerId, string $sessionId): void {
        if ($questId === null || $playerId === null) {
            return;
        }

        $questPlayer = QuestPlayer::findOne(['quest_id' => $questId, 'player_id' => $playerId]);

        if (!$questPlayer) {
            // The player is not defined int the quest yet. Stop here
            return;
        }
        $questPlayer->status = AppStatus::ONLINE->value;
        $questPlayer->save();

        $NotificationDto = $this->messageFactory->createNotificationMessage("Player registered", 'info', []);
        $this->broadcastService->broadcastToQuest($questId, $NotificationDto, $sessionId);
    }

    /**
     * Handles attachment messages.
     *
     * @param ConnectionInterface $from
     * @param string $clientId
     * @param string $sessionId
     * @param array<string, mixed> $data
     * @return void
     */
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void {
        $this->logger->logStart("RegistrationHandler: handle clientId=[{$clientId}], sessionId=[{$sessionId}]", $data);

        $playerId = $data['playerId'] ?? null;
        $questId = $data['questId'] ?? null;
        $registered = $this->questSessionManager->registerSession($sessionId, $playerId, $questId, $clientId, $data);
        $this->logger->log("RegistrationHandler: Session registration via QuestSessionManager. Result: " . ($registered ? 'Success' : 'Failure'));

        if ($registered) {
            $this->updateQuestPlayerStatus($questId, $playerId, $sessionId);
            $this->broadcastService->sendBack($from, 'connected', "Client ID [{$clientId}] attached to session [{$sessionId}]");
        } else {
            $this->logger->log("RegistrationHandler: Unable to register/find session Id [{$sessionId}] for clientId=[{$clientId}]", $data, 'warning');
            $this->broadcastService->sendBack($from, 'error', "Unable to find session Id [{$sessionId}]");
        }

        $this->logger->logEnd("RegistrationHandler: handle clientId=[{$clientId}]");
    }
}
