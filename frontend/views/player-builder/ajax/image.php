<?php
/** @var yii\web\View $this */
/** @var int $imageId */
/** @var common\models\Image[] $images */
?>
<h4 class="card-title text-decoration">Images</h4>
<div class="row g-4">
    <?php foreach ($images as $image): ?>
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <img src="img/character/<?= $image->file_name ?>" style="max-width: 150px;">
                    <div class="custom-control custom-radio card-title">
                        <input type="radio" id="imageId<?= $image->id ?>" name="imageId" class="custom-control-input"
                        <?= ($imageId && $imageId === $image->id) ? "checked" : '' ?>
                               onchange="PlayerBuilder.setProperty('image_id', '<?= $image->id ?>');">
                        <label class="custom-control-label" for="imageId<?= $image->id ?>"></label>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>
