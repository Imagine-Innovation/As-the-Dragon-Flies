<?php

use common\widgets\MarkDown;

/** @var string $description */
?>
<div class="container g-0 p-0">
    <?= MarkDown::widget(['content' => $description]) ?>
</div>
