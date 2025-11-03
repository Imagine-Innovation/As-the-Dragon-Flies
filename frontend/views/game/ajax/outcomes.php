<?php

use frontend\widgets\Button;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var string $diceRoll */
/** @var common\components\Apptatus $status */
/** @var common\models\Outcome $outcomes */
/** @var int $hpLoss */
/** @var bool $isFree */
/** @var int $questProgressId */
/** @var int|null $nextMissionId */
$hr = '<hr class="border border-warning border-1 opacity-50 w-50"><hr>';
echo "<p>{$diceRoll}: the action {$status->getActionAdjective()}</p>";
if ($hpLoss > 0) {
    echo "<p>You lost {$hpLoss} hit points</p>";
}

foreach ($outcomes as $outcome) {
    echo $hr;
    if ($outcome->description) {
        echo "<p>" . nl2br($outcome->description) . "</p>";
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
}

if ($isFree) {
    echo Button::widget([
        'icon' => 'bi-plus-square',
        'title' => "Try another action",
        'isCta' => true,
    ]);
}
