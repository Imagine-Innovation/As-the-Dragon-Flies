<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Mission $model */
$this->title = 'Update Mission: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Missions', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="mission-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
    $this->render('snippets/_form', [
        'model' => $model,
    ])
    ?>

</div>
