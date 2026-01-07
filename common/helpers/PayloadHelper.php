<?php

namespace common\helpers;

class PayloadHelper
{

    /**
     * Extract questId from payload
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed>|null $defaultData
     * @return int|null
     */
    public static function getQuestId(array $payload, ?array $defaultData = null): ?int {
        if (array_key_exists('questId', $payload) && is_numeric($payload['questId'])) {
            return (int) $payload['questId'];
        } elseif ($defaultData !== null && array_key_exists('questId', $defaultData) && is_numeric($defaultData['questId'])) {
            return (int) $defaultData['questId'];
        } else {
            return null;
        }
    }

    /**
     * Extract playerId from payload
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed>|null $defaultData
     * @return int|null
     */
    public static function getPlayerId(array $payload, ?array $defaultData = null): ?int {
        if (array_key_exists('playerId', $payload) && is_numeric($payload['playerId'])) {
            return (int) $payload['playerId'];
        } elseif ($defaultData !== null && array_key_exists('playerId', $defaultData) && is_numeric($defaultData['playerId'])) {
            return (int) $defaultData['playerId'];
        } else {
            return null;
        }
    }

    /**
     * Extract excludeSessionId from payload
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed>|null $defaultData
     * @return string|null
     */
    public static function getExcludeSessionId(array $payload, ?array $defaultData = null): ?string {
        if (array_key_exists('excludeSessionId', $payload) && is_string($payload['excludeSessionId'])) {
            return (string) $payload['excludeSessionId'];
        } elseif ($defaultData !== null && array_key_exists('excludeSessionId', $defaultData) && is_string($defaultData['excludeSessionId'])) {
            return (string) $defaultData['excludeSessionId'];
        } else {
            return null;
        }
    }

    /**
     * Extract action from payload
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed>|null $defaultData
     * @return string|null
     */
    public static function getAction(array $payload, ?array $defaultData = null): ?string {
        if (array_key_exists('action', $payload) && is_string($payload['action'])) {
            return (string) $payload['action'];
        } elseif ($defaultData !== null && array_key_exists('action', $defaultData) && is_string($defaultData['action'])) {
            return (string) $defaultData['action'];
        } else {
            return null;
        }
    }

    /**
     * Extract playerName from payload
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed>|null $defaultData
     * @return string
     */
    public static function getPlayerName(array $payload, ?array $defaultData = null): string {
        if (array_key_exists('playerName', $payload) && is_string($payload['playerName'])) {
            return (string) $payload['playerName'];
        } elseif ($defaultData !== null && array_key_exists('playerName', $defaultData) && is_string($defaultData['playerName'])) {
            return (string) $defaultData['playerName'];
        } else {
            return 'Unknown';
        }
    }

    /**
     * Extract questName from payload
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed>|null $defaultData
     * @return string
     */
    public static function getQuestName(array $payload, ?array $defaultData = null): string {
        if (array_key_exists('questName', $payload) && is_string($payload['questName'])) {
            return (string) $payload['questName'];
        } elseif ($defaultData !== null && array_key_exists('questName', $defaultData) && is_string($defaultData['questName'])) {
            return (string) $defaultData['questName'];
        } else {
            return 'Unknown';
        }
    }

    /**
     * Extract reason from payload
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed>|null $defaultData
     * @return string
     */
    public static function getReason(array $payload, ?array $defaultData = null): string {
        if (array_key_exists('reason', $payload) && is_string($payload['reason'])) {
            return (string) $payload['reason'];
        } elseif ($defaultData !== null && array_key_exists('reason', $defaultData) && is_string($defaultData['reason'])) {
            return (string) $defaultData['reason'];
        } else {
            return 'Unknown';
        }
    }

    /**
     * Extract message from payload
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed>|null $defaultData
     * @return array<mixed>
     */
    public static function getDetail(array $payload, ?array $defaultData = null): array {
        if (array_key_exists('detail', $payload) && is_array($payload['detail'])) {
            return (array) $payload['detail'];
        } elseif ($defaultData !== null && array_key_exists('detail', $defaultData) && is_array($defaultData['detail'])) {
            return (array) $defaultData['detail'];
        } else {
            return [];
        }
    }

    /**
     * Extract message from payload
     *
     * @param array<string, mixed> $payload
     * @param array<string, mixed>|null $defaultData
     * @return array<mixed>
     */
    public static function getMessage(array $payload, ?array $defaultData = null): array {
        if (array_key_exists('message', $payload) && is_array($payload['message'])) {
            return (array) $payload['message'];
        } elseif ($defaultData !== null && array_key_exists('message', $defaultData) && is_array($defaultData['message'])) {
            return (array) $defaultData['message'];
        } else {
            return [];
        }
    }
}
