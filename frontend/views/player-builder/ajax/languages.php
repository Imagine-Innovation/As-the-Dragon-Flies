<?php
/** @var yii\web\View $this */
/** @var common\models\Player|null $player */
/** @var array $raceLanguages */
/** @var array $otherLanguages */
/** @var int $n */
$max = $n + count($raceLanguages);
$defaultLanguages = [];
foreach ($raceLanguages as $raceLanguage) {
    $defaultLanguages[] = $raceLanguage['language_id'];
}
?>

<h4 class="card-title text-decoration">Languages</h4>
<?php if ($player): ?>
    <?php if ($raceLanguages): ?>
        <p class="text-muted">As a <?= $player->race->name ?>, you speak the following languages</p>
        <br>
        <?php foreach ($raceLanguages as $raceLanguage): ?>
            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" id="languageCheckbox-<?= $raceLanguage['language_id'] ?>" name="playerLanguages" class="custom-control-input" checked disabled>
                <label class="custom-control-label" for="languageCheckbox-<?= $raceLanguage['language_id'] ?>"><?= $raceLanguage['name'] ?></label>
            </div>
        <?php endforeach; ?>
        <br>
    <?php endif; ?>
    <?php if ($n > 0): ?>
        <?php if ($n > 1): ?>
            <p class="text-muted">You can select a maximum of <?= $n ?> <?= $raceLanguages ? "additional " : "" ?>languages</p>
        <?php else: ?>
            <p class="text-muted">You can select only one <?= $raceLanguages ? "additional " : "" ?>language</p>
        <?php endif; ?>
        <br>
        <?php foreach ($otherLanguages as $otherLanguage): ?>
            <?php if (!in_array($otherLanguage['language_id'], $defaultLanguages)): ?>
                <div class="custom-control custom-checkbox mb-2">
                    <input type="checkbox" id="languageCheckbox-<?= $otherLanguage['language_id'] ?>" name="playerLanguages" class="custom-control-input"
                           onclick='PlayerBuilder.validateLanguages(<?= $otherLanguage['language_id'] ?>, <?= $max ?>);'>
                    <label class="custom-control-label" for="languageCheckbox-<?= $otherLanguage['language_id'] ?>"><?= $otherLanguage['name'] ?></label>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
<?php else: ?>
    <p class="text-muted">Your player is not properly saved yet!!</p>
<?php endif; ?>
