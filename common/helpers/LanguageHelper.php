<?php

namespace common\helpers;

use Yii;

final class LanguageHelper
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

    /**
     *
     * @param array<string> $inputStringList
     * @param string $language
     * @return string
     */
    public static function formatList(array $inputStringList, string $language = 'en'): string
    {
        $trimmedList = array_map('trim', $inputStringList);
        // Remove the empty elements
        $strippedList = array_filter($trimmedList, fn($v) => $v !== '');

        $count = count($strippedList);

        switch ($count) {
            case 0 : return '';
            case 1 : return $strippedList[0];
            case 2 : {
                    $and = Yii::t('app', 'and', $language);
                    return "{$strippedList[0]}{$and}{$strippedList[1]}";
                }
            default: {// 3+ items
                    $lastElement = array_pop($strippedList);
                    $and = Yii::t('app', 'and', $language);
                    return implode(', ', $strippedList) . $and . $lastElement;
                }
        }
    }
}
