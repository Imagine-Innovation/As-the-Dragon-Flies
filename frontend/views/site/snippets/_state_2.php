<?php

use frontend\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Player $player  */
/** @var Player[] $otherPlayers */
/** @var string $row */
/** @var string $col */
?>

<div class="<?= $row ?>">
    <div class="<?= $col ?>">
        <!-- Section 1: Find a quest -->
        <?=
        $this->render('section1', [
            'title' => "Find a quest",
            'img' => Url::to('@web/img/sm/story.png'),
            'paragraphs' => [
                'Your player is ready for a new adventure.',
                'Visit the tavern to find a quest.',
            ],
            'button' => [
                'url' => Url::toRoute(['story/index']),
                'icon' => 'dnd-scroll"',
                'style' => 'text-decoration mt-auto',
                'tooltip' => null,
                'title' => 'Browse the stories',
                'isCta' => true,
            ]
        ])
        ?>
    </div>
</div>

<div class="<?= $row ?>">
    <div class="<?= $col ?>">
        <!-- Section 2: Other actions -->
        <?=
        $this->render('other-actions', [
            'player' => $player,
        ])
        ?>
    </div>
</div>

<div class="<?= $row ?>">
    <div class="<?= $col ?>">
        <!-- Section 3: Players -->
        <section id="level3">
            <?=
            $this->render('players', [
                'currentPlayer' => $player,
                'otherPlayers' => $otherPlayers,
                'nbCards' => 3
            ])
            ?>
        </section>
    </div>
</div>
