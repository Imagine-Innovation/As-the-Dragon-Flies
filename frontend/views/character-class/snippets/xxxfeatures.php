<?php

use common\models\Level;

/** @var yii\web\View $this */
/** @var common\models\CharacterClass $model */
$header = ['Level', 'Proficiency Bonus', 'Features'];

$levels = Level::find()->all();

$data = [];
foreach ($levels as $level) {
    $data[$level->id] = [$level->name, "+{$level->proficiency_bonus}", '&nbsp;'];
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
        $data[$row][2] = $f->feature->name . " (" . implode(", ", $val) . ")";
    } else {
        $data[$row][2] = $f->feature->name;
    }
}
?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-dark table-hover mb-0">
                <thead>
                    <tr>
                        <th class="text-center"><?= $header[0] ?></th>
                        <th class="text-center"><?= $header[1] ?></th>
                        <th><?= $header[2] ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php for ($level = 1; $level <= 20; $level++) { ?>
                        <tr>
                            <th class="text-center" scope="row"><?= $data[$level][0] ?></th>
                            <td class="text-center"><?= $data[$level][1] ?></td>
                            <td><?= $data[$level][2] ?></td>
                        </tr>
                        <?php
                    } // end for
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
