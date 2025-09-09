<?php

use frontend\widgets\Button;
use yii\helpers\Url;

/* @var yii\web\View $this */
/* @var Player $currentPlayer  */
/* @var Player[] $otherPlayers */
/* @var int $nbCards */
$maxCard = min($nbCards ?? 2, 6);

$rowCols = [
    0 => 'row-cols-1',
    1 => 'row-cols-1 row-cols-sm-3',
    2 => 'row-cols-1 row-cols-sm-3',
    3 => 'row-cols-1 row-cols-sm-3 row-cols-md-4',
    4 => 'row-cols-1 row-cols-sm-3 row-cols-md-4 row-cols-lg-5',
    5 => 'row-cols-1 row-cols-sm-3 row-cols-md-4 row-cols-lg-6',
    6 => 'row-cols-1 row-cols-sm-3 row-cols-md-4 row-cols-lg-7',
];
$rowCol = $rowCols[$maxCard];
$n = 0;
?>

<h4 class="text-decoration text-yellow">Your players</h4>
<div class="row <?= $rowCol ?> g-4">
    <?php
    if ($currentPlayer) {
        echo $this->renderFile('@app/views/site/snippets/player-card.php', [
            'player' => $currentPlayer,
            'current' => true,
        ]);
        $n++;
    }
    foreach ($otherPlayers as $player) {
        if ($n >= $maxCard) {
            break;
        }
        echo $this->renderFile('@app/views/site/snippets/player-card.php', [
            'player' => $player,
            'current' => false,
        ]);
        $n++;
    }
    ?>

    <!-- Create new player card -->
    <div class="col">
        <div class="image-card h-100">
            <div class="image-card-body" style="background-image: url('img/blank.png');">
                <div class="image-card-label">
                    <h1 class="display-5">
                        <i class="bi bi-plus-circle"></i>
                    </h1>
                    <h5>Create a new player</h5>
                    <p></p>
                    <?=
                    Button::widget([
                        'url' => Url::toRoute('player-builder/create'),
                        'icon' => 'bi-plus-circle',
                        'style' => 'text-decoration justify-content-center mt-auto',
                        'tooltip' => null,
                        'title' => 'Create',
                        'isCta' => false,
                    ]);
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php if ($n >= $maxCard): ?>
    <div class="lead text-decoration text-end">
        <a href="<?= Url::toRoute('player/index') ?>">See more...</a>
    </div>
<?php endif; ?>

