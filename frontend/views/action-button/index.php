<?php

use common\models\ActionButton;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Action Buttons';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="action-button-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Action Button', ['create'], ['class' => 'btn btn-success']) ?>
    </p>


    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'id',
            'route',
            'action',
            'icon',
            'tooltip',
            //'in_table',
            //'in_view',
            //'sort_order',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, ActionButton $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                }
            ],
        ],
    ]);
    ?>


</div>
