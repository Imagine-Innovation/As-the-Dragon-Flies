<?php

use common\helpers\WebResourcesHelper;
use common\models\Player;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Player $player  */
/** @var Player[] $otherPlayers */
/** @var string $row */
/** @var string $col */
$imgPath = WebResourcesHelper::imagePath();
?>

<div class="<?= $row ?>">
    <div class="<?= $col ?>">
        <!-- Section 1: Find a quest -->
        <?=
        $this->render('section1', [
            'title' => Yii::t('app', 'Find a quest'),
            'img' => Url::to($imgPath . '/sm/story.png'),
            'paragraphs' => [
                Yii::t('app', 'Your player is ready for a new adventure.'),
                Yii::t('app', 'Visit the tavern to find a quest.'),
            ],
            'button' => [
                'url' => Url::toRoute(['story/index']),
                'icon' => 'dnd-scroll"',
                'style' => 'text-decoration mt-auto',
                'tooltip' => null,
                'title' => Yii::t('app', 'Browse the stories'),
                'isCta' => true,
            ],
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
                'nbCards' => 3,
            ])
            ?>
        </section>
    </div>
</div>
