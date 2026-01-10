<?php

namespace common\helpers;

final class MixedHelper
{

    /**
     * Convert mixed to int
     *
     * @param mixed $mixedValue
     * @return int
     */
    public static function toInt(mixed $mixedValue): int {
        return is_integer($mixedValue) ? (int) $mixedValue : 0;
    }

    /**
     * Convert mixed to string
     *
     * @param mixed $mixedValue
     * @param string|null $defaultValue
     * @return string|null
     */
    public static function toString(mixed $mixedValue, ?string $defaultValue = null): ?string {
        return is_string($mixedValue) ? (string) $mixedValue : $defaultValue;
    }

    /**
     * Extract an array from payload
     *
     * @param mixed $mixedValue
     * @return array<mixed>
     */
    public static function toArray(mixed $mixedValue): array {
        return is_array($mixedValue) ? (array) $mixedValue : [];
    }
}
