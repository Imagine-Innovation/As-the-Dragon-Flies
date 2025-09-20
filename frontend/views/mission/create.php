<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Mission $model */
$this->title = 'Create Mission';
$this->params['breadcrumbs'][] = ['label' => 'Missions', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="mission-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
    $this->render('snippets/_form', [
        'model' => $model,
    ])
    ?>

</div>
