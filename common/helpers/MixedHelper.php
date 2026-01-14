<?php

namespace common\helpers;

final class MixedHelper
{

    /**
     * Convert mixed to int, default value 0
     *
     * @param mixed $mixedValue
     * @return int
     */
    public static function toInt(mixed $mixedValue): int {
        return is_integer($mixedValue) ? (int) $mixedValue : 0;
    }

    /**
     * Convert mixed to bool, default value false
     *
     * @param mixed $mixedValue
     * @return bool
     */
    public static function toBool(mixed $mixedValue): bool {
        if (is_bool($mixedValue)) {
            return (bool) $mixedValue;
        } elseif (is_integer($mixedValue)) {
            return ((int) $mixedValue > 0);
        } else {
            return false;
        }
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
