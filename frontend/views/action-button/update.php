<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\ActionButton $model */

$this->title = 'Update Action Button: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Action Buttons', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="action-button-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
