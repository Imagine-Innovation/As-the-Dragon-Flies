<?php

use frontend\widgets\ActionOutcomes;
use common\widgets\Button;
use common\widgets\MarkDown;

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
<?=
Button::widget([
    'icon' => $isFree ? 'bi-arrow-repeat' : 'bi-escape',
    'title' => $isFree ? 'Try another action' : 'Finish your turn',
    'onclick' => $isFree ? null : "vtt.moveToNextPlayer({$questProgressId}, " . ($nextMissionId ?? 'null') . "); return false;",
    'isCta' => true,
    'ariaParams' => ['data-bs-dismiss' => 'modal'],
])
?>
<p>
    isFree=<?= $isFree ? 'true' : 'false' ?>,
    questProgressId=<?= $questProgressId ?>,
    nextMissionId=<?= $nextMissionId ?? 'null' ?>
</p>
