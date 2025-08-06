<?php

use yii\helpers\Html;
use frontend\widgets\AjaxContainer;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'User Logins';
$this->params['breadcrumbs'][] = $this->title;
?>
<h3><?= Html::encode($this->title) ?></h3>
<?= AjaxContainer::widget(['name' => 'ajaxContainer']) ?>

<?=
$this->renderFile('@app/views/layouts/snippets/ajax-params.php', [
    'route' => 'user-login/ajax',
])
?>
