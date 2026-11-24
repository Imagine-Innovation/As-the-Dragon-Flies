<?php

use common\widgets\AjaxContainer;
use common\widgets\TableFilter;
use common\components\AccessRightsManager;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Access rights';
$this->params['breadcrumbs'][] = $this->title;
?>
<h3><?= Html::encode($this->title) ?></h3>

<?=
    TableFilter::widget([
        'filterArray' => [
            AccessRightsManager::APP_BACKEND => 'Backend',
            AccessRightsManager::APP_FRONTEND => 'Frontend',
        ],
        'multipleValues' => true,
        'searchString' => true,
        'onLoad' => 'AccessRightManager.loadGenericAjaxTable(0, "application", "routeSearch");',
    ])
?>

<?= AjaxContainer::widget(['name' => 'ajaxContainer']) ?>

<?=
    $this->renderFile('@app/views/layouts/snippets/ajax-params.php', [
        'route' => 'access-right/ajax',
    ])
?>
