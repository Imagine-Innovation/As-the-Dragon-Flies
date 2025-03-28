<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\ActionButton $model */

$this->title = 'Create Action Button';
$this->params['breadcrumbs'][] = ['label' => 'Action Buttons', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="action-button-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
