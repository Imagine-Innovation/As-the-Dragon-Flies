<?php
/** @var yii\web\View $this */
/** @var array $ageTable */
/** @var int $age */
$devider = 20;

$adultAge = $ageTable['adultAge'];
$lifespan = $ageTable['lifespan'];

// Devide the lifespan into "$devider" steps
$step = floor(($lifespan - $adultAge) / $devider);

// Compute min value regarding the lifespan and the adult age
// add one step for the childhood of the player
$min = $lifespan - (($devider + 1) * $step);
$currentAge = max(0, $age);
$currentLabel = current(array_filter(
                        $ageTable['labels'],
                        fn($label) => $currentAge <= $label['age']
                ))['lib'] ?? 'fine';
?>
<h4 class="card-title text-decoration">Age</h4>
<div id="useSliderAgeLabel" style="visibility: <?= $currentAge == 0 ? "visible" : "hidden" ?>">
    <h6 class="card-subtitle">Simply move the slider below to select your player's age.</h6>
</div>
<div id="displayAgeLabel" style="visibility: <?= $currentAge == 0 ? "hidden" : "visible" ?>">
    <h6 class="card-subtitle">Your player is <span id="playerAgeNum"><?= $currentAge ?></span>. He is <span id="playerAgeLabel"><?= $currentLabel ?></span></h6>
</div>
<br>
<div class="custom-control custom-range mb-2">
    <input type="range" class="form-control-range" min="<?= $min ?>" max="<?= $lifespan ?>" value="<?= $currentAge ?>" step="<?= $step ?>" id="ageRange"
           onInput="$('#ageRangeValue').html($(this).val())"
           onload='PlayerBuilder.setProperty("age", <?= $currentAge ?>);'
           onchange='PlayerBuilder.setProperty("age", $("#ageRange").val());'>
    <span id="ageRangeValue"><?= $currentAge ?></span>
</div>
