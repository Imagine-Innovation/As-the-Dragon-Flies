<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\PlayerCart $model */

$this->title = 'Update Player Cart: ' . $model->player_id;
$this->params['breadcrumbs'][] = ['label' => 'Player Carts', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->player_id, 'url' => ['view', 'player_id' => $model->player_id, 'item_id' => $model->item_id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="player-cart-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
