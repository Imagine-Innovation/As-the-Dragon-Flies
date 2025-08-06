<?php

use common\components\AppStatus;
use yii\helpers\Url;
use frontend\widgets\PlayerCharacteristics;

/** @var yii\web\View $this */
/** @var common\models\Player $player */
$route = $player->status == AppStatus::ACTIVE->value ? 'player/view' : 'player/update';
?>

<div class="card h-100">
    <div class="toolbar toolbar--inner">
        <h4><?= $player->name ?></h4>
        <div class="actions">
            <a href="<?= Url::toRoute([$route, 'id' => $player->id]) ?>" role="button" class="actions__item position-relative"
               data-bs-toggle="tooltip" title="View player details" data-placement="bottom">
                <i class="bi bi-controller"></i>
            </a>
        </div>
    </div>

    <img class="card-img-top" src="img/characters/<?= $player->avatar ?>" alt="avatar">

    <div class="card-body">
        <h4 class="card-title"><?= $player->name ?? "Unkown yet" ?></h4>
        <?php if ($player->age): ?>
            <h4 class="card-subtitle"><?= $player->age ?>-year-old <?= $player->gender == 'M' ? 'male' : 'female' ?> <?= $player->race_id ? $player->race->name : "Undefined" ?></h4>
        <?php endif; ?>

        <div>
            <p>
                <span class="badge badge-warning"><?= $player->level->name ?></span>
                <span class="badge badge-warning"><?= $player->alignment_id ? $player->alignment->name : "Undefined" ?></span>
                <span class="badge badge-warning"><?= $player->class_id ? $player->class->name : "Undefined" ?></span>
            </p>
        </div>

        <?= PlayerCharacteristics::widget(['player' => $player, 'embedded' => true]) ?>
    </div>
</div>
