<?php

use common\components\AppStatus;
use common\widgets\MarkDown;
use yii\helpers\Html;

/* @var yii\web\View $this */
/* @var common\models\Story $story */
/* @var string $storyRoot */
/* @var string[] $t */
?>
<h1><?= Html::encode($story->name) ?></h1>
<div class="row g-2 d-flex"> <!-- Add d-flex to the row -->
    <?php if ($story->image): ?>
        <div class="col-md-4 d-flex align-items-stretch"> <!-- Add d-flex and align-items-stretch -->
            <img src="<?= $storyRoot ?>/img/<?= $story->image ?>" class="img-fluid object-fit-cover rounded-start w-100" alt="<?= $story->name ?>" style="max-height:200px;">
        </div>
        <div class="col-md-8 text-decoration d-flex flex-column"> <!-- Add d-flex and flex-column -->
        <?php else: ?>
            <div class="col-12 text-decoration"> <!-- Add d-flex and flex-column -->
            <?php endif; ?>
            <div class="mb-3">
                <?= MarkDown::widget(['content' => $story->description ?? '']) ?>
            </div>
            <div class="mb-4">
                <ul>
                    <li><span class="fw-bold"><?= $t['level_range'] ?>:</span> <?= $story->min_level ?> - <?= $story->max_level ?></li>
                    <li><span class="fw-bold"><?= $t['players'] ?>:</span> <?= $story->min_players ?> - <?= $story->max_players ?></li>
                    <li><span class="fw-bold">Status:</span> <?= AppStatus::from($story->status)->getLabel() ?></li>
                    <li><span class="fw-bold">Language:</span> <?= Html::encode($story->language) ?></li>
                </ul>
            </div>
            <?php if ($story->tags): ?>
                <div class="story-tags mb-3">
                    <span class="fw-bold"><?= $t['tags'] ?>:</span>
                    <?php foreach ($story->tags as $tag): ?>
                        <span class="badge text-bg-secondary"><?= Html::encode($tag->name) ?></span>
                        <?php if (!empty($tag->description)): ?>
                            <div class="ms-3 small text-muted"><?= MarkDown::widget(['content' => $tag->description ?? '']) ?></div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
