<?php

namespace common\helpers;

use Yii;

final class MergeHelper
{

    /**
     * List of possible/typical placeholders supported by the system.
     * Placeholders are strings enclosed in curly braces
     */
    private const VALID_PLACEHOLDERS = [
        'playerName',
        'otherPlayerName',
        'questName',
        'actionName',
        'turnOwner',
        'placeName',
        'npcName',
        'monsterName',
    ];

    /**
     * Performs dynamic placeholder replacement.
     *
     * @param string|null $content
     * @param array<string, mixed> $placeholderValueArray
     * @return string
     */
    public static function merge(?string $content, array $placeholderValueArray): string
    {
        $input = trim($content ?? '');

        if ($input === '') {
            return '';
        }

        // Step 1: find every {placeholder} in the input content
        $matches = [];
        preg_match_all('/\{([a-zA-Z0-9_\-]+)\}/', $input, $matches);

        $placeholderTags = $matches[0]; // e.g. ['{playerName}', '{date}']
        $placeholderKeys = $matches[1]; // e.g. ['playerName', 'date']
        // Step 2: build a list of [search => replace] pairs
        $search = [];
        $replace = [];

        foreach ($placeholderKeys as $searchString => $placeholder) {
            $search[] = $placeholderTags[$searchString];
            $replace[] = self::getPlaceholderAllowedValue($placeholder, $placeholderValueArray);
        }

        // Step 3: replace them all in one pass
        return str_replace($search, $replace, $input);
    }

    /**
     * Even if placeholder value is of a mixed type, we restrict the allowed types to int and string
     *
     * @param string $placeholder
     * @param array<string, mixed> $placeholderValueArray
     * @return string
     */
    private static function getPlaceholderAllowedValue(string $placeholder, array $placeholderValueArray): string
    {
        if (!self::isValidPlaceholder($placeholder, $placeholderValueArray)) {
            return '{' . $placeholder . '}';
        }

        $value = $placeholderValueArray[$placeholder];

        if ($value === null) {
            return '';
        }

        if (is_string($value)) {
            return self::sanitize($value);
        }

        if (is_int($value)) {
            return (string) $value;
        }

        // Any other type returns a empty string
        return '';
    }

    /**
     * Check whether the reserved space is valid and whether a replacement value has been provided
     *
     * @param string $placeholder
     * @param array<string, mixed> $placeholderValueArray.
     * @return bool
     */
    private static function isValidPlaceholder(string $placeholder, array $placeholderValueArray): bool
    {
        return in_array($placeholder, self::VALID_PLACEHOLDERS, true) && array_key_exists($placeholder, $placeholderValueArray);
    }

    /**
     *
     * @param string $value
     * @return string
     */
    private static function sanitize(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
