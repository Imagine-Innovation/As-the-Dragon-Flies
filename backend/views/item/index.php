<?php

use common\widgets\AjaxContainer;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var \common\models\ItemType[] $itemTypes */
$this->title = 'Items';
$this->params['breadcrumbs'][] = $this->title;

$firstTypeId = $itemTypes[0]->id;
?>
<h3><?= Html::encode($this->title) ?></h3>
<div class="card">
    <div class="card-body">
        <div class="tab-container">
            <ul class="nav nav-tabs" role="tablist">
                <?php foreach ($itemTypes as $itemType): ?>
                    <li class="nav-item">
                        <a class="nav-link<?= $itemType->id === $firstTypeId ? ' active' : '' ?>"
                           data-bs-toggle="tab" href="#tab-<?= $itemType->id ?>" role="tab"
                           href="#" onclick="ItemManager.loadTypeTab('<?= $itemType->id ?>', '<?= $itemType->name ?>');">
                               <?= $itemType->name ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="tab-content">
                <?php foreach ($itemTypes as $itemType): ?>
                    <div class="tab-pane <?= $itemType->id === $firstTypeId ? 'active fade show' : 'fade' ?>"
                         id="tab-<?= $itemType->id ?>" role="tabpanel">
                             <?= AjaxContainer::widget(['name' => 'ajax-' . $itemType->id]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
<?=
$this->renderFile('@app/views/layouts/snippets/ajax-params.php', [
    'route' => 'item/ajax', // default route
    'initTab' => $firstTypeId,
])
?>
