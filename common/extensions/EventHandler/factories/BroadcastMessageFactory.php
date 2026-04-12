<?php

namespace common\extensions\EventHandler\factories;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;
use common\extensions\EventHandler\dtos\ErrorDto;
use common\extensions\EventHandler\dtos\GameActionDto;
use common\extensions\EventHandler\dtos\GameOverDto;
use common\extensions\EventHandler\dtos\NewMessageDto;
use common\extensions\EventHandler\dtos\NextMissionDto;
use common\extensions\EventHandler\dtos\NextTurnDto;
use common\extensions\EventHandler\dtos\NotificationDto;
use common\extensions\EventHandler\dtos\PlayerJoinedDto;
use common\extensions\EventHandler\dtos\PlayerQuitDto;
use common\extensions\EventHandler\dtos\QuestStartedDto;
use common\extensions\EventHandler\LoggerService;
use Yii;

class BroadcastMessageFactory
{
    const LOG_FILE_PATH = 'c:/temp/BroadcastMessage.log';

    private LoggerService $loggerService;

    /**
     *
     * @param string $message
     * @param string $sender
     * @param string|null $recipient
     * @param array<string, mixed> $extraData
     * @return NewMessageDto
     */
    public function createNewMessage(
        string $message,
        string $sender,
        ?string $recipient = null,
        array $extraData = [],
    ): NewMessageDto {
        return new NewMessageDto(array_merge($extraData, [
            'message' => $message,
            'sender' => $sender,
            'recipient' => $recipient,
        ]));
    }

    /**
     *
     * @param string $playerName
     * @param string $sessionId
     * @param string $questName
     * @param array<string, mixed> $extraData
     * @return PlayerJoinedDto
     */
    public function createPlayerJoinedMessage(
        string $playerName,
        string $sessionId,
        string $questName,
        array $extraData = [],
    ): PlayerJoinedDto {
        return new PlayerJoinedDto(array_merge($extraData, [
            'playerName' => $playerName,
            'sessionId' => $sessionId,
            'questName' => $questName,
        ]));
    }

    /**
     *
     * @param string $playerName
     * @param string $sessionId
     * @param string $questName
     * @param string $reason
     * @param array<string, mixed> $extraData
     * @return PlayerQuitDto
     */
    public function createPlayerQuitMessage(
        string $playerName,
        string $sessionId,
        string $questName,
        string $reason,
        array $extraData = [],
    ): PlayerQuitDto {
        return new PlayerQuitDto(array_merge($extraData, [
            'playerName' => $playerName,
            'sessionId' => $sessionId,
            'questName' => $questName,
            'reason' => $reason,
        ]));
    }

    /**
     *
     * @param string $sessionId
     * @param int $questId
     * @param string $questName
     * @param array<string, mixed> $extraData
     * @return QuestStartedDto
     */
    public function createQuestStartedMessage(
        string $sessionId,
        int $questId,
        string $questName,
        array $extraData = [],
    ): QuestStartedDto {
        return new QuestStartedDto(array_merge($extraData, [
            'sessionId' => $sessionId,
            'questId' => $questId,
            'questName' => $questName,
            'message' => "Quest '{$questName}' has started!",
            'redirectUrl' => '/frontend/web/index.php?r=game/view&id=' . $questId,
            'startedAt' => date('Y-m-d H:i:s', time()),
        ]));
    }

    /**
     *
     * @param string $playerName
     * @param string $action
     * @param array<string, mixed> $detail
     * @param array<string, mixed> $extraData
     * @return GameActionDto
     */
    public function createGameActionMessage(
        string $playerName,
        string $action,
        array $detail,
        array $extraData = [],
    ): GameActionDto {
        return new GameActionDto(array_merge($extraData, [
            'playerName' => $playerName,
            'action' => $action,
            'detail' => $detail,
        ]));
    }

    /**
     *
     * @param array<string, mixed> $detail
     * @param array<string, mixed> $extraData
     * @return NextTurnDto
     */
    public function createNextTurnMessage(array $detail, array $extraData = []): NextTurnDto
    {
        return new NextTurnDto(array_merge($extraData, [
            'detail' => $detail,
        ]));
    }

    /**
     *
     * @param array<string, mixed> $detail
     * @param array<string, mixed> $extraData
     * @return NextMissionDto
     */
    public function createNextMissionMessage(array $detail, array $extraData = []): NextMissionDto
    {
        return new NextMissionDto(array_merge($extraData, [
            'detail' => $detail,
        ]));
    }

