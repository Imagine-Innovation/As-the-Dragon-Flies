<?php
/** @var \yii\web\View $this */

/** @var string $content */
use common\widgets\Alert;
use frontend\assets\AppAsset;

AppAsset::register($this);
?>
<!--Main Navigation-->
<?= $this->renderFile('@app/views/layouts/_navbar-game.php') ?>
<!-- End Main Navigation-->

<section class="virtual-tabletop">
    <?= Alert::widget() ?>

    <?= $content ?>

    <!-- Toast Markup -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast-wrapper">
            <div id="toastContainer"></div>
        </div>
    </div>

</section>
