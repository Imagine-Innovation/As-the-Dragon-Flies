<?php

namespace common\extensions\EventHandler\factories;

use common\extensions\EventHandler\dtos\NewMessageDto;
use common\extensions\EventHandler\dtos\PlayerJoinedDto;
use common\extensions\EventHandler\dtos\PlayerQuitDto;
use common\extensions\EventHandler\dtos\QuestStartedDto;
use common\extensions\EventHandler\dtos\GameActionDto;
use common\extensions\EventHandler\dtos\NotificationDto;
use common\extensions\EventHandler\dtos\ErrorDto;
use common\extensions\EventHandler\contracts\BroadcastMessageInterface;
use common\extensions\EventHandler\LoggerService;
use Yii;

class BroadcastMessageFactory
{

    private ?LoggerService $loggerService = null;

    public function createNewMessage(string $message, string $sender, ?string $recipient = null): NewMessageDto {
        return new NewMessageDto($message, $sender, $recipient);
    }

    public function createPlayerJoinedMessage(string $playerName, string $sessionId, string $questName): PlayerJoinedDto {
        return new PlayerJoinedDto($playerName, $sessionId, $questName);
    }

    public function createPlayerQuitMessage(string $playerName, string $sessionId, string $questName, string $reason): PlayerQuitDto {
        return new PlayerQuitDto($playerName, $sessionId, $questName, $reason);
    }

    public function createQuestStartedMessage(string $sessionId, int $questId, string $questName): QuestStartedDto {
        return new QuestStartedDto($sessionId, $questId, $questName);
    }

    public function createGameActionMessage(string $action, array $details): GameActionDto {
        return new GameActionDto($action, $details);
    }

    public function createNotificationMessage(string $message, string $level = 'info', ?array $details = null): NotificationDto {
        return new NotificationDto($message, $level, $details);
    }

    public function createErrorMessage(string $errorMessage, ?int $errorCode = null, ?array $details = null): ErrorDto {
        return new ErrorDto($errorMessage, $errorCode, $details);
    }

    /**
     * Generic factory method if type and payload are already known.
     * This can be useful for reconstructing messages or for dynamic creation.
     */
    public function createMessage(string $type, array $payload): ?BroadcastMessageInterface {
        $actualLogFilePath = 'c:/temp/BroadcastMessage.log';
        $this->loggerService = new LoggerService($actualLogFilePath, true);
        $this->loggerService->logStart("BroadcastMessageFactory - createMessage - type={$type}, payload=", $payload);
        $dto = null;
        switch ($type) {
            case 'new-message':
                if (isset($payload['message'], $payload['sender'])) {
                    $dto = new NewMessageDto($payload['message'], $payload['sender'], $payload['recipient'] ?? null);
                } else {
                    $this->loggerService->log("BroadcastMessageFactory - createMessage - type={$type}, invalid payload, expected payload attributes: message, sender");
                }
                break;
            case 'player-joined':
                if (isset($payload['playerName'], $payload['sessionId'], $payload['questName'])) {
                    $dto = new PlayerJoinedDto($payload['playerName'], $payload['sessionId'], $payload['questName']);
                } else {
                    $this->loggerService->log("BroadcastMessageFactory - createMessage - type={$type}, invalid payload, expected payload attributes: playerName, sessionId, questName");
                }
                break;
            case 'player-quit':
                if (isset($payload['playerName'], $payload['sessionId'], $payload['questName'])) {
                    $dto = new PlayerQuitDto($payload['playerName'], $payload['sessionId'], $payload['questName'], $payload['reason']);
                } else {
                    $this->loggerService->log("BroadcastMessageFactory - createMessage - type={$type}, invalid payload, expected payload attributes: playerName, sessionId, questName, reason");
                }
                break;
            case 'quest-started':
                if (isset($payload['sessionId'], $payload['questName'], $payload['questId'])) {
                    $dto = new QuestStartedDto($payload['sessionId'], $payload['questId'], $payload['questName']);
                } else {
                    $this->loggerService->log("BroadcastMessageFactory - createMessage - type={$type}, invalid payload, expected payload attributes: sessionId, questName, questId");
                }
                break;
            case 'game-action':
                if (isset($payload['action'], $payload['details'])) {
                    $dto = new GameActionDto($payload['action'], $payload['details']);
                } else {
                    $this->loggerService->log("BroadcastMessageFactory - createMessage - type={$type}, invalid payload, expected payload attributes: action, details");
                }
                break;
            case 'notification':
                if (isset($payload['message'])) {
                    $dto = new NotificationDto($payload['message'], $payload['level'] ?? 'info', $payload['details'] ?? null);
                } else {
                    $this->loggerService->log("BroadcastMessageFactory - createMessage - type={$type}, invalid payload, expected payload attributes: message, level, details");
                }
                break;
            case 'error':
                if (isset($payload['message'])) {
                    $dto = new ErrorDto($payload['message'], $payload['code'] ?? null, $payload['details'] ?? null);
                } else {
                    $this->loggerService->log("BroadcastMessageFactory - createMessage - type={$type}, invalid payload, expected payload attributes: message");
                }
                break;
            default:
                $this->loggerService->log("BroadcastMessageFactory - createMessage - unhandled type=[{$type}]", null, 'warning');
                break;
        }
        $this->loggerService->logEnd("BroadcastMessageFactory - createMessage - type={$type} returns", $dto);

        return $dto;
    }
}
