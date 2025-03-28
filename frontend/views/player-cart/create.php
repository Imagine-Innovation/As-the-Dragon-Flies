<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\PlayerCart $model */

$this->title = 'Create Player Cart';
$this->params['breadcrumbs'][] = ['label' => 'Player Carts', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="player-cart-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
