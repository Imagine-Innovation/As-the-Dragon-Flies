<?php

use common\models\RaceGroup;
use common\helpers\Utilities;
use frontend\widgets\ModalDesc;
use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\PlayerBuilder $model */
/** @var string[] $paragraphs */
$raceGroups = RaceGroup::find()->all();
?>
<!-- Character Builder - Races Tab -->
<?= Utilities::formatMultiLine($paragraphs) ?>

<div class="container">
    <div class="row g-4">
        <?php foreach ($raceGroups as $raceGroup): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="card-title"><?= $raceGroup->name ?></h4>
                        <h6 class="card-subtitle text-muted">
                            <?=
                            ModalDesc::widget([
                                'name' => $raceGroup->name,
                                'description' => $raceGroup->description,
                                'maxLength' => 200,
                                'type' => 'race-group',
                                'id' => $raceGroup->id,
                            ])
                            ?>
                        </h6>

                        <?php foreach ($raceGroup->races as $race): ?>
                            <div class="custom-control custom-radio mb-2">
                                <input type="radio" id="race<?= $race->id ?>" name="race" class="custom-control-input"
                                       onchange='PlayerBuilder.setProperty("race_id", "<?= $race->id ?>");'>
                                <label class="custom-control-label" for="race<?= $race->id ?>"><?= $race->name ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
