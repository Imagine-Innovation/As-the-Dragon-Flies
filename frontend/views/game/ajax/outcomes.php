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
?>
<article class="text-decoration">
    <?= MarkDown::widget(['content' => $action->description]) ?>
</article>
<?=
ActionOutcomes::widget([
    'outcomes' => $outcomes,
    'diceRoll' => $diceRoll,
    'status' => $status,
    'hpLoss' => $hpLoss,
    'isFree' => $isFree,
    'questProgressId' => $questProgressId,
    'nextMissionId' => $nextMissionId,
]);
?>
<?php
if ($nextMissionId) {
    $buttonTitle = 'Move to ' . Html::encode($nextMissionName ?? "Mission #{$nextMissionId}");
    $buttonIcon = 'bi-chevron-double-right';
    $buttonOnclick = "vtt.moveToNextPlayer({$questProgressId}, {$nextMissionId}); return false;";
} elseif ($isFree) {
    $buttonTitle = 'Try another action';
    $buttonIcon = 'bi-arrow-repeat';
    $buttonOnclick = null;
} else {
    $buttonTitle = 'Finish your turn';
    $buttonIcon = 'bi-escape';
    $buttonOnclick = "vtt.moveToNextPlayer({$questProgressId}, null); return false;";
}

echo Button::widget([
    'icon' => $buttonIcon,
    'title' => $buttonTitle,
    'onclick' => $buttonOnclick,
    'isCta' => true,
    'ariaParams' => ['data-bs-dismiss' => 'modal'],
]);
?>
<p>
    isFree=<?= $isFree ? 'true' : 'false' ?>,
    questProgressId=<?= $questProgressId ?>,
    nextMissionId=<?= $nextMissionId ?? 'null' ?>
</p>
