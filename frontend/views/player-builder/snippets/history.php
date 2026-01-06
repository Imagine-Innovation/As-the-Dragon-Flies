<?php

use common\helpers\Utilities;
use frontend\widgets\ModalDesc;

/** @var yii\web\View $this */
/** @var frontend\models\PlayerBuilder $model */
/** @var string[] $paragraphs */
$field_name = 'description';
$histories = common\models\History::find()->all();
?>
<!-- Character Builder - <?= $field_name ?> BuilderTab Widget -->
<?= Utilities::formatMultiLine($paragraphs) ?>

<div class="container">
    <div class="row g-4">
        <?php foreach ($histories as $history): ?>
            <div class="col-12 col-md-6 col-lg-4 col-xl-3">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="custom-control custom-radio card-title">
                            <input type="radio" id="<?= $field_name ?><?= $history->id ?>" name="<?= $field_name ?>" class="custom-control-input"
                                   onchange="PlayerBuilder.setProperty('description', `<?= Utilities::encode($history->description ?? '') ?>`);">
                            <label class="custom-control-label text-decoration" for="<?= $field_name ?><?= $history->id ?>"><?= $history->name ?></label>
                        </div>
                        <h6 class="card-subtitle text-muted">
                            <?=
                            ModalDesc::widget([
                                'name' => $history->name,
                                'description' => $history->description,
                                'maxLength' => 200,
                                'type' => $field_name,
                                'id' => $history->id,
                            ])
                            ?>
                        </h6>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
