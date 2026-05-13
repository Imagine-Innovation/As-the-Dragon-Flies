<?php
/** @var yii\web\View $this */
/** @var array $endowments */
/** @var integer $choices */
$choiceLabels = ['', '(a)', '(b)', '(c)', '(d)', '(e)'];

for ($choice = 1; $choice <= $choices; $choice++) {
    $endowment = $endowments[$choice];
    /** @phpstan-ignore-next-line */
    $options = max(array_keys($endowment));
    echo "<p>\n";
    for ($option = 1; $option <= $options; $option++) {
        ?>
        <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" class="custom-control-input" id="endowmentRadio-<?= $endowment[$option]['id'] ?>" name="builderEndowment-<?= $choice ?>"
                   <?= ($options === 1) ? 'checked disabled' : "onclick=\"PlayerBuilder.chooseEquipment({$choice}, {$endowment[$option]['id']});\"" ?> />
            <label class="custom-control-label" for="endowmentRadio-<?= $endowment[$option]['id'] ?>">
                <?php if ($options === 1): ?>
                    <?= $endowment[$option]['name'] ?>&nbsp;
                    <span onclick="PlayerBuilder.chooseEquipment(<?= $choice ?>, <?= $endowment[$option]['id'] ?>);">
                        <i class="bi bi-info-circle"></i>
                    </span>
                <?php else: ?>
                    <?= $choiceLabels[$option] ?>&nbsp;<?= $endowment[$option]['name'] ?>
                <?php endif; ?>
            </label>
        </div>
        <?php
    }
    echo "</p>\n";
}
