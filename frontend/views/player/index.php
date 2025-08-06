<?php

use common\helpers\Utilities;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Player $players */
$this->title = 'Players';
$this->params['breadcrumbs'][] = $this->title;
?>
<h3><?= Utilities::encode($this->title) ?></h3>
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">List of the players you have already defined</h4>
            <div class="actions">
                <a href="<?= Url::toRoute(['player-builder/create']) ?>" role="button" class="actions__item position-relative"
                   data-bs-toggle="tooltip" title="Create a new player" data-placement="bottom">
                    <i class="bi bi-dpad"></i>
                </a>
            </div>
            <div class="row g-4">
                <?php foreach ($players as $player): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <?= $this->renderFile('@app/views/player/snippets/card.php', ['player' => $player,]) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
