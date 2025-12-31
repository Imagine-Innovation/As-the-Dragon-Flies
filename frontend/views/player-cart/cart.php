<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\PlayerCart[] $playerCarts */
$this->title = 'Cart';
$this->params['breadcrumbs'][] = ['label' => 'Shop', 'url' => ['shop']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container g-0 p-0">
    <p id="purseContent"></p>
    <div class="row g-2">
        <div class="col-9">
            <?php if ($playerCarts): ?>
                <?php foreach ($playerCarts as $playerCart): ?>
                    <?= $this->render('snippets/cart', ['playerCart' => $playerCart]) ?>
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
