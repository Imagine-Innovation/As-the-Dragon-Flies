<?php

use common\helpers\WebResourcesHelper;
use common\models\Player;
use common\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Player $player  */
$imgPath = WebResourcesHelper::imagePath();
?>

<h4 class="text-decoration text-yellow"><?= Yii::t('app', 'Equip {name} for the next adventure and relive previous ones', ['name' => $player->name]) ?></h4>
<section id="level2" class="text-decoration">
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
        <!-- Pick up items -->
        <div class="col">
            <div class="card h-100">
                <img src="<?= Url::to($imgPath . '/sm/belongings.png') ?>" class="card-img-top" alt="<?= Yii::t('app', 'Pick up items') ?>">
                <div class="card-body">
                    <h4 class="card-title text-yellow"><?= Yii::t('app', 'Pick up items') ?></h4>
                    <p><?= Yii::t('app', 'Here\'s everything your player owns.') ?></p>
                    <p><?= Yii::t('app', 'It\'s up to you to choose what to take with you on your next adventure.') ?></p>
                    <?=
                    Button::widget([
                        'url' => Url::toRoute(['player-item/index']),
                        'icon' => 'dnd-chest',
                        'style' => 'btn-primary justify-content-center mt-auto',
                        'tooltip' => null,
                        'title' => Yii::t('app', 'Equip'),
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
                <img src="<?= Url::to($imgPath . '/sm/items.png') ?>" class="card-img-top" alt="<?= Yii::t('app', 'Go shopping') ?>">
                <div class="card-body">
                    <h4 class="card-title text-yellow"><?= Yii::t('app', 'Go shopping') ?></h4>
                    <p><?= Yii::t('app', 'A shop were you can buy armors, weapon and other stuff') ?></p>
                    <?=
                    Button::widget([
                        'url' => Url::toRoute(['player-cart/shop']),
                        'icon' => 'bi-shop',
                        'style' => 'btn-primary justify-content-center mt-auto',
                        'tooltip' => null,
                        'title' => Yii::t('app', 'Visit the shop'),
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
                <img src="<?= Url::to($imgPath . '/sm/8.jpg') ?>" class="card-img-top" alt="<?= Yii::t('app', 'Quest history') ?>">
                <div class="card-body">
                    <h4 class="card-title text-yellow"><?= Yii::t('app', 'Quest history') ?></h4>
                    <p><?= Yii::t('app', 'Relive the best moments of your past quests') ?></p>
                    <?=
                    Button::widget([
                        'url' => Url::toRoute(['player/history']),
                        'icon' => 'dnd-spell-book',
                        'style' => 'btn-primary justify-content-center mt-auto',
                        'tooltip' => null,
                        'title' => Yii::t('app', 'View history'),
                        'isCta' => false,
                    ])
                    ;
                    ?>
                </div>
            </div>
        </div>
    </div>
</section>
