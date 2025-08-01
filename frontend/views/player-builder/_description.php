<?php

use common\models\Alignment;
use common\helpers\Utilities;

/** @var yii\web\View $this */
/** @var common\models\PlayerBuilder $model */
/** @var string[] $paragraphs */
$alignments = Alignment::find()->all();
/*
  $alignmentCol = ['Lawful', 'Neutral', 'Chaotic'];
  $alignmentRow = ['Good', 'Neutral', 'Evil'];
 *
 */
$alignmentCol = ['L', 'N', 'C'];
$alignmentRow = ['G', 'N', 'E'];
$alignmentIds = [];

foreach ($alignments as $alignment) {
    $code = $alignment->code;
    if ($code === "N") {
        $col = "N";
        $row = "N";
    } else {
        $col = substr($code, 0, 1);
        $row = substr($code, -1);
    }
    $alignmentIds[$col][$row] = $alignment->id;
}
?>
<!-- Character Builder - Description Tab -->
<?= Utilities::formatMultiLine($paragraphs) ?>

<div class="container">
    <div class="row g-0">
        <div class="col-12 col-lg-6 col-xl-4">
            <!-- Gender -->
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title text-decoration">Gender</h4>
                    <div class="custom-control custom-radio custom-control-inline mb-2">
                        <input type="radio" id="genderM" name="gender" class="custom-control-input"
                               onchange='PlayerBuilder.setProperty("gender", "M");'>
                        <label class="custom-control-label" for="genderM">Male</label>
                    </div>
                    <div class="custom-control custom-radio custom-control-inline mb-2">
                        <input type="radio" id="genderF" name="gender" class="custom-control-input"
                               onchange='PlayerBuilder.setProperty("gender", "F");'>
                        <label class="custom-control-label" for="genderF">Female</label>
                    </div>
                </div>
            </div>

            <!-- Age -->
            <div class="card">
                <div class="card-body" id="ajaxAgeSelection">
                    <h4 class="card-title text-decoration">Age</h4>
                    <h6 class="card-subtitle">Each race has its own age range. Please select a race before choosing an age.</h6>
                </div>
            </div>
        </div>

        <!-- Name -->
        <div class="col-12 col-lg-6 col-xl-8">
            <div class="row row-cols-1 row-cols-xl-2 g-0">
                <div class="col">
                    <div class="card">
                        <div class="actions">
                            <a href="#" class="actions__item bi-arrow-repeat"></a>
                        </div>
                        <div class="card-body" id="ajaxNameSelection">
                            <h4 class="card-title text-decoration">Name</h4>
                            <?php if ($model->name): ?>
                                <h6 class="card-subtitle">Your player is called "<?= $model->name ?>"</h6>
                            <?php else: ?>
                                <h6 class="card-subtitle">Select a race and a gender before selecting a name.</h6>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Alignment -->
                <div class="col">
                    <div class="card">
                        <div class="actions">
                            <a href="#" id="showBuilderWizardModal-button" class="actions__item" data-bs-toggle="tooltip" title="Alignment wizard" data-placement="bottom">
                                <span data-bs-toggle="modal" data-bs-target="#builderWizardModal">
                                    <i class="bi bi-magic"></i>
                                </span>
                            </a>
                        </div>
                        <div class="card-body">
                            <h4 class="card-title text-decoration">Alignment</h4>
                            <div class="table-responsive">
                                <table class="table table-dark table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>&nbsp;</th>
                                            <?php foreach ($alignmentCol as $col): ?>
                                                <th class="text-center"><?= $col ?></th>
                                            <?php endforeach; ?>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($alignmentRow as $row): ?>
                                            <tr>
                                                <th scope="row"><?= $row ?></th>
                                                <?php foreach ($alignmentCol as $col): ?>
                                                    <td class="text-center">
                                                        <div class="custom-control custom-radio mb-2">
                                                            <input type="radio" id="alignment<?= $alignmentIds[$col][$row] ?>" name="alignment" class="custom-control-input"
                                                                   onchange='PlayerBuilder.setProperty("alignment_id", <?= $alignmentIds[$col][$row] ?>);'>
                                                            <label class="custom-control-label" for="alignment<?= $alignmentIds[$col][$row] ?>"> </label>
                                                        </div>
                                                    </td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
