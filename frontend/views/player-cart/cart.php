<?php

use yii\helpers\Url;
use frontend\components\Shopping;
use frontend\widgets\Button;
use frontend\widgets\ModalDesc;

/** @var yii\web\View $this */
/** @var common\models\PlayerCart[] $models */
$this->title = 'Cart';
$this->params['breadcrumbs'][] = ['label' => 'Shop', 'url' => ['shop']];
$this->params['breadcrumbs'][] = $this->title;

$shopping = new Shopping();
?>
<div class="container g-0 p-0">
    <p id="purseContent"></p>
    <div class="row g-2">
        <div class="col-9">
            <?php if ($models): ?>
                <?php
                foreach ($models as $model):
                    $item = $model->item;
                    ?>
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="actions">
                                <?=
                                Button::widget([
                                    'mode' => 'icon',
                                    'id' => "cartButton-add-{$model->item_id}",
                                    'icon' => 'bi-cart-plus',
                                    'tooltip' => "Add a {$item->name} to cart"
                                ])
                                ?>
                                <?=
                                Button::widget([
                                    'mode' => 'icon',
                                    'id' => "cartButton-remove-{$model->item_id}",
                                    'icon' => 'bi-cart-dash',
                                    'tooltip' => "Remove a {$item->name} from cart"
                                ])
                                ?>
                                <?=
                                Button::widget([
                                    'mode' => 'icon',
                                    'id' => "cartButton-delete-{$model->item_id}",
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
                                    <span id="cartCount-<?= $model->item_id ?>" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $model->quantity ?></span>
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
                                    'id' => $model->item_id,
                                ])
                                ?>
                            </h6>
                        </div>
                    </div>
                <?php endforeach; ?>
<?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Your cart is empty</h5>
                        <h6 class="card-subtitle">Let's make some shopping
                            <a class="" href="<?= Url::toRoute('player-cart/shop') ?>">
                                <i class="bi bi-cart"></i>
                            </a>

                        </h6>
                    </div>
                </div>
<?php endif; ?>
        </div>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <div class="actions">
                        <a href="<?= Url::toRoute('player-cart/shop') ?>" role="button" class="actions__item bi bi-shop"></a>
                    </div>
                    <h4 class="card-title">Your cart</h4>
                    <h6 class="card-subtitle text-muted">
                        <span id="cartDisplay"></span>
                        <span class="invisible" id="cartItemCount"></span>
                    </h6>
                    <h6 class="card-subtitle">
                        <span class="text-muted">Total cart value: </span><span id="cartValueString"></span>
                    </h6>
                    <button class="btn btn-theme-dark btn--icon-text bi-cart-check" onclick="ShopManager.validateCart(); return false;"> Validate</button>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="somethingWrongModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title">Sorry, you can't buy this item</h6>
            </div>
            <div class="modal-body">
                <p class="text-muted" id="noFundLabel"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-theme btn--icon" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        </div>
    </div>
</div>
