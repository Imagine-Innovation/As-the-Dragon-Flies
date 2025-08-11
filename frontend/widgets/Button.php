<?php

namespace frontend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class Button extends Widget {

    public $url;
    public $id;
    public $style;
    public $tooltip;
    public $icon;
    public $modal;
    public $title;
    public $mode;
    public $onclick;
    public $callToAction;

    public function run() {
        return $this->mode == 'icon' ? $this->iconButton() : $this->button();
    }

    private function button() {
        $cta = $this->callToAction ?? false;
        // Caution: The spaces at the beginning of the line are intentional; do not delete them.
        $style = ($cta ? ' btn-warning' : '') . ' ' . ($this->style ?? '');

        $icon = $this->icon ? $this->iconElement() : "";

        $html = '<a href="' . ($this->url ?? '#') . '"' . $this->idElement() . ' role="button" class="btn' . $style . '"' . $this->tooltipElement() . '>';
        $html .= "{$icon} " . Html::encode($this->title);
        $html .= "</a>";

        return $html;
    }

    private function buttonV1() {
        $url = ($this->url ?? '#');
        $icon = $this->icon ? '<i class = "bi ' . $this->icon . '"></i>' : "";

        $html = '<a class="btn btn-warning text-decoration" href="' . $url . '">' .
                $icon . ' ' .
                $this->title .
                '</a>';

        return $html;
    }

    private function iconButton() {
        $style = $this->style ?? '';
        $icon = $this->iconElement();
        $onclick = $this->onclick ?? '';
        $url = $this->url ?? '#';

        // Caution: The spaces at the beginning of the line are intentional; do not delete them.

        $html = '<a href="' . $url . '"' . $this->idElement() . ' role="button" class="actions__item ' . $style . '"' . $this->tooltipElement() . $onclick . '>';

        if ($this->modal) {
            $html .= '<span data-bs-toggle="modal" data-bs-target="#' . $this->modal . '">' . $icon . '</span>';
        } else {
            $html .= $icon;
        }

        $html .= "</a>";

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
        return $this->id ? ' id="' . $this->id . '"' : '';
    }
}
