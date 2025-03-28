<?php
/** @var \yii\web\View $this */
/** @var string $content */
$n = 27;    // Number of images to be displayed within the carousel
?>
<div class="carousel slide carousel-fade" data-ride="carousel" style="position: fixed;left: 0px;top: 0px;z-index:-1;width: 100%;">
    <div class="carousel-inner" role="listbox">
        <div class="carousel-item active">
            <img src="img/carousel/car1.jpg" alt="First slide">
        </div>
        <?php for ($img = 2; $img <= $n; $img++): ?>
            <div class="carousel-item">
                <img src="img/carousel/car<?= $img ?>.jpg">
            </div>
        <?php endfor; ?>
    </div>
</div>
