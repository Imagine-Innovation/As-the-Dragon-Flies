<?php

use frontend\widgets\ToolMenu;

/* @var $this yii\web\View */
/* @var string $userName */
/* @var string $playerName */
/* @var string $webRoot */
?>
<header class="content__title h3">
    <p>
        Welcome back <span class="text-decoration"><?= $playerName ?></span>
    </p>
</header>
<h5>What would you like to do?</h5>
<div class="container">
    <?= ToolMenu::widget(['mode' => 'lobby']) ?>
</div>
