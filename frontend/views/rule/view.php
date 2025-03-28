<?php

use common\helpers\Utilities;
use common\helpers\Status;
use frontend\widgets\RuleParsingTree;
use frontend\widgets\ActionButtons;

/** @var yii\web\View $this */
/** @var common\models\Rule $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Rules', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

\yii\web\YiiAsset::register($this);
?>
<h4 class="card-title"><?= Utilities::encode($this->title) ?></h4>
<div class="row g-4">
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5>Rule definition:</h5>
                <?= ActionButtons::widget(['model' => $model]) ?>
                <p class="text-mutted"><?= Utilities::encode($model->definition) ?></p>
                <h5>Status: <span class="h6 text-mutted"><?= Status::label($model->status) ?></span></h5>
                <p><?= Utilities::encode($model->description) ?></p>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5>Rule parsing:</h5>
                <?= RuleParsingTree::widget(['id' => $model->id]) ?>
            </div>
        </div>
    </div>
</div>

