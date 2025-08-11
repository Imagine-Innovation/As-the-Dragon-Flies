<?php

use common\helpers\Utilities;
use frontend\widgets\Button;
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
                <?=
                Button::widget([
                    'mode' => 'icon',
                    'url' => Url::toRoute(['player-builder/create']),
                    'icon' => 'bi-dpad',
                    'tooltip' => 'Create a new player'
                ])
                ?>
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
