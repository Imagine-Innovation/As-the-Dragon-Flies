<?php

namespace frontend\helpers;

use Yii;
use yii\helpers\Html;

class Caligraphy
{

    /**
     *
     * @return string
     */
    public static function appName(): string {
        return '<span class="text-decoration">' . Yii::$app->name . '</span>';
    }

    /**
     *
     * @param array<string> $paragraphs
     * @param string $textClassName
     * @return string
     */
    public static function illuminate(array $paragraphs, string $textClassName = ''): string {

        $formatedParagraphs = [];
        foreach ($paragraphs as $paragraph) {
            $formatedParagraphs[] = "<p class='" . Html::encode($textClassName) . " illuminate'>$paragraph</p>";
        }
        return implode(PHP_EOL, $formatedParagraphs);
    }
}
