<?php

use common\helpers\Utilities;

/** @var string $description */
/** @var bool $asMarkdown */
?>
<div class="container g-0 p-0">
    <?php if ($asMarkdown): ?>
        <?= \common\widgets\MarkDown::widget(['content' => $description]) ?>
    <?php else: ?>
        <?= Utilities::encode($description) ?>
    <?php endif; ?>
</div>
