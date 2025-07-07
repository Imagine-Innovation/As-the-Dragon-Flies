<?php

use common\helpers\Utilities;

/** @var yii\web\View $this */
/** @var common\models\PlayerBuilder $model */
/** @var string[] $paragraphs */
?>
<!-- Character Builder - Skills Tab -->
<?= Utilities::formatMultiLine($paragraphs) ?>

<div class="container-fluid">
    <div class="row g-4">
        <div class="col-12 col-sm-6">
            <div class="card h-100">
                <div class="actions">
                    <a href="#" class="actions__item bi bi-arrow-repeat"></a>
                </div>
                <div class="card-body" id="ajaxTraits">
                    <?= $this->renderFile('@app/views/player-builder/ajax-traits.php', ['player' => $model]) ?>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="card h-100">
                <div class="card-body" id="ajaxSkills">
                    <h4 class="card-title text-decoration">Skills</h4>
                </div>
            </div>
        </div>
    </div>
</div>
