<?php

namespace common\helpers;

class CheckBox {

    /**
     * Generates an HTML checkbox for a given role on a model.
     *
     * This function creates a checkbox element that indicates whether the model
     * has the specified role.
     * The checkbox includes a JavaScript `onclick` event to toggle the role.
     *
     * @param object $model The model object which contains the role information.
     * @param string $role The role to check for and create a checkbox for.
     * @return string The generated HTML for the checkbox, or an empty string
     *                if the role property is not set on the model.
     */
    public static function setUserRole($model, $role) {
        // Construct the property name for the role (e.g., 'is_admin' for the 'admin' role)
        $property = 'is_' . $role;

        // Check if the model has the role property
        if (isset($model->$property)) {
            // Determine if the checkbox should be checked based on the model's role property
            $checked = $model->$property ? "checked" : "";

            // Generate the HTML for the checkbox
            /*
              $html = '<div class="custom-control custom-checkbox mb-2">';
              $html .= '<input type="checkbox" class="custom-control-input" ';
              $html .= 'id="user-' . $role . '-' . $model->id . '" ';
              $html .= 'onclick="UserManager.setRole(' . $model->id . ', \'' . $role . '\');" ';
              $html .= $checked . '>';
              $html .= '<label class="custom-control-label" for="user-' . $role . '-' . $model->id . '">';
              $html .= '</label>';
              $html .= '</div>';
             *
             */
            $html = <<<HTML
<div class="custom-control custom-checkbox mb-2">
    <input type="checkbox"
           class="custom-control-input"
           id="user-{$role}-{$model->id}"
           onclick="UserManager.setRole({$model->id}, '{$role}');"
           {$checked}>
    <label class="custom-control-label" for="user-{$role}-{$model->id}"></label>
</div>
HTML;
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
     * @param object $model The model object which contains the access right information.
     * @param string $access The access right to check for and create a checkbox for.
     * @return string The generated HTML for the checkbox, or an empty string
     *                if the access right property is not set on the model.
     */
    public static function setAccessRight($model, $access) {
        // Check if the model has the role property
        if (isset($model->$access)) {
            // Determine if the checkbox should be checked based on the model's role property
            $checked = $model->$access ? "checked" : "";

            // Generate the HTML for the checkbox
            /*
              $html = '<div class="custom-control custom-checkbox mb-2">';
              $html .= '<input type="checkbox" class="custom-control-input" ';
              $html .= 'id="access-right-' . $access . '-' . $model->id . '" ';
              $html .= 'onclick="UserManager.setAccessRight(' . $model->id . ', \'' . $access . '\');" ';
              $html .= $checked . '>';
              $html .= '<label class="custom-control-label" for="access-right-' . $access . '-' . $model->id . '">';
              $html .= '</label>';
              $html .= '</div>';
             *
             */
            $html = <<<HTML
<div class="custom-control custom-checkbox mb-2">
    <input type="checkbox"
           class="custom-control-input"
           id="access-right-{$access}-{$model->id}"
           onclick="UserManager.setAccessRight({$model->id}, '{$access}');"
           {$checked}>
    <label class="custom-control-label" for="access-right-{$access}-{$model->id}"></label>
</div>
HTML;
        } else {
            // If the role property is not set on the model, return an empty string
            $html = '';
        }

        // Return the generated HTML
        return $html;
    }
}
