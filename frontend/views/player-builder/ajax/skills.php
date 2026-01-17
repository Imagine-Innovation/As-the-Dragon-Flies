<?php
/** @var yii\web\View $this */
/** @var common\models\Player|null $player */
/** @var array $backgroundSkills */
/** @var array $classSkills */
/** @var int $n */
$max = $n + count($backgroundSkills);
$defaultSkills = [];
foreach ($backgroundSkills as $backgroundSkill) {
    $defaultSkills[] = $backgroundSkill['skill_id'];
}
?>

<h4 class="card-title text-decoration">Skills</h4>
<?php if ($player): ?>
    <?php if ($backgroundSkills): ?>
        <p class="text-muted">Your <?= $player->background->name ?> background gives you the following skills</p>
        <br>
        <?php foreach ($backgroundSkills as $backgroundSkill): ?>
            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" id="skillCheckbox-<?= $backgroundSkill['skill_id'] ?>" name="playerSkills" class="custom-control-input" checked disabled>
                <label class="custom-control-label" for="skillCheckbox-<?= $backgroundSkill['skill_id'] ?>"><?= $backgroundSkill['name'] ?></label>
            </div>
        <?php endforeach; ?>
        <br>
    <?php endif; ?>
    <?php if ($n > 1): ?>
        <p class="text-muted">You can select a maximum of <?= $n ?> <?= $backgroundSkills ? "additional " : '' ?>skills</p>
    <?php else: ?>
        <p class="text-muted">You can select only one <?= $backgroundSkills ? "additional " : '' ?>skill</p>
    <?php endif; ?>
    <br>
    <?php foreach ($classSkills as $playerSkill): ?>
        <?php if (!in_array($playerSkill['skill_id'], $defaultSkills)): ?>
            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" id="skillCheckbox-<?= $playerSkill['skill_id'] ?>" name="playerSkills" class="custom-control-input"
                <?= $playerSkill['is_proficient'] ? 'checked' : '' ?>
                       onclick='PlayerBuilder.validateSkills(<?= $playerSkill['skill_id'] ?>, <?= $max ?>);'>
                <label class="custom-control-label" for="skillCheckbox-<?= $playerSkill['skill_id'] ?>"><?= $playerSkill['name'] ?></label>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php else: ?>
    <p class="text-muted">Your player is not properly saved yet!!</p>
<?php endif; ?>
