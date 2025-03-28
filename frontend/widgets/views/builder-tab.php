<?php

use common\helpers\Utilities;
use frontend\widgets\ModalDesc;

/** @var yii\web\View $this */
/** @var common\models\xxx $models */
/** @var string $field_name */
/** @var string[] $paragraphs */
?>
<!-- Character Builder - <?= $field_name ?> BuilderTab Widget -->
<?= Utilities::formatMultiLine($paragraphs) ?>

<div class="container">
    <div class="row g-4">
        <?php foreach ($models as $model): ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="custom-control custom-radio card-title">
                            <input type="radio" id="<?= $field_name ?><?= $model->id ?>" name="<?= $field_name ?>" class="custom-control-input"
                                   onchange="PlayerBuilder.setProperty('<?= $field_name ?>_id', <?= $model->id ?>);">
                            <label class="custom-control-label" for="<?= $field_name ?><?= $model->id ?>"><?= $model->name ?></label>
                        </div>
                        <h6 class="card-subtitle text-muted">
                            <?=
                            ModalDesc::widget([
                                'name' => $model->name,
                                'description' => $model->description,
                                'maxLength' => 200,
                                'type' => $field_name,
                                'id' => $model->id,
                            ])
                            ?>
                        </h6>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
