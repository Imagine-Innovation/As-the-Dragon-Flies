<?php

use frontend\components\Inventory;
use frontend\widgets\IconButton;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\PlayerItem[] $models */
//$user = Yii::$app->user->identity;
//$currentPlayer = $user->currentPlayer;
$user = Yii::$app->session->get('user');
$currentPlayer = Yii::$app->session->get('currentPlayer');

$this->title = 'Possessions';
$this->params['breadcrumbs'][] = ['label' => $currentPlayer->name, 'url' => ['player/view', 'id' => $currentPlayer->id]];
$this->params['breadcrumbs'][] = $this->title;

$inventory = new Inventory();
$playerItems = $inventory->loadItemsData($models);
$itemTypes = $inventory->getItemTypes($models);
$firstType = $itemTypes[0] ?? "";
?>
<div class="container g-0 p-0">
    <div class="card">
        <div class="card-body">
            <p id="purseContent"></p>
            <div class="actions">
                <?=
                IconButton::widget([
                    'url' => Url::toRoute(['player-item/index']),
                    'icon' => 'bi-backpack2',
                    'tooltip' => "See what you are carrying"
                ])
                ?>
                <div style="font-size: 12.35px">
                    <span id="cartItemCount" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"></span>
                </div>
            </div>

            <?php if ($itemTypes): ?>
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
                                     $this->render('snippets\inventory', [
                                         'items' => $playerItems[$itemType],
                                         'player' => $currentPlayer,
                                     ]);
                                     ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <h5>Player <?= $currentPlayer->name ?>'s pack is empty</h5>
            <?php endif; ?>
        </div>
    </div>
</div>
