<?php

namespace common\helpers;

final class PayloadHelper
{

    /**
     * Extract payload from data
     *
     * @param array<string, mixed> $data
     * @return array<mixed>
     */
    public static function extractPayloadFromData(array $data): array {
        if (array_key_exists('payload', $data) && is_array($data['payload'])) {
            return (array) $data['payload'];
        }
        return [];
    }

    /**
     * Extract an integer value from payload
     *
     * @param string $key
     * @param array<string, mixed> $payload
     * @param array<string, mixed>|null $alternativePayload
     * @return int|null
     */
    public static function extractIntFromPayload(string $key, array $payload, ?array $alternativePayload = null): ?int {
        if (array_key_exists($key, $payload) && is_integer($payload[$key])) {
            return (int) $payload[$key];
        } elseif ($alternativePayload !== null && array_key_exists($key, $alternativePayload) && is_integer($alternativePayload[$key])) {
            return (int) $alternativePayload[$key];
        }
        return null;
    }

    /**
     * Extract a string value from payload
     *
     * @param string $key
     * @param array<string, mixed> $payload
     * @param string $defaultValue
     * @param array<string, mixed>|null $alternativePayload
     * @return string
     */
    public static function extractStringFromPayload(string $key, array $payload, string $defaultValue = 'Unknown', ?array $alternativePayload = null): string {
        if (array_key_exists($key, $payload) && is_string($payload[$key])) {
            return (string) $payload[$key];
        } elseif ($alternativePayload !== null && array_key_exists($key, $alternativePayload) && is_string($alternativePayload[$key])) {
            return (string) $alternativePayload[$key];
        }
        return $defaultValue;
    }

    /**
     * Extract an array from payload
     *
     * @param string $key
     * @param array<string, mixed> $payload
     * @param array<string, mixed>|null $alternativePayload
     * @return array<mixed>
     */
    public static function extractArrayFromPayload(string $key, array $payload, ?array $alternativePayload = null): array {
        if (array_key_exists($key, $payload) && is_array($payload[$key])) {
            return (array) $payload[$key];
        } elseif ($alternativePayload !== null && array_key_exists($key, $alternativePayload) && is_array($alternativePayload[$key])) {
            return (array) $alternativePayload[$key];
        }
        return [];
    }
}
