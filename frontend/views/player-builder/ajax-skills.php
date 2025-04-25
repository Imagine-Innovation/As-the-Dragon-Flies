<?php
/** @var yii\web\View $this */
/** @var app/models/Player $player */
/** @var app/models/Skill[] $background_skills */
/** @var app/models/Skill[] $class_skills */
/** @var int $n */
$max = $n + count($background_skills);
$defaultSkill = [];
foreach ($background_skills as $skill) {
    $defaultSkill[] = $skill->id;
}

$skillId = [];
if ($player) {
    foreach ($player->playerSkills as $playerSkill) {
        $skillId[] = $playerSkill->skill_id;
    }
}
?>

<h4 class="card-title text-decoration">Skills</h4>
<?php if ($player): ?>
    <?php if ($background_skills): ?>
        <p class="text-muted">Your <?= $player->background->name ?> background gives you the following skills</p>
        <br>
        <?php foreach ($background_skills as $skill): ?>
            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" id="skillCheckbox-<?= $skill->id ?>" name="playerSkills" class="custom-control-input" checked disabled>
                <label class="custom-control-label" for="skillCheckbox-<?= $skill->id ?>"><?= $skill->name ?></label>
            </div>
        <?php endforeach; ?>
        <br>
    <?php endif; ?>
    <?php if ($n > 1): ?>
        <p class="text-muted">You can select a maximum of <?= $n ?> <?= $background_skills ? "additional " : "" ?>skills</p>
    <?php else: ?>
        <p class="text-muted">You can select only one <?= $background_skills ? "additional " : "" ?>skill</p>
    <?php endif; ?>
    <br>
    <?php foreach ($class_skills as $skill): ?>
        <?php if (!in_array($skill->id, $defaultSkill)): ?>
            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" id="skillCheckbox-<?= $skill->id ?>" name="playerSkills" class="custom-control-input"
                <?= in_array($skill->id, $skillId) ? 'checked' : '' ?>
                       onclick='PlayerBuilder.validateSkills(<?= $skill->id ?>, <?= $max ?>);'>
                <label class="custom-control-label" for="skillCheckbox-<?= $skill->id ?>"><?= $skill->name ?></label>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text-muted">Your player is not properly saved yet!!</p>
<?php endif; ?>