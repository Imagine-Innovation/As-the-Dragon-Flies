<?php

use frontend\widgets\Pagination;
use frontend\widgets\RecordCount;
use frontend\widgets\ItemTable;

/** @var yii\web\View $this */
/** @var common\models\Item $models */
/** @var char $itemType: Name of the item type */
/** @var int $count: total number of records retrived by the query */
/** @var int $page: current page number */
/** @var int $pageCount: nomber of pages regarding the limit of the query */
/** @var int $limit: nomber of records to be fetched */
?>
<div class="card">
    <div class="card-body">
        <?= RecordCount::widget(['count' => $count, 'model' => $itemType, 'adjective' => 'available']) ?>
        <?= ItemTable::widget(['items' => $models, 'itemType' => $itemType]) ?>
        <?= Pagination::widget(['page' => $page, 'pageCount' => $pageCount, 'limit' => $limit,]) ?>
    </div>
</div>
