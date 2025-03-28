<?php
/** @var \yii\web\View $this */

/** @var string $content */
use common\widgets\Alert;
use frontend\assets\AppAsset;
use yii\bootstrap5\Breadcrumbs;

AppAsset::register($this);
?>
<!--Main Navigation-->
<?= $this->renderFile('@app/views/layouts/_navbar-lobby.php') ?>
<!-- End Main Navigation-->

<section class="content">
    <a id="top"></a>
    <?=
    Breadcrumbs::widget([
        'links' => isset($this->params['breadcrumbs']) ?
                $this->params['breadcrumbs'] : [],
    ])
    ?>

    <div class="content__inner">
        <?= Alert::widget() ?>

        <?= $content ?>
    </div>

    <!-- Toast Markup -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast-wrapper">
            <div id="toastContainer"></div>
        </div>
    </div>

</section>
