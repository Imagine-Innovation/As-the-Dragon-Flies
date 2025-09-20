<?php

use frontend\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Mission $model */
$this->title = $model->name;
$chapter = $model->chapter;
$story = $chapter->story;

$breadcrumbs = [
    ['label' => 'Stories', 'url' => ['story/index']],
    ['label' => $story->name, 'url' => ['story/view', 'id' => $story->id]],
    ['label' => $chapter->name, 'url' => ['chapter/view', 'id' => $chapter->id]],
    ['label' => $model->name],
];

// Set breadcrumbs for the view
$this->params['breadcrumbs'] = $breadcrumbs;

$components = [
    'npcs'
];

\yii\web\YiiAsset::register($this);
?>
<div class="container">
    <div class="card mb-3">
        <div class="actions">
            <?=
            Button::widget([
                'mode' => 'icon',
                'url' => Url::toRoute(['session/update', 'id' => $model->id]),
                'icon' => 'dnd-spell',
                'tooltip' => "Edit mission"
            ])
            ?>
        </div>
        <div class="row g-0 d-flex"> <!-- Add d-flex to the row -->
            <div class="col-md-4 d-flex align-items-stretch"> <!-- Add d-flex and align-items-stretch -->
                <img src="img/story/<?= $story->id ?>/<?= $model->image ?>" class="img-fluid object-fit-cover rounded-start w-100" alt="<?= $model->name ?>">
            </div>
            <div class="col-md-8 text-decoration d-flex flex-column"> <!-- Add d-flex and flex-column -->
                <div class="card-header">
                    <h3 class="card-title"><?= $model->name ?></h3>
                </div>
                <div class="card-body flex-grow-1"> <!-- Add flex-grow-1 -->
                    <p class="card-text"><?= nl2br($model->description) ?></p>
                    <br>
                </div>
            </div>
        </div>
    </div>
</div>
