<?php

namespace common\helpers;

use common\helpers\Utilities;
use Yii;
use yii\helpers\Url;

class Status {

    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;
    const STATUS = [
        self::STATUS_DELETED => [
            'icon' => 'bi-x-square',
            'tooltip' => 'Deleted, only adminstrators can restore',
            'display' => 'Deleted',
            'action' => 'restore',
        ],
        self::STATUS_INACTIVE => [
            'icon' => 'bi-code-square',
            'tooltip' => 'Draft or inactive. Need to be validated to be used',
            'display' => 'Draft or inactive',
            'action' => 'validate',
        ],
        self::STATUS_ACTIVE => [
            'icon' => 'bi-caret-right-square',
            'tooltip' => 'Validated and active',
            'display' => 'Validated',
            'action' => 'view',
        ],
    ];

    /**
     * Returns the string representation of a status code.
     *
     * This function converts a given status code into its corresponding string
     * representation.
     * It handles several predefined status codes, providing a meaningful label
     * for each.
     *
     * @param int|null $status The status code to be converted. It can be one
     *                         of the predefined constants or null.
     * @return string The string representation of the status code.
     *                If the status code is not recognized, 'Unknown' is returned.
     */
    public static function label($status) {
        if (is_null($status)) {
            return 'Undefined';
        }

        return self::STATUS[$status]['display'] ?? 'Unknown';
    }

    /**
     * Generates an HTML string for displaying a status icon with a tooltip.
     *
     * This function returns an HTML anchor element containing an icon representing
     * the given status. The icon is displayed with a tooltip that provides
     * additional information about the status. The function handles several
     * predefined status codes and assigns corresponding icons and tooltips.
     * If the status code is not recognized, it returns a default icon and tooltip.
     *
     * @param int|null $status The status code for which the icon and tooltip
     *                         need to be generated. It can be one of the
     *                         predefined constants or null.
     * @return string The HTML string representing the status icon with a tooltip.
     */
    public static function icon($status): string {
        if (is_null($status)) {
            $icon = 'bi-exclamation-square';
            $tooltip = 'Undefined';
        } elseif (isset(self::STATUS[$status])) {
            $icon = self::STATUS[$status]['icon'];
            $tooltip = self::STATUS[$status]['tooltip'];
        } else {
            $icon = 'bi-question-square';
            $tooltip = 'Unknown';
        }

        /*
          return sprintf(
          '<a title="%s" data-toggle="tooltip" data-placement="top"><i class="bi %s h5"></i></a>',
          $tooltip,
          $icon
          );
         *
         */
        return <<<HTML
<a title="{$tooltip}" data-toggle="tooltip" data-placement="top">
    <i class="bi {$icon} h5"></i>
</a>
HTML;
    }

    /**
     * Changes the status of a model.
     *
     * This function validates the new status, updates the user's status if it
     * is valid, and saves the changes to the database.
     *
     * @param \yii\db\ActiveRecord $model The model to update
     * @param int $status The new status to be set. Must be one of
     *                    Self::STATUS_DELETED,
     *                    Self::STATUS_ACTIVE, or
     *                    Self::STATUS_INACTIVE.
     * @return bool True if the status change was successful, false otherwise.
     */
    public static function changeStatus($model, $status) {
        // Check if the model exists
        if (!$model) {
            return false;
        }

        // Check if status is valid
        if (isset(self::STATUS[$status])) {
            // Update the status of the user
            $model->status = $status;

            // Save the changes to the database and update the return value
            return $model->save();
        }
        // Status is invalid
        return false;
    }

    /**
     * Generates an HTML hyperlink for a given model based on its status and
     * properties.
     *
     * This function constructs an HTML hyperlink for a model, using the specified
     * property as the display text.
     * It determines the appropriate route based on the model's status and includes
     * a fallback for undefined properties.
     *
     * @param object $model The model object for which to create the hyperlink.
     * @param string $property The property of the model to use as the hyperlink
     *                         text, default is 'name'.
     * @return string The generated HTML hyperlink or a placeholder if the model
     *                or property is invalid.
     */
    public static function hyperlink($model, $property = 'name') {
        $controller = Utilities::modelName($model); // Get the controller name for the model

        $display = isset($model->$property) ?
                Utilities::encode(empty($model->$property) ? 'Unknown' : $model->$property) :
                '<i class="bi bi-exclamation-square"></i>';

        if (
                !$controller ||
                !isset($model->id) ||
                !isset($model->status) ||
                !isset(self::STATUS[$model->status])
        ) {
            return $display;
        }

        $route = $controller . '/view';

        return '<a href="' . Url::toRoute([$route, 'id' => $model->id]) . '">' . $display . '</a>';
    }
}
