<?php

namespace frontend\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class IconButton extends Widget {

    public $url;
    public $id;
    public $css;
    public $onclick;
    public $tooltip;
    public $icon;
    public $modal;

    public function run() {
        $css = $this->css ?? '';
        $bootstrapIcon = '<i class="bi ' . ($this->icon ?? "dnd-logo") . '"></i>';

        // Caution: The spaces at the beginning of the line are intentional; do not delete them.
        $id = $this->id ? ' id="' . $this->id . '"' : '';
        $onclick = $this->onclick ? ' onclick="' . $this->onclick . '"' : '';
        $tooltip = $this->tooltip ? ' data-bs-toggle="tooltip" title="' . Html::encode($this->tooltip) . '" data-bs-placement="bottom"' : '';

        $html = '<a href="' . ($this->url ?? '#') . '"' . $id . ' role="button" class="actions__item ' . $css . '"' . $onclick . $tooltip . '>';

        if ($this->modal) {
            $html .= '<span data-bs-toggle="modal" data-bs-target="#' . $this->modal . '">' . $bootstrapIcon . '</span>';
        } else {
            $html .= $bootstrapIcon;
        }

        $html .= "</a>";

        return $html;
    }
}
