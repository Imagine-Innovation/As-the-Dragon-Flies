<?php

use frontend\widgets\ModalDesc;

/** @var yii\web\View $this */
/** @var array $items */
/** @var common\models\Player $player */
?>
<div class="container">
    <div class="row g-4">
        <?php foreach ($items as $item): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
                    <?php if ($item['image']): ?>
                        <img class="card-img-top" src="img/item/<?= $item['image'] ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="actions">
                            <div class="form-group">
                                <div class="toggle-switch">
                                    <span data-bs-toggle="tooltip" title="Add or remove from pack" data-placement="bottom">
                                        <input type="checkbox" class="toggle-switch__checkbox" id="pack-<?= $item['id'] ?>" <?= $item['is_carrying'] ? 'checked' : '' ?>
                                               onchange="ItemManager.toggle_pack(<?= $item['id'] ?>);">
                                        <i class="toggle-switch__helper"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <h4 class="card-title text-center">
                            <?= $item['quantity'] ?> x <?= $item['name'] ?>
                            <?php if (($player) && ($player->isProficient($item['id']))): ?>
                                <span data-bs-toggle="tooltip" title="You have proficiency with this item" data-placement="bottom">
                                    <i class="bi bi-star-fill"></i>
                                </span>
                            <?php endif; ?>
                        </h4>
                        <h6 class="card-subtitle text-muted">
                            <?=
                            ModalDesc::widget([
                                'name' => $item['name'],
                                'description' => $item['description'],
                                'maxLength' => 180,
                                'type' => $item['type'],
                                'id' => $item['id'],
                            ])
                            ?>
                        </h6>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
