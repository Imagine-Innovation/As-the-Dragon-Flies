<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\AccessRight $model */

$this->title = 'Create Access Right';
$this->params['breadcrumbs'][] = ['label' => 'Access Rights', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="access-right-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?=
    $this->render('_form', [
        'model' => $model,
    ])
?>

</div>
