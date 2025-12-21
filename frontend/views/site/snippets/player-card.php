<?php

use frontend\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Player $player  */
/** @var bool $current */

$route = $current ? 'player/update' : 'player/set-current';
?>

<div class="col">
    <?php if ($current): ?>
        <div class="position-relative">
        <?php endif; ?>
        <div class="image-card h-100">
            <div class="image-card-body" style="background-image: url('img/character/<?= $player->image->file_name ?>');">
                <div class="image-card-label">
                    <h5><?= $player->name ?></h5>
                    <p class="small mb-1"><?= $player->age ?>-year-old <?= $player->gender == 'M' ? 'male' : 'female' ?> <?= $player->race->name ?></p>
                    <p class="small mb-0"><?= $player->level->name ?> <?= $player->alignment->name ?> <?= $player->class->name ?></p>
                    <p></p>
                    <?=
                    Button::widget([
                        'url' => Url::toRoute([$route, 'id' => $player->id]),
                        'icon' => 'dnd-tower',
                        'style' => 'text-decoration mt-auto',
                        'tooltip' => null,
                        'title' => $current ? 'Edit' : 'Select',
                        'isCta' => false,
                    ]);
                    ?>
                </div>
            </div>
            <?php if ($current): ?>
                <span class="position-absolute top-0 start-0 badge rounded-pill bg-primary">
                    current
                </span>
            <?php endif; ?>
        </div>
        <?php if ($current): ?>
        </div>
    <?php endif; ?>
</div>
