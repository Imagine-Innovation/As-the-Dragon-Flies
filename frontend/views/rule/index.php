<?php

use frontend\widgets\AjaxContainer;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Rules';
$this->params['breadcrumbs'][] = $this->title;
?>
<h3><?= Html::encode($this->title) ?></h3>
<?= AjaxContainer::widget(['name' => 'ajaxContainer']) ?>

<?=
$this->renderFile('@app/views/layouts/snippets/ajax-params.php', [
    'route' => 'rule/ajax',
])
?>
