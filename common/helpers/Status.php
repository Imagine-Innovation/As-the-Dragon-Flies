<?php

namespace common\helpers;

use common\components\AppStatus;
use common\helpers\Utilities;
use Yii;
use yii\helpers\Url;

class Status
{

    /**
     * Returns the string representation of a status code.
     *
     * @param ?int $statusCode The status code to be converted.
     * @return string The string representation of the status code.
     */
    public static function label(?int $statusCode = null): string {
        if (!$statusCode) {
            return 'Undefined';
        }

        $status = AppStatus::tryFrom($statusCode);
        return $status?->getLabel() ?? 'Unknown';
    }

    /**
     * Generates an HTML string for displaying a status icon with a tooltip.
     *
     * @param int|null $statusCode The status code for which the icon and tooltip need to be generated.
     * @return string The HTML string representing the status icon with a tooltip.
     */
    public static function icon(?int $statusCode = null): string {
        $defaultIcon = ['icon' => 'bi-exclamation-square', 'tooltip' => 'Undefined'];

        $icon = $statusCode ?
                (AppStatus::tryFrom($statusCode)?->getIcon() ?? $defaultIcon) :
                $defaultIcon;

        $iconClass = $icon['icon'];
        $tooltip = $icon['tooltip'];

        return sprintf(
                '<a title="%s" data-bs-toggle="tooltip" data-placement="top"><i class="bi %s h5"></i></a>',
                Utilities::encode($tooltip),
                Utilities::encode($iconClass)
        );
    }

    /**
     * Changes the status of a model.
     *
     * @param \yii\db\ActiveRecord $model The model to update.
     * @param int $statusCode The new status code to be set. This should be a valid code value from AppStatus enum.
     * @return bool True if the status change was successful (model saved), false otherwise.
     */
    public static function changeStatus(\yii\db\ActiveRecord $model, int $statusCode): bool {
        // Check if the model exists
        if ($model->isNewRecord) {
            return false;
        }

        $className = get_class($model);

        //if (!property_exists($model, 'status')) {
        if (!$model->hasProperty('status')) {
            Yii::error("Model {$className} does not have a 'status' property.", __METHOD__);
            return false;
        }

        if (!AppStatus::isValidForEntity($className, $statusCode)) {
            $statusCase = AppStatus::tryFrom($statusCode);
            $label = $statusCase ? $statusCase->getLabel() : 'Unknown Status';
            Yii::error("{$label} is not a valid status for {$className} class", __METHOD__);
            return false;
        }

        $model->status = $statusCode;
        return $model->save();
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
    public static function hyperlink($model, $property = 'name'): string {
        $controller = Utilities::modelName($model); // Get the controller name for the model

        $propertyVal = $model->$property;
        $display = isset($model->$property) ?
                Utilities::encode(empty($propertyVal) ? 'Unknown' : $propertyVal) :
                '<i class="bi bi-exclamation-square"></i>';

        if ($controller && isset($model->id) && isset($model->status)) {
            $route = "{$controller}/view";
            return '<a href="' . Url::toRoute([$route, 'id' => $model->id]) . '">' . $display . '</a>';
        }
        return $display;
    }
}
