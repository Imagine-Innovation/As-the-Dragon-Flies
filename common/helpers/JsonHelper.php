<?php

namespace common\helpers;

final class JsonHelper
{

    /**
     *
     * @param string|null $jsonMessage
     * @return array<string, mixed>
     */
    public static function decode(?string $jsonMessage = null): array {

        $array = $jsonMessage ? json_decode($jsonMessage, true) : null;

        return is_array($array) ? (array) $array : [];
    }
}
