<?php

namespace common\widgets;

use yii\base\Widget;

class CheckBox extends Widget
{

    public ?string $id = null;
    public ?string $onclick = null;
    public ?string $checked = null;
    public ?string $disabled = null;
    public ?string $label = null;
    public ?string $title = null;
    public ?string $icon = null;

    /**
     *
     * @return string
     */
    public function run(): string
    {
        $id = $this->id ? 'id="' . $this->id . '"' : '';
        $onClick = $this->onclick ? 'onclick="' . $this->onclick . '"' : '';
        $checked = $this->checked ?? '';
        $disabled = $this->disabled ?? '';

        $for = $this->id ? 'for="' . $this->id . '"' : '';
        $icon = $this->icon ? '<i class = "bi ' . $this->icon . '"></i> ' : '';
        $label = $this->label ?? '';

        $startTooltip = $this->title ? '<a href="#" title="' . $this->title . '" data-bs-toggle="tooltip" data-placement="right">'
                    : '';
        $endTooltip = $this->title ? '</a>' : '';

        $html = <<<HTML
            <div class="custom-control custom-checkbox mb-2">{$startTooltip}
                <input type="checkbox" class="custom-control-input" {$id} {$onClick} {$checked} {$disabled}>
                <label class="custom-control-label" {$for}>{$icon}{$label}</label>{$endTooltip}
            </div>
            HTML;
        return $html;
    }
}
