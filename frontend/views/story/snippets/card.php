<?php

use common\components\gameplay\TavernManager;
use common\helpers\StoryNeededClass;
use common\helpers\StoryPlayers;
use frontend\widgets\Button;
use frontend\widgets\ModalDesc;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\User $user */
/** @var common\models\Player $player */
/** @var common\models\Story $story */
/** @var common\models\Quest $quest */
/** @var integer $isDesigner */
/** @var integer $isPlayer */
$tavernManager = new TavernManager(['quest' => $quest]);
$canJoin = $tavernManager->canPlayerJoinQuest($player);

$image = $story->image ? "resources/story-{$story->id}/img/{$story->image}" : 'img/sm/' . mt_rand(1, 8) . '.jpg';
?>

<div class="card h-100">
    <div class="card-header">
        <h4 class="card-title"><?= $story->name ?></h4>
    </div>
    <?php if ($user->is_designer): ?>
        <div class="actions">
            <?=
        Button::widget([
            'mode' => 'icon',
            'url' => Url::toRoute(['story/view', 'id' => $story->id]),
            'icon' => 'bi-journal',
            'tooltip' => 'View story details',
        ])
    ?>
            <?=
        Button::widget([
            'mode' => 'icon',
            'url' => Url::toRoute(['story/update', 'id' => $story->id]),
            'icon' => 'bi-journal-code',
            'tooltip' => 'Edit story',
        ])
    ?>
        </div>
    <?php endif; ?>

    <img class="card-img-top" src="<?= $image ?>">

    <div class="card-body">
        <?php if (!$canJoin['denied']): ?>
            <p>
                <?=
            Button::widget([
                'isPost' => true,
                'url' => Url::toRoute(['quest/join', 'storyId' => $story->id, 'playerId' => $player?->id]),
                'icon' => 'dnd-tower',
                'title' => 'Join the quest',
                'style' => 'text-decoration',
                'isCta' => true,
            ])
        ?>
            </p>
        <?php endif; ?>

        <?php if ($user->is_player): ?>
            <p class="lead">
                <?= StoryPlayers::exists($story, $user->players); ?>
            </p>
        <?php endif; ?>

        <p>
            <span class="badge badge-info"><?= $story->getRequiredLevels() ?></span>
            <span class="badge badge-info"><?= $story->companySize ?></span>
            <?php if ($story->tavern): ?>
                <span class="badge badge-info"><?= $story->tavern->getQuestPlayers()->count() ?> partners waiting</span>
            <?php endif; ?>
        </p>

        <p class="text-muted">
            <?=
    ModalDesc::widget([
        'name' => $story->name,
        'description' => $story->description,
        'maxLength' => 300,
        'id' => $story->id,
    ])
?>
        </p>

        <?php if ($story->tags): ?>
            <div class="listview__attrs">
                <?php foreach ($story->tags as $tag): ?>
                    <span><?= $tag->name ?></span>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?= StoryNeededClass::classList($story); ?>
    </div>
</div>
