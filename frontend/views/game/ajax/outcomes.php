<?php

use frontend\widgets\Button;

/** @var yii\web\View $this */
/** @var string $diceRoll */
/** @var common\components\Apptatus $status */
/** @var common\models\Outcome $outcomes */
/** @var int $hpLoss */
/** @var bool $isFree */
/** @var int $questProgressId */
/** @var int|null $nextMissionId */
$canReplay = false;
$hr = '<hr class="border border-warning border-1 opacity-50 w-50"><hr>';
echo "<p>{$diceRoll}: the action {$status->getActionAdjective()}</p>";
if ($hpLoss > 0) {
    echo "<p>You lost {$hpLoss} hit points</p>";
}
if (empty($outcomes)) {
    echo "<p>Something happened, that's for sure, but I don't really know what</p>";
    echo print_r($outcomes);
} else {
    foreach ($outcomes as $outcome) {
        echo $hr;
        if ($outcome->description) {
            echo "<p>" . nl2br($outcome->description ?? "Something happened, that's for sure, but I don't really know what") . "</p>";
        }

        if ($outcome->gained_gp > 0) {
            echo "<p>You gained {$outcome->gained_gp} gold pieces</p>";
        }

        if ($outcome->gained_xp > 0) {
            echo "<p>You gained {$outcome->gained_xp} experience points</p>";
        }

        if ($outcome->item_id) {
            echo "<p>You now have a {$outcome->item->name} in your back bag</p>";
        }

        $canReplay = $canReplay || $outcome->can_replay;
    }
}

echo "<p>isFree=" . ($isFree ? 'true' : 'false') . ", questProgressId={$questProgressId}, nextMissionId=" . ($nextMissionId ?? "null") . ", canReplay=" . ($canReplay ? 'true' : 'false') . "</p>";
if ($isFree) {
    echo Button::widget([
        'icon' => 'bi-arrow-repeat',
        'title' => "Try another action",
        'isCta' => true,
        'ariaParams' => ['data-bs-dismiss' => 'modal'],
    ]);
} else {
    echo Button::widget([
        'icon' => 'bi-escape',
        'title' => "Finish your turn",
        'isCta' => true,
        'onclick' => "vtt.moveToNextPlayer({$questProgressId}, {$nextMissionId}); return false;",
        'ariaParams' => ['data-bs-dismiss' => 'modal'],
    ]);
}
