<?php

use common\models\Race;
use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Races';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="race-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Race', ['create'], ['class' => 'btn btn-success']) ?>
    </p>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'race_group_id',
            'name',
            'description:ntext',
            'adult_age',
            //'lifespan',
            //'size',
            //'base_height',
            //'height_modifier',
            //'base_weight',
            //'weight_modifier',
            //'speed',
            //'darkvision',
            [
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, Race $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'id' => $model->id]);
                 }
            ],
        ],
    ]); ?>


</div>
