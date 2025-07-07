<?php

namespace frontend\widgets;

use yii\base\Widget;

class CheckBox extends Widget {

    public $id;
    public $onclick;
    public $checked;
    public $disabled;
    public $label;
    public $title;
    public $icon;

    public function run() {

        $id = $this->id ? 'id="' . $this->id . '"' : "";
        $onClick = $this->onclick ? 'onclick="' . $this->onclick . '"' : "";
        $checked = $this->checked ?? "";
        $disabled = $this->disabled ?? "";

        $for = $this->id ? 'for="' . $this->id . '"' : "";
        $icon = $this->icon ? '<i class = "bi ' . $this->icon . '"></i> ' : "";
        $label = $this->label ?? "";

        $startTooltip = $this->title ? '<a href="#" title="' . $this->title . '" data-bs-toggle="tooltip" data-placement="right">' : "";
        $endTooltip = $this->title ? "</a>" : "";

        $html = <<<HTML
<div class="custom-control custom-checkbox mb-2">{$startTooltip}
    <input type="checkbox" class="custom-control-input" {$id} {$onClick} {$checked} {$disabled}>
    <label class="custom-control-label" {$for}>{$icon}{$label}</label>{$endTooltip}
</div>
HTML;
        return $html;
    }
}
