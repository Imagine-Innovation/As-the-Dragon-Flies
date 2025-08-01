<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\AccessRight $model */

$this->title = 'Update Access Right: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Access Rights', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="access-right-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
