<?php

/**
 * StoryNeedClass Helper
 *
 * This helper class provides methods to generate HTML markup for a list of
 * character classes within a story.
 * It dynamically creates checkboxes for each character class and marks the
 * checkboxes as checked based on the players present in a tavern associated
 * with the story.
 *
 * Usage:
 *   - Call StoryNeedClass::exists($story) to generate HTML output for character classes if they exist.
 *
 * @category  Helpers
 * @package   common\helpers
 * @version   1.0
 */

namespace common\helpers;

use frontend\widgets\CheckBox;
use common\models\Story;

class StoryNeededClass {

    /**
     * Generates HTML markup for character classes if the story object contains them.
     * Each character class is represented as a disabled checkbox, and the checkbox is
     * marked as checked if any player in the tavern has the corresponding class.
     *
     * @param  Story $story The story object containing properties:
     *                       - `id`: Unique identifier for the story.
     *                       - `classes`: Array of class objects, each with `id` and `name`.
     *                       - `tavern`: (Optional) Object containing `players` array.
     * @return string        HTML markup with checkboxes for each class in the story,
     *                       or an empty string if no classes are present.
     */
    public static function classList(Story $story): string {
        if (!$story->classes) {
            return '';
        }

        $checkboxes = '';
        foreach ($story->classes as $class) {
            $checkboxes .= CheckBox::widget([
                'id' => "story{$story->id}-{$class->id}",
                'checked' => self::checked($story, $class),
                'disabled' => 'disabled',
                'label' => $class->name
            ]);
        }

        return '<ul class="list list--check">Expected character classes:' . $checkboxes . '</ul>';
    }

    /**
     * Checks if a character class should be marked as checked based on players in the tavern.
     * A checkbox is marked as checked if a player in the tavern has the specified class.
     *
     * @param  object $story The story object containing:
     *                       - `tavern`: (Optional) Object containing `players` array.
     * @param  object $class The character class object to check, with property:
     *                       - `id`: Unique identifier for the character class.
     * @return string        Returns " checked" if the class is found in the tavern's players;
     *                       otherwise, returns an empty string.
     */
    private static function checked($story, $class) {
        $tavern = $story->tavern;

        if ($tavern) {
            foreach ($tavern->players as $player) {
                // Check if the player's class ID matches the specified class ID
                if ($player->class->id === $class->id) {
                    return "checked";
                }
            }
        }

        return "";
    }
}
