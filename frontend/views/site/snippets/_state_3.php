<?php

use common\components\AppStatus;
use frontend\widgets\Button;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Player $player  */
/** @var common\models\Player[] $otherPlayers */
/** @var string $row */
/** @var string $col */
$quest = $player->quest;
$statusEnum = AppStatus::from($quest->status);
$iconInfo = $statusEnum->getIcon();
?>

<div class="<?= $row ?>">
    <div class="<?= $col ?>">
        <!-- Section 1: Resume the quest -->
        <?=
        $this->render('section1', [
            'title' => "Resume the quest '{$quest->name}'",
            'img' => Url::to("@web/resources/story-{$quest->story_id}/img/{$quest->image}"),
            'paragraphs' => [
                'Your player is currently on a quest.',
                $iconInfo['tooltip'],
                'Jump back into the action!',
            ],
            'button' => [
                'url' => Url::toRoute(['quest/resume', 'id' => $quest->id]),
                'icon' => 'dnd-tower"',
                'style' => 'text-decoration mt-auto',
                'tooltip' => null,
                'title' => 'Resume the quest',
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
