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
<div class="d-none" id="ajaxHiddenParams">
    <span id="limit"><?= $limit ?></span>
    <span id="route"><?= $route ?></span>
    <span id="container"><?= $initTab ? 'ajax-' . $initTab : '' ?></span>
    <span id="currentTab"><?= $initTab ?></span>
    <span id="currentId"><?= $initId ?></span>
    <span id="filter"><?= $filter ?></span>
</div>
