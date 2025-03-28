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

class StoryNeededClass {

    /**
     * Generates HTML markup for character classes if the story object contains them.
     * Each character class is represented as a disabled checkbox, and the checkbox is
     * marked as checked if any player in the tavern has the corresponding class.
     *
     * @param  object $story The story object containing properties:
     *                       - `id`: Unique identifier for the story.
     *                       - `classes`: Array of class objects, each with `id` and `name`.
     *                       - `tavern`: (Optional) Object containing `players` array.
     * @return string        HTML markup with checkboxes for each class in the story,
     *                       or an empty string if no classes are present.
     *
      public static function ClassList($story) {
      $html = "";

      if ($story->classes) {
      $html .= '<ul class="list list--check">Expected character classes:';

      foreach ($story->classes as $class) {
      // Generate unique input ID using the story and class IDs
      $inputId = "story" . $story->id . "-" . $class->id;

      // Add a checkbox for each class, with the unique input ID
      $html .= '<div class="custom-control custom-checkbox mb-2">';
      $html .= '<input type="checkbox" class="custom-control-input" ';
      $html .= 'id="' . $inputId . '" ' . self::checked($story, $class) . ' disabled>';
      $html .= '<label class="custom-control-label" for="' . $inputId . '">' . $class->name . '</label>';
      $html .= '</div>';
      }

      $html .= '</ul>';
      }
      return $html;
      }
     */
    public static function ClassList($story) {
        if (!$story->classes) {
            return '';
        }

        $checkboxes = '';
        foreach ($story->classes as $class) {
            $inputId = "story{$story->id}-{$class->id}";
            $checkboxes .= <<<HTML
        <div class="custom-control custom-checkbox mb-2">
            <input type="checkbox"
                   class="custom-control-input"
                   id="{$inputId}"
                   {self::checked($story, $class)}
                   disabled>
            <label class="custom-control-label" for="{$inputId}">{$class->name}</label>
        </div>
        HTML;
        }

        return <<<HTML
    <ul class="list list--check">
        Expected character classes:
        {$checkboxes}
    </ul>
    HTML;
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
