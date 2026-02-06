<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Story $model */
$this->title = 'Update Story: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Stories', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="story-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
    $this->render('snippets/_form', [
        'model' => $model,
    ])
?>

</div>
