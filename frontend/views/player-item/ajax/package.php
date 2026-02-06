<?php

use frontend\widgets\Button;

/** @var yii\web\View $this */
/** @var array<string, non-empty-list<array{itemId: int, name: string, image: string|null, quantity: int, isProficient: int, isTwoHanded: int, buttonId: non-falsy-string}>> $playerItems */
$itemTypes = ['Armor', 'Helmet', 'Shield', 'Weapon', 'Tool'];
?>
<div class="row">
    <?php foreach ($itemTypes as $itemType): ?>
        <?php if (array_key_exists($itemType, $playerItems)): ?>
            <div id="itemType-<?= $itemType ?>" class="card d-none">
                <?php foreach ($playerItems[$itemType] as $item): ?>
                    <article id="item-<?= $item['itemId'] ?>" class="col-12 p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <img src="img/item/<?= $item['image'] ?>" class="image-thumbnail me-2" style="width: 50px;height: 50px;">
                                <?= $item['name'] ?> <?= $item['quantity'] > 1 ? "(x{$item['quantity']})" : '' ?>
                            </div>
                            <?php

                            if ($itemType === 'Weapon') {
                                echo
                                    Button::widget([
                                        'icon' => $item['isTwoHanded'] ? 'dnd-action-fight' : 'dnd-weapon-sword',
                                        'tooltip' => $item['isTwoHanded']
                                            ? 'To use this weapon, you need both hands.'
                                            : 'You only need one hand to use this weapon',
                                        'title' => 'Equip',
                                        'id' => $item['buttonId'],
                                        'isCta' => true,
                                        'style' => 'btn-sm mt-2',
                                    ])
                                ;
                            } else {
                                echo
                                    Button::widget([
                                        'title' => 'Equip',
                                        'id' => $item['buttonId'],
                                        'isCta' => true,
                                        'style' => 'btn-sm mt-2',
                                    ])
                                ;
                            }
                            ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
