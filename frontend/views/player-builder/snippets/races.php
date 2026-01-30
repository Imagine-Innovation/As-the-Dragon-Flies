<?php

use common\helpers\Utilities;
use common\models\RaceGroup;
use frontend\widgets\ModalDesc;

/** @var yii\web\View $this */
/** @var frontend\models\PlayerBuilder $model */
/** @var string[] $paragraphs */
$raceGroups = RaceGroup::find()->all();
?>
<!-- Character Builder - Races Tab -->
<?= Utilities::formatMultiLine($paragraphs) ?>

<div class="container">
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 row-cols-3xl-6 g-4">
        <?php foreach ($raceGroups as $raceGroup): ?>
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h4 class="card-title text-decoration"><?= $raceGroup->name ?></h4>
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
