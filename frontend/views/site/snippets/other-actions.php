<?php

use common\models\Player;
use frontend\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Player $player  */
?>

<h4 class="text-decoration text-yellow">Equip <?= $player->name ?> for the next adventure and relive previous ones</h4>
<section id="level2" class="text-decoration">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
        <!-- Pick up items -->
        <div class="col">
            <div class="card h-100">
                <img src="<?= Url::to('@web/img/sm/belongings.png') ?>" class="card-img-top" alt="Pick up items">
                <div class="card-body">
                    <h4 class="card-title text-yellow">Pick up items</h4>
                    <p>Here's everything your player owns.</p>
                    <p>It's up to you to choose what to take with you on your next adventure.</p>
                    <?=
    Button::widget([
        'url' => Url::toRoute(['player-item/index']),
        'icon' => 'dnd-chest',
        'style' => 'btn-primary justify-content-center mt-auto',
        'tooltip' => null,
        'title' => 'Equip',
        'isCta' => false,
    ])
;
?>
                </div>
            </div>
        </div>
        <!-- Shopping -->
        <div class="col">
            <div class="card h-100">
                <img src="<?= Url::to('@web/img/sm/items.png') ?>" class="card-img-top" alt="Shopping">
                <div class="card-body">
                    <h4 class="card-title text-yellow">Go shopping</h4>
                    <p>A shop were you can buy armors, weapon and other stuff</p>
                    <?=
    Button::widget([
        'url' => Url::toRoute(['player-cart/shop']),
        'icon' => 'bi-shop',
        'style' => 'btn-primary justify-content-center mt-auto',
        'tooltip' => null,
        'title' => 'Visit the shop',
        'isCta' => false,
    ])
;
?>
                </div>
            </div>
        </div>
        <!-- Quest history -->
        <div class="col">
            <div class="card h-100">
                <img src="<?= Url::to('@web/img/sm/8.jpg') ?>" class="card-img-top" alt="Quest history">
                <div class="card-body">
                    <h4 class="card-title text-yellow">Quest history</h4>
                    <p>Relive the best moments of your past quests</p>
                    <?=
    Button::widget([
        'url' => Url::toRoute(['player/history']),
        'icon' => 'dnd-spell-book',
        'style' => 'btn-primary justify-content-center mt-auto',
        'tooltip' => null,
        'title' => 'View history',
        'isCta' => false,
    ])
;
?>
                </div>
            </div>
        </div>
    </div>
</section>
