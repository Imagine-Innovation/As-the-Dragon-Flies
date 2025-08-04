<?php
use frontend\components\BuilderTool;

/** @var yii\web\View $this */
/** @var app/models/Player $player */
/** @var int $n */
$languages = "";

<h4 class = "card-title text-decoration">Languages</h4>
<?php if ($player):
?>
<?php if ($backgroundLanguages): ?>
<p class="text-muted">Your <?= $player->background->name ?> background gives you the following skills</p>
<br>
<?php foreach ($backgroundLanguages as $backgroundLanguage): ?>
<div class="custom-control custom-checkbox mb-2">
    <input type="checkbox" id="skillCheckbox-<?= $backgroundLanguage['skill_id'] ?>" name="playerLanguages" class="custom-control-input" checked disabled>
    <label class="custom-control-label" for="skillCheckbox-<?= $backgroundLanguage['skill_id'] ?>"><?= $backgroundLanguage['name'] ?></label>
</div>
<?php endforeach; ?>
<br>
<?php endif; ?>
<?php if ($n > 1): ?>
<p class="text-muted">You can select a maximum of <?= $n ?> <?= $backgroundLanguages ? "additional " : "" ?>skills</p>
<?php else: ?>
<p class="text-muted">You can select only one <?= $backgroundLanguages ? "additional " : "" ?>skill</p>
<?php endif; ?>
<br>
<?php foreach ($classLanguages as $playerLanguage): ?>
<?php if (!in_array($playerLanguage['skill_id'], $defaultLanguages)): ?>
<div class="custom-control custom-checkbox mb-2">
    <input type="checkbox" id="skillCheckbox-<?= $playerLanguage['skill_id'] ?>" name="playerLanguages" class="custom-control-input"
    <?= $playerLanguage['is_proficient'] ? 'checked' : '' ?>
           onclick='PlayerBuilder.validateLanguages(<?= $playerLanguage['skill_id'] ?>, <?= $max ?>);'>
    <label class="custom-control-label" for="skillCheckbox-<?= $playerLanguage['skill_id'] ?>"><?= $playerLanguage['name'] ?></label>
</div>
<?php endif; ?>
<?php endforeach; ?>
<?php else: ?>
<p class="text-muted">Your player is not properly saved yet!!</p>
<?php endif; ?>
