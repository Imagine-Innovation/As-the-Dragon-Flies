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

    const LOG_FILE_PATH = 'c:/temp/BroadcastMessage.log';

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

    public function createGameActionMessage(string $playerName, string $action, array $detail): GameActionDto {
        return new GameActionDto($playerName, $action, $detail);
    }

    public function createNextTurnMessage(string $playerName, string $sessionId, string $questName): NextTurnDto {
        return new NextTurnDto($playerName, $sessionId, $questName);
    }

    public function createNextMissionMessage(int $playerId, string $playerName, string $sessionId, string $questName, int $missionId, string $missionName): NextMissionDto {
        return new NextMissionDto($playerId, $playerName, $sessionId, $questName, $missionId, $missionName);
    }

    public function createGameOverMessage(string $status, string $playerName, string $sessionId, string $questName): GameOverDto {
        return new GameOverDto($status, $playerName, $sessionId, $questName);
    }

    public function createNotificationMessage(string $message, string $level = 'info', ?array $details = null): NotificationDto {
        return new NotificationDto($message, $level, $details);
    }

    public function createErrorMessage(string $errorMessage, ?int $errorCode = null, ?array $details = null): ErrorDto {
        return new ErrorDto($errorMessage, $errorCode, $details);
    }

    private function newMessage(array $payload): ?BroadcastMessageInterface {
        if (isset($payload['message'], $payload['sender'])) {
            return new NewMessageDto($payload['message'], $payload['sender'], $payload['recipient'] ?? null);
        }
        $this->loggerService->log("BroadcastMessageFactory - createMessage - type=new-message, invalid payload, expected payload attributes: message, sender");
        return null;
    }

    private function playerJoined(array $payload): ?BroadcastMessageInterface {
        if (isset($payload['playerName'], $payload['sessionId'], $payload['questName'])) {
            return new PlayerJoinedDto($payload['playerName'], $payload['sessionId'], $payload['questName']);
        }
        $this->loggerService->log("BroadcastMessageFactory - createMessage - type=player-joined, invalid payload, expected payload attributes: playerName, sessionId, questName");
        return null;
    }

    private function playerQuit(array $payload): ?BroadcastMessageInterface {
        if (isset($payload['playerName'], $payload['sessionId'], $payload['questName'])) {
            return new PlayerQuitDto($payload['playerName'], $payload['sessionId'], $payload['questName'], $payload['reason']);
        }
        $this->loggerService->log("BroadcastMessageFactory - createMessage - type=player-quit, invalid payload, expected payload attributes: playerName, sessionId, questName, reason");
        return null;
    }

    private function questStarted(array $payload): ?BroadcastMessageInterface {
        if (isset($payload['sessionId'], $payload['questName'], $payload['questId'])) {
            return new QuestStartedDto($payload['sessionId'], $payload['questId'], $payload['questName']);
        }
        $this->loggerService->log("BroadcastMessageFactory - createMessage - type=quest-started, invalid payload, expected payload attributes: sessionId, questName, questId");
        return null;
    }

    private function gameAction(array $payload): ?BroadcastMessageInterface {
        if (isset($payload['action'], $payload['detail'])) {
            return new GameActionDto($payload['playerName'], $payload['action'], $payload['detail']);
        }
        $this->loggerService->log("BroadcastMessageFactory - createMessage - type=game-action, invalid payload, expected payload attributes: playerName, action, detail");
        return null;
    }

    private function notification(array $payload): ?BroadcastMessageInterface {
        if (isset($payload['message'])) {
            return new NotificationDto($payload['message'], $payload['level'] ?? 'info', $payload['details'] ?? null);
        }
        $this->loggerService->log("BroadcastMessageFactory - createMessage - type=notification, invalid payload, expected payload attributes: message, level, details");
        return null;
    }

    private function error(array $payload): ?BroadcastMessageInterface {
        if (isset($payload['message'])) {
            return new ErrorDto($payload['message'], $payload['code'] ?? null, $payload['details'] ?? null);
        }
        $this->loggerService->log("BroadcastMessageFactory - createMessage - type=error, invalid payload, expected payload attributes: message");
        return null;
    }

    private function default(strinf $type): ?BroadcastMessageInterface {
        $this->loggerService->log("BroadcastMessageFactory - createMessage - unhandled type=[{$type}]", null, 'warning');
        return null;
    }

    /**
     * Generic factory method if type and payload are already known.
     * This can be useful for reconstructing messages or for dynamic creation.
     */
    public function createMessage(string $type, array $payload): ?BroadcastMessageInterface {
        $this->loggerService = new LoggerService(self::LOG_FILE_PATH, true);
        $this->loggerService->logStart("BroadcastMessageFactory - createMessage - type={$type}, payload=", $payload);

        $dto = match ($type) {
            'new-message' => $this->newMessage($payload),
            'player-joined' => $this->playerJoined($payload),
            'player-quit' => $this->playerQuit($payload),
            'quest-started' => $this->questStarted($payload),
            'game-action' => $this->gameAction($payload),
            'notification' => $this->notification($payload),
            'error' => $this->error($payload),
            default => $this->default($type),
        }
        ;
        $this->loggerService->logEnd("BroadcastMessageFactory - createMessage - type={$type} returns", $dto);

        return $dto;
    }

    public function xxxcreateMessage(string $type, array $payload): ?BroadcastMessageInterface {
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
                if (isset($payload['action'], $payload['detail'])) {
                    $dto = new GameActionDto($payload['playerName'], $payload['action'], $payload['detail']);
                } else {
                    $this->loggerService->log("BroadcastMessageFactory - createMessage - type={$type}, invalid payload, expected payload attributes: playerName, action, detail");
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
