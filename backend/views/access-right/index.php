<?php

use common\components\AccessRightsManager;
use common\widgets\AjaxContainer;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
$this->title = 'Access rights';
$this->params['breadcrumbs'][] = $this->title;

$applications = [
    AccessRightsManager::APP_BACKEND => 'Backend',
    AccessRightsManager::APP_FRONTEND => 'Frontend',
];
?>
<h3><?= Html::encode($this->title) ?></h3>

<div class="d-flex align-items-center flex-wrap gap-3 mb-2">
    <div>
        <?php foreach ($applications as $application => $label): ?>
            <div class="custom-control custom-checkbox custom-control-inline mb-2">
                <input type="checkbox" id="application-<?= Html::encode($application) ?>"
                       name="application" value="<?= Html::encode($application) ?>"
                       class="custom-control-input"
                       onchange='AccessRightManager.loadGenericAjaxTable(0);'>
                <label class="custom-control-label"
                       for="application-<?= Html::encode($application) ?>"><?= $label ?></label>
            </div>
<?php endforeach; ?>
    </div>
    <div class="input-group ms-auto" style="max-width: 20rem;">
        <input type="text" id="routeSearch" name="routeSearch" class="form-control"
               placeholder="Search route"
               onkeydown='if (event.key === "Enter") { event.preventDefault(); AccessRightManager.loadGenericAjaxTable(0); }'>
        <button type="button" class="btn btn-outline-secondary"
                onclick='AccessRightManager.loadGenericAjaxTable(0);'>
            <i class="bi bi-search"></i>
        </button>
    </div>
</div>

<?= AjaxContainer::widget(['name' => 'ajaxContainer']) ?>

<?=
    $this->renderFile('@app/views/layouts/snippets/ajax-params.php', [
        'route' => 'access-right/ajax',
    ])
?>
