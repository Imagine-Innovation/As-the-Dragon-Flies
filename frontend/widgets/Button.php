<?php

namespace frontend\widgets;

use yii\base\Widget;
use yii\helpers\Url;

class Button extends Widget {

    public $route;
    public $icon;
    public $title;

    public function run() {
        $url = Url::toRoute($this->route);
        $icon = $this->icon ? '<i class = "bi ' . $this->icon . '"></i>' : "";

        $html = '<a class="btn btn-warning text-decoration" href="' . $url . '">' .
                $icon . ' ' .
                $this->title .
                '</a>';

        return $html;
    }
}
