<?php

namespace common\extensions\EventHandler\factories;

use common\extensions\EventHandler\dtos\ChatMessageDto;
use common\extensions\EventHandler\dtos\PlayerJoinedDto;
use common\extensions\EventHandler\dtos\QuestCanStartDto;
use common\extensions\EventHandler\dtos\GameActionDto;
use common\extensions\EventHandler\dtos\NotificationDto;
use common\extensions\EventHandler\dtos\ErrorDto;
use common\extensions\EventHandler\contracts\BroadcastMessageInterface;

class BroadcastMessageFactory {

    public function createChatMessage(string $message, string $sender, ?string $recipient = null): ChatMessageDto {
        return new ChatMessageDto($message, $sender, $recipient);
    }

    public function createPlayerJoinedMessage(string $playerName, string $sessionId, string $questName): PlayerJoinedDto {
        return new PlayerJoinedDto($playerName, $sessionId, $questName);
    }

    public function createQuestCanStartMessage(string $sessionId, string $questName): QuestCanStartDto {
        return new QuestCanStartDto($sessionId, $questName);
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
        switch ($type) {
            case 'chat':
                // Assuming payload keys match ChatMessageDto constructor or a setter method
                // This part needs careful implementation based on how payload is structured
                // For simplicity, let's assume direct payload usage isn't the primary path for this factory
                // and specific creator methods are preferred.
                // If direct creation from payload is needed, each DTO should have a constructor
                // that accepts an array or dedicated setters.
                // Example: return new ChatMessageDto($payload['message'], $payload['sender']);
                // This is a placeholder and might need adjustment based on DTO constructor signatures.
                if (isset($payload['message'], $payload['sender'])) {
                    return new ChatMessageDto($payload['message'], $payload['sender'], $payload['recipient'] ?? null);
                }
                break;
            case 'player_joined':
                if (isset($payload['playerName'], $payload['sessionId'], $payload['questName'])) {
                    return new PlayerJoinedDto($payload['playerName'], $payload['sessionId'], $payload['questName']);
                }
                // Consider adding an else or logging if payload is incomplete for this type
                break;
            case 'game_action':
                if (isset($payload['action'], $payload['details'])) {
                    return new GameActionDto($payload['action'], $payload['details']);
                }
                break;
            case 'notification':
                if (isset($payload['message'])) {
                    return new NotificationDto($payload['message'], $payload['level'] ?? 'info', $payload['details'] ?? null);
                }
                break;
            case 'quest_can_start':
                if (isset($payload['sessionId'], $payload['questName'])) {
                    return new QuestCanStartDto($payload['sessionId'], $payload['questName']);
                }
                break;
            case 'error':
                if (isset($payload['message'])) {
                    return new ErrorDto($payload['message'], $payload['code'] ?? null, $payload['details'] ?? null);
                }
                break;
            // Add other types as needed
        }
        // Log error or throw exception for unknown type or invalid payload
        // error_log("BroadcastMessageFactory: Unknown message type '{$type}' or invalid payload for generic creation.");
        return null;
    }
}
