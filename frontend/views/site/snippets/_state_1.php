<?php

use yii\helpers\Url;

/* @var yii\web\View $this */
/* @var Player $lastPlayer  */
/* @var Player[] $otherPlayers */
/* @var string $row */
/* @var string $col */
?>

<div class="<?= $row ?>">
    <div class="<?= $col ?>">
        <!-- Section 1: Last created player -->
        <?=
        $this->render('section1', [
            'title' => "Continue with {$lastPlayer->name}",
            'img' => $lastPlayer->image->getImageUrl(),
            'paragraphs' => [
                'Select this player to start a new adventure',
            ],
            'button' => [
                'url' => Url::toRoute(['player/set-current', 'id' => $lastPlayer->id]),
                'icon' => 'dnd-tower',
                'style' => 'text-decoration mt-auto',
                'tooltip' => null,
                'title' => 'Select',
                'isCta' => true,
            ]
        ])
        ?>
    </div>
</div>

<div class="<?= $row ?>">
    <div class="<?= $col ?>">
        <!-- Section 2: Other players -->
        <section id="level2">
            <?=
            $this->render('players', [
                'currentPlayer' => null,
                'otherPlayers' => $otherPlayers,
                'nbCards' => 2
            ])
            ?>
        </section>
    </div>
</div>
