<?php

use frontend\widgets\Button;
use frontend\widgets\ModalDesc;

/** @var yii\web\View $this */
/** @var common\models\PlayerCart $playerCart */
$item = $playerCart->item;
?>
<div class="card mb-3">
    <div class="card-body">
        <div class="actions">
            <?=
            Button::widget([
                'mode' => 'icon',
                'id' => "cartButton-add-{$playerCart->item_id}",
                'icon' => 'bi-cart-plus',
                'tooltip' => "Add a {$item->name} to cart"
            ])
            ?>
            <?=
            Button::widget([
                'mode' => 'icon',
                'id' => "cartButton-remove-{$playerCart->item_id}",
                'icon' => 'bi-cart-dash',
                'tooltip' => "Remove a {$item->name} from cart"
            ])
            ?>
            <?=
            Button::widget([
                'mode' => 'icon',
                'id' => "cartButton-delete-{$playerCart->item_id}",
                'icon' => 'bi-trash3',
                'tooltip' => "Delete every {$item->name} from cart"
            ])
            ?>
            <?=
            Button::widget([
                'mode' => 'icon',
                'icon' => 'bi-cart',
                'tooltip' => "{$item->name} in your cart"
            ])
            ?>
            <a href="#" class="invisible" id="somethingWrongModal-hiddenButton" data-bs-toggle="modal" data-bs-target="#somethingWrongModal"></a>
            <div style="font-size: 12.35px">
                <span id="cartCount-<?= $playerCart->item_id ?>" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $playerCart->quantity ?></span>
            </div>
        </div>
        <img src="img/item/<?= $item->image ?>" class="image-thumbnail float-start" style="width: 80px;height: 80px;">
        <h6 class="card-subtitle">
            <?= $item->name ?><?= ($item->quantity > 1) ? "(x{$item->quantity})" : "" ?>(<?= $item->price ?>)
        </h6>
        <h6 class="card-subtitle text-muted w-75">
            <?=
            ModalDesc::widget([
                'name' => $item->name,
                'description' => $item->description,
                'maxLength' => 180,
                'type' => $item->itemType->name,
                'id' => $playerCart->item_id,
            ])
            ?>
        </h6>
    </div>
</div>
