<?php

use common\models\ItemType;
use yii\helpers\Html;
use frontend\widgets\AjaxContainer;

/** @var yii\web\View $this */
/** @var int $count: total number of records retrived by the query */
/** @var int $page: current page number */
/** @var int $pageCount: nomber of pages regarding the limit of the query */
/** @var int $limit: nomber of records to be fetched */
/** @var boolean $partial: indicates that the first column should not be displayed */
$this->title = 'Items';
$this->params['breadcrumbs'][] = $this->title;

$itemTypes = ItemType::find()
        ->orderBy('sort_order')
        ->all();

//$tabs = ['Armor', 'Weapon', 'Tool', 'Gear', 'Pack', 'Poison'];

$firstTypeId = $itemTypes[0]->id;
?>
<script src="js/atdf-item-manager.js"></script>

<h3><?= Html::encode($this->title) ?></h3>
<div class="card">
    <div class="card-body">
        <div class="tab-container">
            <ul class="nav nav-tabs" role="tablist">
                <?php foreach ($itemTypes as $itemType): ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $itemType->id == $firstTypeId ? " active" : "" ?>"
                           data-bs-toggle="tab" href="#tab-<?= $itemType->id ?>" role="tab"
                           href="#" onclick="ItemManager.loadTypeTab('<?= $itemType->id ?>');">
                               <?= $itemType->name ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content">
                <?php foreach ($itemTypes as $itemType): ?>
                    <div class="tab-pane <?= $itemType->id == $firstTypeId ? "active fade show" : "fade" ?>"
                         id="tab-<?= $itemType->id ?>" role="tabpanel">
                             <?= AjaxContainer::widget(['name' => 'ajax-' . $itemType->id]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?=
$this->renderFile('@app/views/layouts/_ajax.php', [
    'route' => 'item/ajax', // default route
    'initTab' => $firstTypeId,
])
?>
