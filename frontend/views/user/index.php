<?php

use frontend\widgets\AjaxContainer;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Users';
$this->params['breadcrumbs'][] = $this->title;
?>
<h3><?= Html::encode($this->title) ?></h3>
<div class="container-fluid">
    <?= AjaxContainer::widget(['name' => 'ajaxContainer']) ?>
    <?=
    $this->renderFile('@app/views/layouts/snippets/ajax-params.php', [
        'route' => 'user/ajax',
    ])
    ?>
</div>
