<?php
/** @var \yii\web\View $this */
/** @var int $limit */
/** @var string $route */
/** @var string $initTab */
/** @var string|null $initId */
/** @var string|null $filter */
/** @var int|null $limit */
$initTab = $initTab ?? '';
$filter = $filter ?? '';
$limit = $limit ?? 10;
$initId = $initId ?? '';
$functionCall = $functionCall ?? null;
?>
<?php use yii\helpers\Html; ?>
<div class="d-none" id="ajaxHiddenParams">
    <span id="limit"><?= (int) $limit ?></span>
    <span id="route"><?= Html::encode($route) ?></span>
    <span id="container"><?= $initTab ? "ajax-" . Html::encode((string) $initTab) : '' ?></span>
    <span id="currentTab"><?= Html::encode((string) $initTab) ?></span>
    <span id="currentId"><?= Html::encode((string) $initId) ?></span>
    <span id="filter"><?= Html::encode((string) $filter) ?></span>
</div>
