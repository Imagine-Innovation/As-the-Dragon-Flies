<?php

use frontend\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Chapter $model */
$this->title = $model->name;
$story = $model->story;

$breadcrumbs = [
    ['label' => 'Stories', 'url' => ['story/index']],
    ['label' => $story->name, 'url' => ['story/view', 'id' => $story->id]],
    ['label' => $model->name],
];

// Set breadcrumbs for the view
$this->params['breadcrumbs'] = $breadcrumbs;

\yii\web\YiiAsset::register($this);
?>
<div class="container">
    <div class="card mb-3">
        <div class="actions">
            <?=
    Button::widget([
        'mode' => 'icon',
        'url' => Url::toRoute(['chapter/update', 'id' => $model->id]),
        'icon' => 'dnd-spell',
        'tooltip' => 'Edit chapter',
    ])
?>
        </div>
        <div class="row g-0 d-flex"> <!-- Add d-flex to the row -->
            <div class="col-md-4 d-flex align-items-stretch"> <!-- Add d-flex and align-items-stretch -->
                <img src="resources/story-<?= $model->story_id ?>/img/<?= $model->image ?>" class="img-fluid object-fit-cover rounded-start w-100" alt="<?=
    $model->name
?>">
            </div>
            <div class="col-md-8 text-decoration d-flex flex-column"> <!-- Add d-flex and flex-column -->
                <div class="card-header">
                    <h3 class="card-title">Chapter <?= $model->chapter_number ?>: <?= $model->name ?></h3>
                </div>
                <div class="card-body flex-grow-1"> <!-- Add flex-grow-1 -->
                    <p class="card-text"><?= nl2br($model->description ?? '') ?></p>
                    <br>
                    <?php foreach ($model->missions as $mission): ?>
                        <p><a href="<?= Url::toRoute(['mission/view', 'id' => $mission->id]) ?>"><?= $mission->name ?></a></p>
                    <?php endforeach; ?>
                </div>
                <div class="card-footer">
                    <?=
    Button::widget([
        'url' => Url::toRoute(['chapter/create', 'storyId' => $model->story_id]),
        'icon' => 'dnd-scroll',
        'title' => 'Add another chapter',
        'isCta' => true,
    ])
?>
                    <?=
    Button::widget([
        'url' => Url::toRoute(['mission/create', 'chapterId' => $model->id]),
        'icon' => 'dnd-badge',
        'title' => 'Add a mission to this chapter',
        'isCta' => true,
    ])
?>
                </div>
            </div>
        </div>
    </div>
</div>
