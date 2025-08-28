<?php

use common\models\PlayerItem;
use frontend\widgets\Button;

/** @var yii\web\View $this */
/** @var common\models\PlayerBody $playerBody */
$playerItems = [];
$itemTypes = ['Armor', 'Helmet', 'Shield', 'Weapon', 'Tool'];

if ($playerBody) {
    foreach (PlayerItem::BODY_ZONE as $property => $zone) {
        $playerItem = $playerBody->$property;
        if ($playerItem) {
            $item = $playerItem->item;
            $playerItems[$playerItem->item_type][] = [
                'name' => $item->name,
                'image' => $item->image,
                'quantity' => $playerItem->quantity,
            ];
        }
    }
}
$lastItemType = "none";
?>
<!-- Equipement -->
<div class="actions">
    <?=
    Button::widget([
        'id' => 'showEquipmentModal-Button',
        'mode' => 'icon',
        'icon' => 'dnd-equipment',
        'tooltip' => "Player's equipement",
        'modal' => 'equipmentModal'
    ])
    ?>
</div>
<div class="m-3">
    <h6 class="text-warning">Equipment</h6>

    <?php if ($playerBody): ?>
        <?php foreach ($itemTypes as $itemType): ?>
            <?php if (array_key_exists($itemType, $playerItems) && (1 === 1) && !empty($playerItems[$itemType])): ?>
                <?php
                if ($lastItemType !== $itemType):
                    $lastItemType = $itemType;
                    ?>
                    <p><?= $itemType ?></p>
                <?php endif; ?>

                <?php foreach ($playerItems[$itemType] as $item): ?>
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <img src="img/item/<?= $item['image'] ?>" class="image-thumbnail me-2" style="width: 50px;height: 50px;">
                            <?= $item['name'] ?> <?= $item['quantity'] > 1 ? "(x{$item['quantity']})" : "" ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="m-3">This player is not equipped yet</p>
    <?php endif; ?>
</div>
