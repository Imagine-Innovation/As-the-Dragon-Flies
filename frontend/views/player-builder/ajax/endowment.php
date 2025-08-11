<?php
/** @var yii\web\View $this */
/** @var string[] $endowments */
/** @var integer $choices */
$choiceLabels = ['', '(a)', '(b)', '(c)', '(d)', '(e)'];
?>
<?php
for ($choice = 1; $choice <= $choices; $choice++):
    $endowment = $endowments[$choice];
    $options = max(array_keys($endowment));
    ?>
    <p>
        <?php for ($option = 1; $option <= $options; $option++): ?>
        <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" class="custom-control-input"
                   id="endowmentRadio-<?= $endowment[$option]['id'] ?>"
                   name="builderEndowment-<?= $choice ?>"
                   <?php if ($options === 1): ?>
                       checked disabled
                   <?php else: ?>
                       onclick="PlayerBuilder.chooseEquipment(<?= $choice ?>, <?= $endowment[$option]['id'] ?>);"
                   <?php endif; ?>
                   />
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
    <?php endfor; ?>
    </p>
<?php endfor; ?>
