<?php

use frontend\widgets\ActionOutcomes;
use frontend\widgets\Button;

/** @var yii\web\View $this */
/** @var string $diceRoll */
/** @var common\components\AppStatus $status */
/** @var common\models\Outcome[] $outcomes */
/** @var int $hpLoss */
/** @var bool $isFree */
/** @var int $questProgressId */
/** @var int|null $nextMissionId */
?>

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
    'onclick' => $isFree ? null : "vtt.moveToNextPlayer({$questProgressId}, {$nextMissionId}); return false;",
    'isCta' => true,
    'ariaParams' => ['data-bs-dismiss' => 'modal'],
])
?>
<p>
    isFree=<?= $isFree ? 'true' : 'false' ?>,
    questProgressId=<?= $questProgressId ?>,
    nextMissionId=<?= $nextMissionId ?? 'null' ?>
</p>
