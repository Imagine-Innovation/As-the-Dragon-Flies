<?php

use frontend\widgets\Button;

/** @var yii\web\View $this */
/** @var string $title */
/** @var string $img */
/** @var string[] $paragraphs */
/** @var array $button */
?>
<section id="level1">
    <div class="card overflow-hidden rounded-4">
        <div class="row g-0 h-100">
            <!-- Image on the left (40% width on md and up, 100% on small) -->
            <div class="col-12 col-md-4" style="background: url('<?= $img ?>') center center / cover no-repeat;">
            </div>

            <!-- Card Body on the right (remaining width) -->
            <div class="col-12 col-md-8 d-flex flex-column p-4">
                <div class="card-body">
                    <h5 class="card-title text-decoration text-yellow"><?= $title ?></h5>
                    <?php foreach ($paragraphs as $paragraph): ?>
                        <p class="lead card-text text-decoration"><?= $paragraph ?></p>
                    <?php endforeach; ?>
                    <?= Button::widget($button) ?>
                </div>
            </div>
        </div>
    </div>
</section>
