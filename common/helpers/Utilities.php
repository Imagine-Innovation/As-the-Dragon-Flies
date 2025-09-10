<?php

/**
 * Utility Helper Class
 *
 * This class provides helper methods for common formatting and image handling operations
 * used throughout the application.
 *
 * @package common\helpers
 * @author François Gros
 * @version 1.0
 * @since 2024-11-01
 */

namespace common\helpers;

use Yii;
use yii\helpers\Html;
use yii\helpers\Inflector;

class Utilities extends Html
{

    /**
     * Trims the input string to a specified maximum length, adding ellipsis
     * if necessary.
     *
     * This function trims the input string to the specified maximum length.
     * If the string is longer than the maximum length, it is truncated and
     * ellipsis are added. The function tries to break at a space to avoid
     * cutting words in half.
     *
     * @param string $inputString The string to be trimmed.
     * @param int $maxLength The maximum length of the trimmed string,
     *                       default is 250 characters.
     * @return string The trimmed string with ellipsis if it was truncated.
     */
    public static function trim($inputString, $maxLength = 250) {
        // If the input string is empty, return an empty string
        if (!$inputString) {
            return "";
        }

        // If the length of the input string is less than or equal to the
        // maximum length, return the string as is
        if (mb_strlen($inputString) <= $maxLength) {
            return $inputString;
        }

        // Find the position of the first space in the input string
        $firstSpace = mb_strpos($inputString, " ");
        if ($firstSpace > $maxLength) {
            // If the first space is beyond the maximum length, cut the string
            // from the first space and add ellipsis
            return mb_substr($inputString, 0, $firstSpace - 1) . '...';
        }

        // Trim the string to the maximum length
        $trimmed = mb_substr($inputString, 0, $maxLength);

        // Find the position of the last space within the trimmed portion
        $lastSpace = mb_strrpos($trimmed, ' ');

        // If a space is found, trim to that position, otherwise use the
        // original trimmed string
        $trim = $lastSpace !== false ? mb_substr($trimmed, 0, $lastSpace) : $trimmed;

        // Add ellipsis to indicate the string was truncated
        return $trim . '...';
    }

    /**
     * Generates a new UUID (version 4).
     *
     * This function generates a new Universally Unique Identifier (UUID) version 4,
     * which is randomly generated.
     * UUID version 4 is based on random numbers and has a specific format and structure.
     *
     * The structure of a UUID version 4 is as follows:
     * xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
     * - 8 hex digits for "time_low"
     * - 4 hex digits for "time_mid"
     * - 4 hex digits for "time_hi_and_version" (with the most significant bits
     *   set to 0100 for version 4)
     * - 4 hex digits for "clk_seq_hi_res" and "clk_seq_low" (with the most
     *   significant bits set to 10 for variant DCE1.1)
     * - 12 hex digits for "node"
     *
     * @return string The generated UUID.
     */
    public static function newUUID() {
        return sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                // 16 bits for "time_mid"
                mt_rand(0, 0xffff),
                // 16 bits for "time_hi_and_version"
                // (the most significant bits set to 0100 for version 4)
                mt_rand(0, 0x0fff) | 0x4000,
                // 16 bits for "clk_seq_hi_res" and "clk_seq_low"
                // (the most significant bits set to 10 for variant DCE1.1)
                mt_rand(0, 0x3fff) | 0x8000,
                // 48 bits for "node"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Formats a datetime value according to the user's browser language
     *
     * Detects browser language preference between English and French,
     * then formats the date accordingly using Yii's formatter.
     *
     * @param mixed $dateTime The datetime value to format
     * @return string Formatted datetime string in 'short' format
     */
    public static function formatDate($dateTime) {
        // Get browser language preference (supports en-US or fr-FR)
        $browserLanguage = Yii::$app->request->getPreferredLanguage(['en-US', 'fr-FR']);

        // Configure formatter locale based on browser language
        Yii::$app->formatter->locale = $browserLanguage;

        return Yii::$app->formatter->asDateTime($dateTime, 'short');
    }

    /**
     *
     * @param srting[] $paragraphs
     * @return type
     */
    public static function formatMultiLine($paragraphs) {
        $lines = array_map(fn($p) => "<p class='text-muted'>" . Html::encode($p) . "</p>", $paragraphs);
        return implode("\n", $lines);
    }

    /**
     * Generates the appropriate image path based on context and quest state
     *
     * If in context mode and quest exists, returns story-specific image path.
     * Otherwise returns a path to either a specified image or random placeholder.
     *
     * @param string|null $imageFile Optional specific image filename
     * @param bool $isContext Whether to consider quest context
     * @return string Path to the image file
     */
    public static function toolImage($imageFile, $isContext) {
        $questId = Yii::$app->session->get('questId');

        // Handle context-specific story images
        if ($isContext && $questId) {
            $quest = Yii::$app->session->get('currentQuest');
            // Return story-specific image if available
            if ($quest->image) {
                return "img/story/" . $quest->story_id . "/" . $image;
            }
        }

        // Generate path for regular or random placeholder image
        $randomFileName = random_int(1, 8) . '.jpg';
        return 'img/sm/' . ($imageFile ? $imageFile : $randomFileName);
    }

    /**
     * Extracts and returns the class name of the provided object in lowercase.
     *
     * This function takes a model or controller instance, extracts its class name,
     * and returns it in lowercase.
     * If the provided parameter is not an object, it returns null.
     *
     * @param object $object The object from which to extract the class name.
     * @return string|null The lowercase class name of the object, or null
     *                     if the parameter is not an object.
     */
    public static function modelName($object) {
        // Check if the provided model is an instance of a class
        if (is_object($object)) {
            // Extract the full class name of the model including its namespace
            $path = explode("\\", get_class($object));
            // Move the internal pointer to the end of the array
            end($path);
            // Return the class name in lowercase
            return Inflector::camel2id(current($path));
            //return mb_strtolower(current($path));
        }

        // If the provided parameter is not an object, return null
        return null;
    }
}
