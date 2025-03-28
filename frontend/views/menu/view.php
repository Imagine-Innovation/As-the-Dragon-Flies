<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/** @var yii\web\View $this */
/** @var common\models\Menu $model */

$this->title = $model->access_right_id;
$this->params['breadcrumbs'][] = ['label' => 'Menus', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="menu-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'access_right_id' => $model->access_right_id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'access_right_id' => $model->access_right_id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'access_right_id',
            'label',
            'icon',
            'tooltip',
            'card_title',
            'subtitle',
            'description:ntext',
            'button_label',
            'image',
            'is_context',
            'sort_order',
        ],
    ]) ?>

</div>
