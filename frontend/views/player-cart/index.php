<?php

use common\models\PlayerCart;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use yii\grid\GridView;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Player Carts';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="player-cart-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Player Cart', ['create'], ['class' => 'btn btn-success']) ?>
    </p>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'player_id',
            'item_id',
            'quantity',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, PlayerCart $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'playerId' => $model->player_id, 'itemId' => $model->item_id]);
                 }
            ],
        ],
    ]); ?>


</div>