    /**
     *
     * @param array<string, mixed> $detail
     * @param array<string, mixed> $extraData
     * @return GameOverDto
     */
    public function createGameOverMessage(array $detail, array $extraData = []): GameOverDto
    {
        return new GameOverDto(array_merge($extraData, [
            'detail' => $detail,
        ]));
    }

    /**
     *
     * @param string $message
     * @param string $level
     * @param array<string, mixed>|null $details
     * @param array<string, mixed> $extraData
     * @return NotificationDto
     */
    public function createNotificationMessage(
        string $message,
        string $level = 'info',
        ?array $details = null,
        array $extraData = [],
    ): NotificationDto {
        $data = array_merge($extraData, [
            'message' => $message,
            'level' => $level,
        ]);
        if ($details !== null) {
            $data['details'] = $details;
        }
        return new NotificationDto($data);
    }

    /**
     *
     * @param string $errorMessage
     * @param int|null $errorCode
     * @param array<string, mixed>|null $details
     * @param array<string, mixed> $extraData
     * @return ErrorDto
     */
    public function createErrorMessage(
        string $errorMessage,
        ?int $errorCode = null,
        ?array $details = null,
        array $extraData = [],
    ): ErrorDto {
        $data = array_merge($extraData, [
            'message' => $errorMessage,
        ]);
        if ($errorCode !== null) {
            $data['code'] = $errorCode;
        }
        if ($details !== null) {
            $data['details'] = $details;
        }
        return new ErrorDto($data);
    }

    /**
     * Validates if all required keys are present in the payload.
     *
     * @param array<string, mixed> $payload
     * @param array<string> $requiredKeys
     * @return bool
     */
    private function validatePayload(array $payload, array $requiredKeys): bool
    {
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
     * @param class-string<BroadcastMessageInterface> $dtoClass
     * @param string $type
     * @param array<string, mixed> $payload
     * @param array<string> $requiredKeys
     * @return BroadcastMessageInterface|null
     */
    private function newDto(
        string $dtoClass,
        string $type,
        array $payload,
        array $requiredKeys,
    ): ?BroadcastMessageInterface {
        if ($this->validatePayload($payload, $requiredKeys)) {
            /** @var BroadcastMessageInterface */
            return new $dtoClass($payload);
        }
        $requiredKeysLabel = implode(', ', $requiredKeys);
        $this->loggerService->log(
            "BroadcastMessageFactory - createMessage - type={$type}, invalid payload, expected payload attributes: {$requiredKeysLabel}",
        );
        return null;
    }

    /**
     *
     * @param string $type
     * @return void
     */
    private function handleUnknownType(string $type): void
    {
        $this->loggerService->log(
            "BroadcastMessageFactory - createMessage - unhandled type=[{$type}]",
            null,
            'warning',
        );
        return;
    }

    /**
     * Generic factory method if type and payload are already known.
     *
     * @param string $type
     * @param array<string, mixed> $payload
     * @return BroadcastMessageInterface|null
     */
    public function createMessage(string $type, array $payload): ?BroadcastMessageInterface
    {
        $this->loggerService = new LoggerService(self::LOG_FILE_PATH, true);
        $this->loggerService->logStart("BroadcastMessageFactory - createMessage - type={$type}, payload=", $payload);

        $dto = match ($type) {
            'new-message' => $this->newDto(NewMessageDto::class, $type, $payload, ['message', 'sender']),
            'player-joined' => $this->newDto(
                PlayerJoinedDto::class,
                $type,
                $payload,
                ['playerName', 'sessionId', 'questName'],
            ),
            'player-quit' => $this->newDto(PlayerQuitDto::class, $type, $payload, [
                'playerName',
                'sessionId',
                'questName',
                'reason',
            ]),
            'quest-started' => $this->newDto(
                QuestStartedDto::class,
                $type,
                $payload,
                ['sessionId', 'questId', 'questName'],
            ),
            'game-action' => $this->newDto(GameActionDto::class, $type, $payload, ['playerName', 'action', 'detail']),
            'next-turn' => $this->newDto(NextTurnDto::class, $type, $payload, ['detail']),
            'next-mission' => $this->newDto(NextMissionDto::class, $type, $payload, ['detail']),
            'game-over' => $this->newDto(GameOverDto::class, $type, $payload, ['detail']),
            'notification' => $this->newDto(NotificationDto::class, $type, $payload, ['message']),
            'error' => $this->newDto(ErrorDto::class, $type, $payload, ['message']),
            default => $this->handleUnknownType($type),
        };

        $this->loggerService->logEnd("BroadcastMessageFactory - createMessage - type={$type} returns", $dto);
        return $dto;
    }
}
