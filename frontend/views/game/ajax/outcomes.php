<?php

use frontend\widgets\ActionOutcomes;
use common\widgets\Button;
use common\widgets\MarkDown;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Action $action */
/** @var common\components\AppStatus $status */
/** @var common\models\Outcome[] $outcomes */
/** @var string $diceRoll */
/** @var int $hpLoss */
/** @var bool $isFree */
/** @var bool $canReplay */
/** @var int $questProgressId */
/** @var int $missionId */
/** @var int|null $nextMissionId */
/** @var string|null $nextMissionName */
/** @var string $nextActionButtonType */
?>
<article class="text-decoration">
    <?= MarkDown::widget(['content' => $action->description]) ?>
</article>
<?php
echo ActionOutcomes::widget([
    'outcomes' => $outcomes,
    'diceRoll' => $diceRoll,
    'status' => $status,
    'hpLoss' => $hpLoss,
    'isFree' => $isFree,
    'questProgressId' => $questProgressId,
    'nextMissionId' => $nextMissionId,
]);

$nextActionButtonParam = match ($nextActionButtonType) {
    'nextMission' => [
        'icon' => 'bi-chevron-double-right',
        'title' => 'Move to ' . ($nextMissionName ?? "Mission #{$nextMissionId}"),
        'onclick' => "vtt.moveToNextPlayer({$questProgressId}, {$nextMissionId}); return false;",
        'isCta' => true,
        'ariaParams' => ['data-bs-dismiss' => 'modal'],
    ],
    'samePlayer' => [
        'icon' => 'bi-arrow-repeat',
        'title' => 'Try another action',
        'onclick' => null,
        'isCta' => true,
        'ariaParams' => ['data-bs-dismiss' => 'modal'],
    ],
    default => [
        'icon' => 'bi-escape',
        'title' => 'Finish your turn',
        'onclick' => "vtt.moveToNextPlayer({$questProgressId}, null); return false;",
        'isCta' => true,
        'ariaParams' => ['data-bs-dismiss' => 'modal'],
    ],
};

echo Button::widget($nextActionButtonParam);
?>
<p>
    isFree=<?= $isFree ? 'true' : 'false' ?>,
    questProgressId=<?= $questProgressId ?>,
    nextMissionId=<?= $nextMissionId ?? 'null' ?>
</p>
