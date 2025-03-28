<?php

use common\models\Level;

/** @var yii\web\View $this */
/** @var common\models\CharacterClass $model */
$index = array();
$header = array();
$data = array();

$index[] = -2;
$index[] = -1;
$index[] = 0;
$header[-2] = "Level";
$header[-1] = "Proficiency Bonus";
$header[0] = "Features";

foreach ($model->classProficiencies as $p) {
    $col = $p->sort_order;
    if (!in_array($col, $index, TRUE)) {
        $index[] = $col;
    }
    $colName = $p->proficiency->name;
    if ($colName === "Spell") {
        $colName = $colName . " L" . $p->spell_level;
    }
    $header[$col] = $colName;
}

sort($index);

$levels = Level::find()->all();

foreach ($levels as $level) {
    $data[$level->id][-2] = $level->name;
    $data[$level->id][-1] = "+" . $level->proficiency_bonus;
    foreach ($index as $i) {
        if ($i >= 0) {
            $data[$level->id][$i] = "&nbsp;";
        }
    }
}

foreach ($model->classFeatures as $f) {

    $val = array();   // Clear the array of values

    if ($f->cr > 0) {
        $val[] = "CR " . $f->cr;
    }
    if ($f->dice != "") {
        $val[] = $f->dice;
    }
    if ($f->weapon_dice > 0) {
        $val[] = $f->weapon_dice . " weapon dice";
    }
    if ($f->times_used > 1) {
        $val[] = "used " . $f->times_used . " times";
    }
    if ($f->spell_level > 0) {
        $val[] = "spell level " . $f->spell_level;
    }

    $row = $f->level_id;
    if (count($val) > 0) {
        $data[$row][0] = $f->feature->name . " (" . implode(", ", $val) . ")";
    } else {
        $data[$row][0] = $f->feature->name;
    }
}

foreach ($model->classProficiencies as $p) {
    $col = $p->sort_order;
    $row = $p->level_id;

    $data[$row][$col] = $p->bonus . $p->dice . $p->spell_slot;
}
?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <?php foreach ($index as $i): ?>
                            <th class="text-center"><?= $header[$i] ?></th>
                        <?php endforeach; ?>

                    </tr>
                </thead>
                <tbody>
                    <?php for ($level = 1; $level <= 20; $level++) { ?>
                        <tr>
                            <?php
                            foreach ($index as $i) {
                                if ($i === -2) {
                                    ?>
                                    <th class="text-center" scope="row"><?= $data[$level][$i] ?></th>
                                <?php } else { ?>
                                    <td class="text-center"><?= $data[$level][$i] ?></td>
                                    <?php
                                } // end if
                            } //endforeach;
                            ?>
                        </tr>
                        <?php
                    } // end for
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
