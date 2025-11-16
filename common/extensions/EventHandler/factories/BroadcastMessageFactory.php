<?php

namespace common\extensions\EventHandler\factories;

use common\extensions\EventHandler\dtos\NewMessageDto;
use common\extensions\EventHandler\dtos\PlayerJoinedDto;
use common\extensions\EventHandler\dtos\PlayerQuitDto;
use common\extensions\EventHandler\dtos\QuestStartedDto;
use common\extensions\EventHandler\dtos\GameActionDto;
use common\extensions\EventHandler\dtos\NextTurnDto;
use common\extensions\EventHandler\dtos\NextMissionDto;
use common\extensions\EventHandler\dtos\GameOverDto;
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

    public function createNextTurnMessage(array $detail): NextTurnDto {
        return new NextTurnDto($detail);
    }

    public function createNextMissionMessage(array $detail): NextMissionDto {
        return new NextMissionDto($detail);
    }

    public function createGameOverMessage(array $detail): GameOverDto {
        return new GameOverDto($detail);
    }

    public function createNotificationMessage(string $message, string $level = 'info', ?array $details = null): NotificationDto {
        return new NotificationDto($message, $level, $details);
    }

    public function createErrorMessage(string $errorMessage, ?int $errorCode = null, ?array $details = null): ErrorDto {
        return new ErrorDto($errorMessage, $errorCode, $details);
    }

    /**
     * Validates if all required keys are present in the payload.
     *
     * @param array $payload
     * @param array $requiredKeys
     * @return bool
     */
    private function validatePayload(array $payload, array $requiredKeys): bool {
        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $payload)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Creates the appropriate DTO if validation passes, otherwise logs an error.
     *
     * @param string $dtoClass
     * @param string $type
     * @param array $payload
     * @param array $requiredKeys
     * @param array $optionalKeys
     * @return BroadcastMessageInterface|null
     */
    private function newDto(string $dtoClass, string $type, array $payload, array $requiredKeys, array $optionalKeys = []): ?BroadcastMessageInterface {
        if ($this->validatePayload($payload, $requiredKeys)) {
            $args = [];
            foreach ($requiredKeys as $key) {
                $args[] = $payload[$key];
            }
            foreach ($optionalKeys as $key) {
                $args[] = $payload[$key] ?? null;
            }
            return new $dtoClass(...$args);
        }
        $requiredKeysLabel = implode(', ', $requiredKeys);
        $this->loggerService->log("BroadcastMessageFactory - createMessage - type={$type}, invalid payload, expected payload attributes: {$requiredKeysLabel}");
        return null;
    }

    private function handleUnknownType(string $type): ?BroadcastMessageInterface {
        $this->loggerService->log("BroadcastMessageFactory - createMessage - unhandled type=[{$type}]", null, 'warning');
        return null;
    }

    /**
     * Generic factory method if type and payload are already known.
     *
     * @param string $type
     * @param array $payload
     * @return BroadcastMessageInterface|null
     */
    public function createMessage(string $type, array $payload): ?BroadcastMessageInterface {
        $this->loggerService = new LoggerService(self::LOG_FILE_PATH, true);
        $this->loggerService->logStart("BroadcastMessageFactory - createMessage - type={$type}, payload=", $payload);

        $dto = match ($type) {
            'new-message' => $this->newDto(NewMessageDto::class, $type, $payload, ['message', 'sender'], ['recipient']),
            'player-joined' => $this->newDto(PlayerJoinedDto::class, $type, $payload, ['playerName', 'sessionId', 'questName']),
            'player-quit' => $this->newDto(PlayerQuitDto::class, $type, $payload, ['playerName', 'sessionId', 'questName', 'reason']),
            'quest-started' => $this->newDto(QuestStartedDto::class, $type, $payload, ['sessionId', 'questId', 'questName']),
            'game-action' => $this->newDto(GameActionDto::class, $type, $payload, ['playerName', 'action', 'detail']),
            'next-turn' => $this->newDto(NextTurnDto::class, $type, $payload, ['detail']),
            'next-mission' => $this->newDto(NextMissionDto::class, $type, $payload, ['detail']),
            'game-over' => $this->newDto(GameOverDto::class, $type, $payload, ['detail']),
            'notification' => $this->newDto(NotificationDto::class, $type, $payload, ['message'], ['level', 'details']),
            'error' => $this->newDto(ErrorDto::class, $type, $payload, ['message'], ['code', 'details']),
            default => $this->handleUnknownType($type),
        };

        $this->loggerService->logEnd("BroadcastMessageFactory - createMessage - type={$type} returns", $dto);
        return $dto;
    }
}
