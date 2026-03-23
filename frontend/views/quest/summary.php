<?php

use common\components\AppStatus;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Quest $model */

$this->title = 'Quest Summary: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Quests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$status = AppStatus::from($model->status);
?>
<div class="quest-summary">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Quest Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Story:</strong> <?= Html::encode($model->story->name) ?></p>
                    <p><strong>Status:</strong>
                        <span class="badge bg-<?= $model->status === AppStatus::COMPLETED->value ? 'success' : 'danger' ?>">
                            <?= Html::encode($status->getLabel()) ?>
                        </span>
                    </p>
                    <p><strong>Started at:</strong> <?= Yii::$app->formatter->asDatetime($model->started_at) ?></p>
                    <p><strong>Completed at:</strong> <?= Yii::$app->formatter->asDatetime($model->completed_at) ?></p>
                    <p><strong>Elapsed time:</strong> <?= $model->elapsed_time ?> minutes</p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Quest Description</h5>
                </div>
                <div class="card-body">
                    <?= $model->description ?>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">Participants</h5>
                </div>
                <ul class="list-group list-group-flush">
                    <?php foreach ($model->allPlayers as $player): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= Html::encode($player->name) ?>
                            <span class="badge bg-secondary"><?= Html::encode($player->characterClass->name ?? 'Adventurer') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="d-grid gap-2">
                <?= Html::a('Back to Stories', ['story/index'], ['class' => 'btn btn-primary']) ?>
                <?= Html::a('View All Quests', ['quest/index'], ['class' => 'btn btn-outline-secondary']) ?>
            </div>
        </div>
    </div>

</div>
