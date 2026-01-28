<?php

namespace frontend\widgets;

use common\helpers\Utilities;
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

    /** @var array<string, string>|null $postParams */
    public ?array $postParams = null;      // Associative array ['param' => value, ...] for hidden POST params

    /** @var array<string, mixed>|null $ariaParams */
    public ?array $ariaParams = null;      // Associative array ['param' => value, ...] for aria attributes

    /**
     *
     * @return string
     */
    public function run(): string
    {
        if ($this->isPost) {
            return $this->postForm();
        }
        return ($this->mode === 'icon') ? $this->iconButton() : $this->button();
    }

    /**
     *
     * @return string
     */
    private function button(): string
    {
// Caution: The spaces at the beginning of the line are intentional; do not delete them.
//
// If the btn style is not defined by the user, default it to 'btn-seconday'
        $paramStyle = $this->style ?? '';
        $defaultBtn = (strpos($paramStyle, "btn-") !== false) ? '' : ' btn-secondary';
        $style = ($this->isCta ? ' btn-warning' : $defaultBtn) . ' ' . $paramStyle;
        $icon = $this->icon ? $this->iconElement() : '';
        $title = Html::encode($this->title ?? '');

        $html = $this->anchorTag('btn', $style) . "{$icon} {$title}</a>";

        return $html;
    }

    /**
     *
     * @return string
     */
    private function iconButton(): string
    {
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
    private function anchorTag(string $baseCss, string $AdditionalCss): string
    {
        $url = $this->url ?? '#';
        $closeModal = $this->isCloseModal ? ' data-bs-dismiss="modal"' : '';
        $onclick = $this->onclick ? " onclick=\"{$this->onclick}\"" : '';
        $id = $this->idElement();
        $aria = $this->ariaParams ? ' ' . Utilities::formatAttributes($this->ariaParams) : '';

        $html = "<a href=\"{$url}\" {$id} role=\"button\" class=\"{$baseCss} {$AdditionalCss}\"{$closeModal}{$this->tooltipElement()}{$aria}{$onclick}>";
        return $html;
    }

    /**
     *
     * @return string
     */
    private function iconElement(): string
    {
        $bootstropIcon = $this->icon ?? 'dnd-logo';
        return "<i class=\"bi {$bootstropIcon}\"></i>";
    }

    /**
     *
     * @return string
     */
    private function tooltipElement(): string
    {
// Caution: The spaces at the beginning of the line are intentional; do not delete them.
        $tooltip = Html::encode($this->tooltip ?? '');
        return $this->tooltip ? " data-bs-toggle=\"tooltip\" title=\"{$tooltip}\" data-bs-placement=\"bottom\"" : '';
    }

    /**
     *
     * @return string
     */
    private function idElement(): string
    {
// Caution: The spaces at the beginning of the line are intentional; do not delete them.
        return $this->id ? " id=\"{$this->id}\"" : '';
    }

    /**
     *
     * @return string
     */
    private function postForm(): string
    {
        $request = Yii::$app->request;
        $html = "<form action=\"{$this->url}\" method=\"POST\">";
        $html .= "<input type=\"hidden\" name=\"{$request->csrfParam}\" value=\"{$request->csrfToken}\">";
        if ($this->postParams) {
            foreach ($this->postParams as $name => $value) {
                $html .= "<input type=\"hidden\" name=\"{$name}\" value=\"{$value}\">";
            }
        }
        $html .= $this->postButton();
        $html .= '</form>';

        return $html;
    }

    /**
     *
     * @return string
     */
    private function postButton(): string
    {
        // Caution: The spaces at the beginning of the line are intentional; do not delete them.
        $style = $this->style ?? '';
        $button = $this->isCta ? "btn btn-warning {$style}" : "btn btn-secondary {$style}";
        $tooltip = $this->tooltipElement();
        $title = Html::encode($this->title ?? '');
        $id = $this->idElement();
        $icon = $this->icon ? $this->iconElement() : '';

        $html = "<button {$id} role=\"button\" class=\"{$button}\" {$tooltip}>"
                . "{$icon} {$title}"
                . "</button>";

        return $html;
    }
}
