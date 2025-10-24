<?php

namespace frontend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class Button extends Widget
{

    public bool $isPost = false;        // 'true' if the button triggers a POST request, default='false'
    public $postParams = [];            // Associative array ['param' => value, ...] for hidden POST params
    public $url;                        // URL to call when the button is clicked, default='#'
    public $id;                         // Button ID (for javascript purpose)
    public $style;                      // Additional CSS class
    public $tooltip;                    // Button tooltip
    public $icon;                       // Icon to display before the button name
    public $modal;                      // Name of the modal to display when clicking on the button
    public $title;                      // Button name
    public $mode;                       // “icon” to use it as an icon button, otherwise Bootstrap button behavior
    public $onclick;                    // javascript hook to trigger onclick
    public bool $isCta = false;         // when 'true' is call to action (CTA) button
    public bool $isCloseModal = false;  // when 'true' is adding data-bs-dismiss="modal"

    public function run() {
        if ($this->isPost) {
            return $this->postForm();
        }
        return $this->mode == 'icon' ? $this->iconButton() : $this->button();
    }

    private function button() {
        // Caution: The spaces at the beginning of the line are intentional; do not delete them.
        //
        // If the btn style is not defined by the user, default it to 'btn-seconday'
        $paramStyle = $this->style ?? '';
        $defaultBtn = (strpos($paramStyle, "btn-") !== false) ? '' : ' btn-secondary';
        $style = ($this->isCta ? ' btn-warning' : $defaultBtn) . ' ' . $paramStyle;
        $icon = $this->icon ? $this->iconElement() : "";
        $title = Html::encode($this->title);

        $html = $this->anchorTag('btn', $style) . "{$icon} {$title}</a>";

        return $html;
    }

    private function iconButton() {
        $style = $this->style ?? '';
        $icon = $this->iconElement();

        $html = $this->anchorTag('btn', $style);

        if ($this->modal) {
            $html .= "<span data-bs-toggle=\"modal\" data-bs-target=\"#{$this->modal}\">{$icon}</span></a>";
        } else {
            $html .= "{$icon}</a>";
        }

        return $html;
    }

    private function anchorTag(string $baseCss, string $AdditionalCss): string {
        $url = $this->url ?? '#';
        $closeModal = $this->isCloseModal ? ' data-bs-dismiss="modal"' : '';
        $onclick = $this->onclick ? " onclick=\"{$this->onclick}\"" : '';
        $id = $this->idElement();

        $html = "<a href=\"{$url}\" {$id} role=\"button\" class=\"{$baseCss} {$AdditionalCss}\"{$closeModal}{$this->tooltipElement()}{$onclick}>";
        return $html;
    }

    private function iconElement(): string {
        return '<i class="bi ' . ($this->icon ?? "dnd-logo") . '"></i>';
    }

    private function tooltipElement(): string {
        // Caution: The spaces at the beginning of the line are intentional; do not delete them.
        return $this->tooltip ? ' data-bs-toggle="tooltip" title="' . Html::encode($this->tooltip) . '" data-bs-placement="bottom"' : '';
    }

    private function idElement(): string {
        // Caution: The spaces at the beginning of the line are intentional; do not delete them.
        return $this->id ? " id=\"{$this->id}\"" : '';
    }

    private function postForm(): string {
        $html = '<form action="' . $this->url . '" method="POST">';
        $html .= '<input type="hidden" name="' . Yii::$app->request->csrfParam . '" value="' . Yii::$app->request->csrfToken . '">';
        foreach ($this->postParams as $name => $value) {
            $html .= '<input type="hidden" name="' . $name . '" value="' . $value . '">';
        }
        $html .= $this->postButton();
        $html .= '</form>';

        return $html;
    }

    private function postButton() {
        // Caution: The spaces at the beginning of the line are intentional; do not delete them.
        $style = ($this->isCta ? ' btn-warning' : ' btn-secondary') . ' ' . ($this->style ?? '');

        $icon = $this->icon ? $this->iconElement() : "";

        $html = '<button ' . $this->idElement() . ' role="button" class="btn' . $style . '"' . $this->tooltipElement() . '>';
        $html .= "{$icon} " . Html::encode($this->title);
        $html .= "</button>";

        return $html;
    }
}
