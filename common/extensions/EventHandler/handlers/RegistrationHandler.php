<?php

namespace common\extensions\EventHandler\handlers;

use common\components\AppStatus;
use common\extensions\EventHandler\BroadcastService;
use common\extensions\EventHandler\contracts\SpecificMessageHandlerInterface;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\LoggerService;
use common\extensions\EventHandler\QuestSessionManager;
use common\helpers\PayloadHelper;
use common\models\QuestPlayer;
use Ratchet\ConnectionInterface;

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
        BroadcastMessageFactory $messageFactory,
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
    private function updateQuestPlayerStatus(?int $questId, ?int $playerId, string $sessionId): void
    {
        $this->logger->logStart(
            "RegistrationHandler: updateQuestPlayerStatus questId={$questId}, playerId={$playerId}, sessionId={$sessionId}",
        );
        if ($questId === null || $playerId === null) {
            return;
        }

        $questPlayer = QuestPlayer::findOne(['quest_id' => $questId, 'player_id' => $playerId]);

        if (!$questPlayer) {
            // The player is not defined in the quest yet. Stop here
            $this->logger->logEnd('RegistrationHandler: updateQuestPlayerStatus');
            return;
        }
        $questPlayer->status = AppStatus::ONLINE->value;
        $successfullySaved = $questPlayer->save();
        if (!$successfullySaved) {
            $this->logger->log(
                'Failed to update QuestPlayer:  Errors: ' . print_r($questPlayer->getErrors(), true),
                null,
                'error',
            );
            $this->logger->logEnd('RegistrationHandler: updateQuestPlayerStatus');
            return;
        }

        $NotificationDto = $this->messageFactory->createNotificationMessage('Player registered', 'info', []);
        $this->broadcastService->broadcastToQuest($questId, $NotificationDto, $sessionId);
        $this->logger->logEnd('RegistrationHandler: updateQuestPlayerStatus');
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
    public function handle(ConnectionInterface $from, string $clientId, string $sessionId, array $data): void
    {
        $this->logger->logStart("RegistrationHandler: handle clientId=[{$clientId}], sessionId=[{$sessionId}]", $data);

        $questId = PayloadHelper::extractIntFromPayload('questId', $data);
        $playerId = PayloadHelper::extractIntFromPayload('playerId', $data);

        $registered = $this->questSessionManager->registerSession($sessionId, $playerId, $questId, $clientId, $data);
        $this->logger->log(
            'RegistrationHandler: Session registration via QuestSessionManager. Result: '
            . ($registered ? 'Success' : 'Failure'),
        );

        if ($registered) {
            $this->updateQuestPlayerStatus($questId, $playerId, $sessionId);
            $this->broadcastService->sendBack(
                $from,
                'connected',
                "Client ID [{$clientId}] attached to session [{$sessionId}]",
            );
        } else {
            $this->logger->log(
                "RegistrationHandler: Unable to register/find session Id [{$sessionId}] for clientId=[{$clientId}]",
                $data,
                'warning',
            );
            $this->broadcastService->sendBack($from, 'error', "Unable to find session Id [{$sessionId}]");
        }

        $this->logger->logEnd("RegistrationHandler: handle clientId=[{$clientId}]");
    }
}
