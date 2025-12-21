<?php

use common\helpers\Utilities;

/** @var yii\web\View $this */
/** @var frontend\models\PlayerBuilder $model */
$endowmentTable = $model->getInitialEndowment();
$choices = max(array_keys($endowmentTable));

$backgroundItems = $model->background->backgroundItems;

$backgroundName = $model->background->name;
$className = $model->class->name;

$paragraphs = [
    "Your class '{$className}' and your background '{$backgroundName}' offers you the following options for building your starting equipment",
    "Select one option on each line:"
];

$items = [];
$category = "";
foreach ($backgroundItems as $backgroundItem) {
    if ($backgroundItem->item_id) {
        $items[] = "{$backgroundItem->item_id}|{$backgroundItem->quantity}";
    } else {
        // Side effect: one background has 0 or 1 item category
        $category = "{$backgroundItem->category_id}|{$backgroundItem->quantity}";
    }
}
?>
<!-- Character Builder - Equipment Tab -->
<?= Utilities::formatMultiLine($paragraphs) ?>
<div class="d-none">
    Hidden div to embeb utility tags for PHP/JS communication
    <span id="hiddenCategory"><?= $category ?></span>
</div>

<div class="container-fluid">
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body">
                    <h4 class="card-title text-decoration">Your background <?= $backgroundName ?> gives you:</h4>
                    <p><?= $model->background->initial_equipment ?>&nbsp;
                        <span onclick="PlayerBuilder.chooseBackgroundEquipment('<?= $category ?>');">
                            <i class="bi bi-info-circle"></i>
                        </span>
                    </p>
                    <p>&nbsp;</p>
                    <h4 class="card-title text-decoration">Your class <?= $className ?> gives you!</h4>
                    <div id="playerBuilderEndowment">
                        <p>Nothing</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body" id="ajaxSelectedItems">
                    <h4 class="card-title text-decoration">Items</h4>
                    <div id="ajaxItemImages"></div>
                    <div class="d-noness">
                        Hidden div to embeb utility tags for PHP/JS communication
                        <span id="ajaxItemChoice-background"><?= implode(',', $items) ?></span>
                        <?php for ($i = 1; $i <= $choices; $i++): ?>
                            <span id="ajaxItemChoice-<?= $i ?>"></span>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
