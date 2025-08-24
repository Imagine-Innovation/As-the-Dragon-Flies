<?php

use frontend\widgets\Button;

/** @var yii\web\View $this */
/** @var array $itemTypes */
/** @var array $playerItems */
?>
<div class="row">
    <?php foreach ($itemTypes as $key => $itemType): ?>
        <div id="itemType-<?= $itemType ?>" class="card d-none">
            <?php foreach ($playerItems[$itemType] as $item): ?>
                <article id="item-<?= $item['itemId'] ?>" class="col-12 p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="">
                                <img src="img/item/<?= $item['image'] ?>" class="image-thumbnail float-start" style="width: 50px;height: 50px;">
                                <?= $item['name'] ?>
                            </div>
                        </div>
                        <?php
                        if ($itemType === 'Weapon') {
                            echo Button::widget([
                                'icon' => ($item['isTwoHanded'] ? 'dnd-action-fight' : 'dnd-weapon-sword'),
                                'tooltip' => ($item['isTwoHanded'] ? 'To use this weapon, you need both hands.' : 'You only need one hand to use this weapon'),
                                'title' => 'Equip',
                                'id' => $item['buttonId'],
                                'callToAction' => true,
                                'style' => 'btn-sm mt-2',
                            ]);
                        } else {
                            echo Button::widget([
                                'title' => 'Equip',
                                'id' => $item['buttonId'],
                                'callToAction' => true,
                                'style' => 'btn-sm mt-2',
                            ]);
                        }
                        ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
</div>
