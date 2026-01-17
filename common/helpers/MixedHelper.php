<?php

namespace common\helpers;

use Yii;

final class MixedHelper
{

    /**
     * Convert mixed to int, default value 0
     *
     * @param mixed $mixedValue
     * @return int
     */
    public static function toInt(mixed $mixedValue): int {
        /** @phpstan-ignore-next-line */
        Yii::debug("*** debug *** MixedHelper::toInt mixedValue={$mixedValue}, type=" . gettype($mixedValue));
        return is_numeric($mixedValue) ? (int) $mixedValue : 0;
    }

    /**
     * Convert mixed to bool, default value false
     *
     * @param mixed $mixedValue
     * @return bool
     */
    public static function toBool(mixed $mixedValue): bool {
        /** @phpstan-ignore-next-line */
        Yii::debug("*** debug *** MixedHelper::toBool mixedValue={$mixedValue}, type=" . gettype($mixedValue));
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
        /** @phpstan-ignore-next-line */
        Yii::debug("*** debug *** MixedHelper::toString mixedValue={$mixedValue}, type=" . gettype($mixedValue));
        return is_string($mixedValue) ? (string) $mixedValue : $defaultValue;
    }

    /**
     * Extract an array from payload
     *
     * @param mixed $mixedValue
     * @return array<mixed>
     */
    public static function toArray(mixed $mixedValue): array {
        /** @phpstan-ignore-next-line */
        Yii::debug("*** debug *** MixedHelper::toArray mixedValue={$mixedValue}, type=" . gettype($mixedValue));
        return is_array($mixedValue) ? (array) $mixedValue : [];
    }
}
