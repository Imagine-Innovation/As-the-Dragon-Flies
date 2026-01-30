<?php

use frontend\widgets\ItemTable;
use frontend\widgets\Pagination;
use frontend\widgets\RecordCount;

/** @var yii\web\View $this */
/** @var common\models\Item $models */
/** @var int $itemTypeId internal id of the item type */
/** @var int $count total number of records retrived by the query */
/** @var int $page current page number */
/** @var int $pageCount number of pages regarding the limit of the query */
/** @var int $limit number of records to be fetched */
?>
<div class="card">
    <div class="card-body">
        <?= RecordCount::widget(['count' => $count, 'model' => $itemTypeId, 'adjective' => 'available']) ?>
        <?= ItemTable::widget(['items' => $models, 'itemTypeId' => $itemTypeId]) ?>
        <?= Pagination::widget(['page' => $page, 'pageCount' => $pageCount, 'limit' => $limit,]) ?>
    </div>
</div>
