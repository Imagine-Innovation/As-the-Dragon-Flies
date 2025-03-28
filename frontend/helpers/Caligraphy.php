<?php

namespace frontend\helpers;

use Yii;
use yii\helpers\Html;

class Caligraphy {

    public static function appName() {
        return '<span class="text-decoration">' . Yii::$app->name . '</span>';
    }

    public static function illuminate(array $paragraphs, string $textClassName = '') {

        $formatedParagraphs = [];
        foreach ($paragraphs as $paragraph) {
            $formatedParagraphs[] = "<p class='" . Html::encode($textClassName) . " illuminate'>$paragraph</p>";
        }
        return implode("\n", $formatedParagraphs);
    }
}
