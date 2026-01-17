<?php

namespace common\helpers;

use common\models\AccessRight;
use common\models\User;
use frontend\widgets\CheckBox;

class SpecialCheckBox
{

    /**
     * Generates an HTML checkbox for a given role on a model.
     *
     * This function creates a checkbox element that indicates whether the model
     * has the specified role.
     * The checkbox includes a JavaScript `onclick` event to toggle the role.
     *
     * @param AccessRight|User $model The model object which contains the role information.
     * @param string $role The role to check for and create a checkbox for.
     * @return string The generated HTML for the checkbox, or an empty string
     *                if the role property is not set on the model.
     */
    public static function setUserRole(AccessRight|User $model, string $role): string {
        // Construct the property name for the role (e.g., 'is_admin' for the 'admin' role)
        $property = 'is_' . $role;

        // Check if the model has the role property
        if (isset($model->$property)) {
            // Determine if the checkbox should be checked based on the model's role property
            $checked = $model->$property ? "checked" : '';

            $html = CheckBox::widget([
                'id' => "user-{$role}-{$model->id}",
                'onclick' => "UserManager.setRole({$model->id}, '{$role}');",
                'checked' => $checked
            ]);
        } else {
            // If the role property is not set on the model, return an empty string
            $html = '';
        }

        // Return the generated HTML
        return $html;
    }

    /**
     * Generates an HTML checkbox for a given access right on a model.
     *
     * This function creates a checkbox element that indicates whether the model
     * has the specified access right.
     * The checkbox includes a JavaScript `onclick` event to toggle the access right.
     *
     * @param AccessRight|User $model The model object which contains the access right information.
     * @param string $access The access right to check for and create a checkbox for.
     * @return string The generated HTML for the checkbox, or an empty string
     *                if the access right property is not set on the model.
     */
    public static function setAccessRight(AccessRight|User $model, string $access): string {
        // Check if the model has the role property
        if (isset($model->$access)) {
            // Determine if the checkbox should be checked based on the model's role property
            $checked = $model->$access ? "checked" : '';
            $html = CheckBox::widget([
                'id' => "access-right-{$access}-{$model->id}",
                'onclick' => "UserManager.setAccessRight({$model->id}, '{$access}');",
                'checked' => $checked
            ]);
        } else {
            // If the role property is not set on the model, return an empty string
            $html = '';
        }

        // Return the generated HTML
        return $html;
    }
}
