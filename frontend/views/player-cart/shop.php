<?php

use yii\helpers\Url;
use frontend\components\Shopping;
use frontend\widgets\CurrentPlayer;
use frontend\widgets\IconButton;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var common\models\Item[] $models */
/** @var number|null $player_id */
$this->title = 'Shop';
$this->params['breadcrumbs'][] = $this->title;

$shopping = new Shopping();

$itemTypes = $shopping->itemTypes;
$shopData = $shopping->loadShopData($models);

$user = Yii::$app->session->get('user');
$currentPlayer = Yii::$app->session->get('currentPlayer');

$active = $user->hasPlayers();

$firstType = $itemTypes[0];
?>
<div class="container g-0 p-0">
    <div class="card">
        <div class="card-body">
            <p id="purseContent"></p>
            <div class="actions">
                <?=
                IconButton::widget([
                    'url' => Url::toRoute(['player-cart/cart']),
                    'icon' => 'bi-cart',
                    'tooltip' => "Go to your cart"
                ])
                ?>
                <div style="font-size: 12.35px">
                    <span id="cartItemCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"></span>
                </div>
                <a href="#" class="invisible" id="somethingWrongModal-hiddenButton" data-bs-toggle="modal" data-bs-target="#somethingWrongModal"></a>
            </div>

            <div class="tab-container">
                <ul class="nav nav-tabs" role="tablist">
                    <?php foreach ($itemTypes as $itemType): ?>
                        <li class="nav-item">
                            <a class="nav-link<?= $itemType == $firstType ? " active" : "" ?>"
                               data-bs-toggle="tab" href="#tab-<?= $itemType ?>" role="tab" href="#">
                                   <?= $itemType ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="tab-content">
                    <?php foreach ($itemTypes as $itemType): ?>
                        <div class="tab-pane <?= $itemType == $firstType ? "active fade show" : "fade" ?>"
                             id="tab-<?= $itemType ?>" role="tabpanel">
                                 <?=
                                 $this->render('snippets/shop-items', [
                                     'shopData' => $shopData[$itemType],
                                     'player' => $currentPlayer,
                                     'active' => $active,
                                 ]);
                                 ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?=
    CurrentPlayer::widget([
        'user' => $user,
        'mode' => 'modal',
    ])
    ?>
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
</div>
