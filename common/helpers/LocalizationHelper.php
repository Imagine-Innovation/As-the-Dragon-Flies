<?php

namespace common\helpers;

use Yii;

final class LocalizationHelper
{

    /**
     * Sets a default value for missing labels when using i18n localization
     *
     * @param string $type (Player, Quest, Action, Story...)
     * @param string|null $name
     * @param string $language
     * @return string
     */
    public static function defaultName(string $type, ?string $name, string $language = 'en'): string
    {
        $trimmedName = trim($name ?? '');

        if ($trimmedName === '') {
            return Yii::t('app', $type, $language);
        }

        return $trimmedName;
    }
}
