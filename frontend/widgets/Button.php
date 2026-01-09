<?php

namespace frontend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class Button extends Widget
{

    public bool $isPost = false;        // 'true' if the button triggers a POST request, default='false'
    public ?string $url = null;         // URL to call when the button is clicked, default='#'
    public ?string $id = null;          // Button ID (for javascript purpose)
    public ?string $style = null;       // Additional CSS class
    public ?string $tooltip = null;     // Button tooltip
    public ?string $icon = null;         // Icon to display before the button name
    public ?string $modal = null;       // Name of the modal to display when clicking on the button
    public ?string $title = null;       // Button name
    public ?string $mode = null;        // “icon” to use it as an icon button, otherwise Bootstrap button behavior
    public ?string $onclick = null;     // javascript hook to trigger onclick
    public bool $isCta = false;         // when 'true' is call to action (CTA) button
    public bool $isCloseModal = false;  // when 'true' is adding data-bs-dismiss="modal"

    /** @var array<string, mixed> $postParams */
    public array $postParams = [];      // Associative array ['param' => value, ...] for hidden POST params

    /** @var array<string, mixed> $ariaParams */
    public array $ariaParams = [];      // Associative array ['param' => value, ...] for aria attributes

    /**
     *
     * @return string
     */
    public function run(): string {
        if ($this->isPost) {
            return $this->postForm();
        }
        return ($this->mode === 'icon') ? $this->iconButton() : $this->button();
    }

    /**
     *
     * @return string
     */
    private function button(): string {
        // Caution: The spaces at the beginning of the line are intentional; do not delete them.
        //
        // If the btn style is not defined by the user, default it to 'btn-seconday'
        $paramStyle = $this->style ?? '';
        $defaultBtn = (strpos($paramStyle, "btn-") !== false) ? '' : ' btn-secondary';
        $style = ($this->isCta ? ' btn-warning' : $defaultBtn) . ' ' . $paramStyle;
        $icon = $this->icon ? $this->iconElement() : "";
        $title = Html::encode($this->title ?? '');

        $html = $this->anchorTag('btn', $style) . "{$icon} {$title}</a>";

        return $html;
    }

    /**
     *
     * @param array<string, mixed>|null $paramArray
     * @return string
     */
    private function array2HTMLAttributes(?array $paramArray): string {
        if (!$paramArray) {
            return '';
        }

        $html = '';
        foreach ($paramArray as $attribute => $value) {
            // Caution: The spaces at the beginning of the line
            // are intentional; do not delete them.
            $html .= " {$attribute}=\"{$value}\"";
        }

        return $html;
    }

    /**
     *
     * @return string
     */
    private function iconButton(): string {
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

    /**
     *
     * @param string $baseCss
     * @param string $AdditionalCss
     * @return string
     */
    private function anchorTag(string $baseCss, string $AdditionalCss): string {
        $url = $this->url ?? '#';
        $closeModal = $this->isCloseModal ? ' data-bs-dismiss="modal"' : '';
        $onclick = $this->onclick ? " onclick=\"{$this->onclick}\"" : '';
        $id = $this->idElement();
        $aria = $this->array2HTMLAttributes($this->ariaParams);

        $html = "<a href=\"{$url}\" {$id} role=\"button\" class=\"{$baseCss} {$AdditionalCss}\"{$closeModal}{$this->tooltipElement()}{$aria}{$onclick}>";
        return $html;
    }

    /**
     *
     * @return string
     */
    private function iconElement(): string {
        return '<i class="bi ' . ($this->icon ?? "dnd-logo") . '"></i>';
    }

    /**
     *
     * @return string
     */
    private function tooltipElement(): string {
        // Caution: The spaces at the beginning of the line are intentional; do not delete them.
        return $this->tooltip ? ' data-bs-toggle="tooltip" title="' . Html::encode($this->tooltip) . '" data-bs-placement="bottom"' : '';
    }

    /**
     *
     * @return string
     */
    private function idElement(): string {
        // Caution: The spaces at the beginning of the line are intentional; do not delete them.
        return $this->id ? " id=\"{$this->id}\"" : '';
    }

    /**
     *
     * @return string
     */
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

    /**
     *
     * @return string
     */
    private function postButton() {
        // Caution: The spaces at the beginning of the line are intentional; do not delete them.
        $style = ($this->isCta ? ' btn-warning' : ' btn-secondary') . ' ' . ($this->style ?? '');

        $icon = $this->icon ? $this->iconElement() : "";

        $html = '<button ' . $this->idElement() . ' role="button" class="btn' . $style . '"' . $this->tooltipElement() . '>';
        $html .= "{$icon} " . Html::encode($this->title ?? '');
        $html .= "</button>";

        return $html;
    }
}
