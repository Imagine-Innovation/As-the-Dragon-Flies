<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var string $row */
/** @var string $col */
?>
<div class="<?= $row ?>">
    <div class="<?= $col ?>">
        <?=
        $this->render('section1', [
            'title' => 'Create your first player',
            'img' => Url::to('@web/img/sm/builder.png'),
            'paragraphs' => [
                'Before you can embark on a quest, you need to create a player.',
                'This player will be your representation in the world.',
            ],
            'button' => [
                'url' => Url::toRoute('player-builder/create'),
                'icon' => 'dnd-armor-helmet-large',
                'style' => 'text-decoration mt-auto',
                'tooltip' => null,
                'title' => 'Create a new player',
                'isCta' => true,
            ]
        ])
        ?>
    </div>
</div>
