<?php

use common\widgets\MarkDown;

/** @var string $description */
/** @var string $style */
?>
<div class="container g-0 p-0 <?= $style ?>">
    <?= MarkDown::widget(['content' => $description]) ?>
</div>
