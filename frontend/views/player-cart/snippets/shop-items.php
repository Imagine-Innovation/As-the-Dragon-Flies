<?php

use frontend\widgets\Button;
use frontend\widgets\ModalDesc;

/** @var yii\web\View $this */
/** @var array $shopData */
/** @var common\models\Player $player */
/** @var boolean $active */
?>
<div class="container">
    <div class="row g-4">
        <?php foreach ($shopData as $item): ?>
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                <div class="card h-100">
                    <?php if ($item['image']): ?>
                        <img class="card-img-top" src="img/item/<?= $item['image'] ?>">
                    <?php endif; ?>
                    <div class="card-body">
                        <div class="actions">
                            <?=
                            Button::widget([
                                'mode' => 'icon',
                                'id' => "cartButton-add-{$item['id']}",
                                'icon' => 'bi-cart-plus h2',
                                'tooltip' => "Add a {$item['name']} to cart"
                            ])
                            ?>
                        </div>
                        <h4 class="card-title text-center">
                            <?= $item['name'] ?>
                            <?php if ($item['quantity'] > 1): ?>
                                (x<?= $item['quantity'] ?>)
                            <?php endif; ?>
                            <?php if ($player->isProficient($item['id'])): ?>
                                <span data-bs-toggle="tooltip" title="You have proficiency with this item" data-placement="bottom">
                                    <i class="bi bi-star-fill"></i>
                                </span>
                            <?php endif; ?>
                        </h4>
                        <h4 class="card-subtitle text-center"><?= $item['price'] ?></h4>
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
                    <?php if ($active): ?>
                        <div class="card-footer">
                            <a class="card-link" href="#" onclick="ShopManager.addToCart(<?= $item['id'] ?>);">
                                <i class="bi bi-cart-plus"></i>
                                <small>Add to cart</small>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
