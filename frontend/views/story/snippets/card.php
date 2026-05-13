<?php

use common\components\gameplay\TavernManager;
use common\helpers\StoryNeededClass;
use common\helpers\StoryPlayers;
use common\helpers\WebResourcesHelper;
use common\widgets\Button;
use common\widgets\ModalDesc;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\User $user */
/** @var common\models\Player $player */
/** @var common\models\Story $story */
/** @var common\models\Quest $quest */
$tavernManager = new TavernManager(['quest' => $quest]);
$canJoin = $tavernManager->canPlayerJoinQuest($player);

$randomFileName = random_int(1, 8) . '.jpg';
$imgPath = WebResourcesHelper::imagePath();
$randomImage = "{$imgPath}/sm/{$randomFileName}";
$storyRoot = WebResourcesHelper::storyRootPath($story->id);

$image = $story->image ? "{$storyRoot}/img/{$story->image}" : $randomImage;
?>

<div class="card h-100">
    <div class="card-header">
        <h4 class="card-title text-decoration"><?= $story->name ?></h4>
    </div>
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

        <p class="lead">
            <?= StoryPlayers::exists($story, $user->players); ?>
        </p>
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
                'style' => 'text-decoration',
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
