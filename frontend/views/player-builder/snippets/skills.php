<?php

use common\helpers\Utilities;
use frontend\widgets\Button;

/** @var yii\web\View $this */
/** @var frontend\models\PlayerBuilder $model */
/** @var string[] $paragraphs */
?>
<!-- Character Builder - Skills Tab -->
<?= Utilities::formatMultiLine($paragraphs) ?>

<div class="container-fluid">
    <div class="row g-4">
        <div class="col-12 col-sm-6">
            <div class="card h-100">
                <div class="actions">
                    <?=
    Button::widget([
        'mode' => 'icon',
        'id' => 'generateNewTraitsButton',
        'icon' => 'bi-arrow-repeat',
        'tooltip' => 'New random traits',
    ])
?>
                </div>
                <div class="card-body" id="ajaxTraits">
                    <?= $this->renderFile('@app/views/player-builder/ajax/traits.php', ['player' => $model]) ?>
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
