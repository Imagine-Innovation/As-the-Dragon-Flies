<?php

namespace common\helpers;

final class JsonHelper
{
    /**
     * Decodes a JSON payload, tolerating every form it can take between the
     * database and the caller: raw JSON string, already decoded array (JSON
     * column phpTypecast), legacy row double-encoded by the JSON column
     * typecast (a JSON string wrapping the actual JSON object), or null.
     *
     * @param mixed $jsonMessage payload to decode
     * @return array<string, mixed>
     */
    public static function decode(mixed $jsonMessage = null): array
    {
        if (is_array($jsonMessage)) {
            return $jsonMessage;
        }

        $array = is_string($jsonMessage) && $jsonMessage !== '' ? json_decode($jsonMessage, true) : null;

        if (is_string($array)) {
            return self::decode($array);
        }

        return is_array($array) ? (array) $array : [];
    }
}
