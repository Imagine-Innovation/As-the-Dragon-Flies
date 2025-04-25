<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Race $model */
$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Races', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div>

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?=
        Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ])
        ?>
    </p>

    <?=
    DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'race_group_id',
            'name',
            'description:ntext',
            'adult_age',
            'lifespan',
            'size',
            'base_height',
            'height_modifier',
            'base_weight',
            'weight_modifier',
            'speed',
            'darkvision',
        ],
    ])
    ?>

</div>
