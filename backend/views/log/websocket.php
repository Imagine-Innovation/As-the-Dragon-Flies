<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\data\ArrayDataProvider;

$this->title = 'WebSocket Logs';
$this->params['breadcrumbs'][] = $this->title;

$dataProvider = new ArrayDataProvider([
    'allModels' => $logs,
    'pagination' => [
        'pageSize' => 50,
    ],
        ]);
?>

<div class="log-websocket">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?=
        Html::a('Clear Logs', ['clear-websocket'], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to clear all WebSocket logs?',
                'method' => 'post',
            ],
        ])
        ?>
    </p>

    <?=
    GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            [
                'attribute' => 'time',
                'label' => 'Time',
                'value' => function ($model) {
                    return $model['time'] ?? '';
                },
            ],
            [
                'attribute' => 'category',
                'label' => 'Category',
                'value' => function ($model) {
                    return $model['category'] ?? '';
                },
            ],
            [
                'attribute' => 'message',
                'label' => 'Message',
                'format' => 'raw',
                'value' => function ($model) {
                    return Html::encode($model['message'] ?? '');
                },
            ],
        ],
    ]);
    ?>
</div>
