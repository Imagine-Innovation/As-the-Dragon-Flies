<?php

use common\helpers\Utilities;

/** @var yii\web\View $this */
/** @var common\models\PlayerBuilder $player */
/** @var string[] $endowments */
/** @var integer $choices */
$choiceLabels = ['', '(a)', '(b)', '(c)', '(d)', '(e)'];
$paragraphs = [
    "Your class '{$player->class->name}' and your background '{$player->background->name}' offers you the following options for building your starting equipment",
    "Select one option on each line:"
];
?>
<!-- Character Builder - Equipment Tab -->
<?= Utilities::formatMultiLine($paragraphs) ?>

<div class="container-fluid">
    <div class="row g-4">
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body" id="ajaxEndowment">
                    <h4 class="card-title">Your background <?= $player->background->name ?> gives you</h4>
                    <p><?= $player->background->initial_equipment ?>&nbsp;
                        <span onclick="PlayerBuilder.chooseBackgroundEquipment();">
                            <i class="bi bi-info-circle"></i>
                        </span>
                    </p>
                    <p/>
                    <h4 class="card-title">Your class <?= $player->class->name ?> gives you</h4>
                    <?php
                    for ($choice = 1; $choice <= $choices; $choice++):
                        $options = max(array_keys($endowments[$choice]));
                        ?>
                        <p>
                            <?php for ($option = 1; $option <= $options; $option++): ?>
                            <div class="custom-control custom-radio custom-control-inline">
                                <input type="radio" class="custom-control-input"
                                       id="endowmentRadio-<?= $endowments[$choice][$option]['id'] ?>"
                                       name="builderEndowment-<?= $choice ?>"
                                       <?php if ($options === 1): ?>
                                           checked disabled
                                       <?php else: ?>
                                           onclick="PlayerBuilder.chooseEquipment(<?= $choice ?>, <?= $endowments[$choice][$option]['id'] ?>);"
                                       <?php endif; ?>
                                       />
                                <label class="custom-control-label" for="endowmentRadio-<?= $endowments[$choice][$option]['id'] ?>">
                                    <?php if ($options === 1): ?>
                                        <?= $endowments[$choice][$option]['name'] ?>&nbsp;
                                        <span onclick="PlayerBuilder.chooseEquipment(<?= $choice ?>, <?= $endowments[$choice][$option]['id'] ?>);">
                                            <i class="bi bi-info-circle"></i>
                                        </span>
                                    <?php else: ?>
                                        <?= $choiceLabels[$option] ?>&nbsp;<?= $endowments[$choice][$option]['name'] ?>
                                    <?php endif; ?>
                                </label>
                            </div>
                        <?php endfor; ?>
                        </p>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card h-100">
                <div class="card-body" id="ajaxSelectedItems">
                    <h4 class="card-title">Items</h4>
                    <div id="ajaxItemImages"></div>
                    <div id="ajaxBackgroundItems"></div>
                    <?php for ($i = 1; $i <= $choices; $i++): ?>
                        <div id="ajaxItemChoice-<?= $i ?>"></div>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        //PlayerBuilder.loadBackgroundEquipment();
<?php
for ($choice = 1; $choice <= $choices; $choice++):
    $options = max(array_keys($endowments[$choice]));
    if ($options === 1):
        ?>
                PlayerBuilder.chooseEquipment(<?= $choice ?>, <?= $endowments[$choice][1]['id'] ?>);
    <?php endif; ?>
<?php endfor; ?>
    });
</script>
