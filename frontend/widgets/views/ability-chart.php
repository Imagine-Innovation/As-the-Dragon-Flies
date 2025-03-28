<?php
/** @var int $id */
/** @var int $score */
/** @var int $bonus */
/** @var string $code */
/** @var int $modifier */
$abilityScore = $score + $bonus;
?>
<div class="lite-chart">
    <svg style="width: 60px; height: 60px; padding: 0px" viewBox="0 0 60 60">

    <circle cx="30" cy="30" r="25" fill="none" stroke="#446688" stroke-width="8" />
    <path class="path" id="abilityChart-<?= $id ?>"
          data-score="<?= $score ?>" data-bonus="<?= $bonus ?>" data-min="0" data-max="30" 
          fill="none" stroke="#dc3545" stroke-width="8" />
    <text x="50%" y="35%" dominant-baseline="middle" text-anchor="middle" font-size="0.80rem"
          style="fill:rgba(255,255,255,.75)!important"><?= $code ?>
    </text>
    <text x="50%" y="60%" dominant-baseline="middle" text-anchor="middle" font-size="1.05rem"
          style="fill:rgba(255,255,255,.75)!important" id="abilityText-<?= $id ?>"><?= $abilityScore ?>
    </text>
    <circle cx="50" cy="10" r="10" fill="#ff4444" />
    <text x="50" y="10" dominant-baseline="middle" text-anchor="middle" font-size="0.95rem" id="modifier-<?= $id ?>"
          style="fill:rgba(255,255,255,1)!important"><?= $modifier ?></text>
    </svg>
</div>
