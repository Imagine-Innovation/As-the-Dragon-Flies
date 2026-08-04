<?php

use common\helpers\WebResourcesHelper;
use common\widgets\Button;
use common\widgets\MarkDown;

/** @var yii\web\View $this */
/** @var string $chapterName */
/** @var string $missionName */
/** @var string $actionName */
/** @var string $actionDescription */
/** @var string $shortResult */
/** @var string $result */
/** @var string $diceRoll */
/** @var string $hpLoss */
/** @var list<array{name: string|null, image: string|null, description: string|null, actionOutcome: array<string>}> $outcomeList */
/** @var bool $isFree */
/** @var int $questProgressId */
/** @var int|null $nextMissionId */
/** @var int $storyId */
$stayInCurrentMission = ($nextMissionId === null || $nextMissionId === 0);
$isFreeAndNoTransition = $isFree && $stayInCurrentMission;
$storyRoot = WebResourcesHelper::storyRootPath($storyId);
?>
<article class="text-decoration">
    <?= MarkDown::widget(['content' => $actionDescription]) ?>

    <p><?= MarkDown::widget(['content' => "{$diceRoll}. {$shortResult}. {$hpLoss}"]) ?></p>

    <?php foreach ($outcomeList as $outcome): ?>
        <hr class="border border-warning border-1 opacity-50 w-50"><hr>
        <?php if ($outcome['image'] !== null): ?>
            <div class="clearfix">
                <img class="col-md-6 float-md-end mb-3 ms-md-3" src="<?= $storyRoot ?>/img/<?= $outcome['image'] ?>" alt="<?= $outcome['name'] ?>" style="max-width: 150px;">
                <h4><?= MarkDown::widget(['content' => $outcome['name']]) ?></h4>
                <p class="text-muted"><?= MarkDown::widget(['content' => $outcome['description']]) ?></p>
                <?php foreach ($outcome['actionOutcome'] as $actionOutcome): ?>
                    <p><?= $actionOutcome ?></p>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <h4><?= MarkDown::widget(['content' => $outcome['name']]) ?></h4>
            <p class="text-muted"><?= MarkDown::widget(['content' => $outcome['description']]) ?></p>
            <?php foreach ($outcome['actionOutcome'] as $actionOutcome): ?>
                <p><?= $actionOutcome ?></p>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endforeach; ?>
</article>
<?=
Button::widget([
    'icon' => $isFreeAndNoTransition ? 'bi-arrow-repeat' : 'bi-escape',
    'title' => $isFreeAndNoTransition ? 'Try another action' : 'Finish your turn',
    'onclick' => $isFreeAndNoTransition ? null : "vtt.moveToNextPlayer({$questProgressId}, " . ($nextMissionId ?? 'null') . "); return false;",
    'isCta' => true,
    'ariaParams' => ['data-bs-dismiss' => 'modal'],
])
?>
