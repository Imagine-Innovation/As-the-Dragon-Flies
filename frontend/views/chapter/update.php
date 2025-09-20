<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Chapter $model */
$this->title = 'Update Chapter: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Chapters', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="chapter-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
    $this->render('snippets/_form', [
        'model' => $model,
    ])
    ?>

</div>
