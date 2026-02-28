<?php

use backend\assets\AppAsset;
use backend\helpers\KpiHelper;
use common\widgets\Button;
use common\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \yii\web\View $this */
/** @var string $content */
AppAsset::register($this);

$currentUser = Yii::$app->user->identity;
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" data-bs-theme="dark">

    <?= $this->renderFile('@app/views/layouts/snippets/head.php') ?>
    <?php $this->beginBody(); ?>
    <body>

        <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title">
                    <img src="Dragonfly32White.png" alt="<?= Yii::$app->name ?>">
                    <?= Yii::$app->name ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body p-0" id="mobileSidebarContent">
            </div>
        </div>

        <div class="d-flex">
            <?= $this->renderFile('@app/views/layouts/snippets/left-menu.php') ?>

            <div class="flex-grow-1">
                <header class="navbar navbar-expand bg-body-tertiary border-bottom sticky-top">
                    <div class="container-fluid">
                        <button class="btn btn-sm btn-outline-secondary d-none d-lg-inline-block" id="sidebarToggle">
                            <i class="bi bi-list"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                            <i class="bi bi-list"></i>
                        </button>

                        <ul class="navbar-nav ms-auto align-items-center">
                            <li class="nav-item px-3 text-secondary border-end d-none d-sm-block">
                                <?= $currentUser->fullname ?? $currentUser->username ?>
                            </li>
                            <li class="nav-item px-2">
                                <?=
                                Button::widget([
                                    'isPost' => true,
                                    'url' => Url::toRoute(['site/logout']),
                                    'icon' => 'dnd-power-off',
                                    'tooltip' => 'logout',
                                ])
                                ?>
                            </li>
                        </ul>
                    </div>
                </header>

                <main class="p-4">
                    <section class="content">
                        <a id="top"></a>
                        <?=
                        Breadcrumbs::widget([
                            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs']
                                        : [],
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
                </main>
            </div>
        </div>

    </body>
    <?php $this->endBody(); ?>

    <script type="text/javascript">
        const kpiManager = new KpiManager(60);
        kpiManager.init();

    </script>

    <script>
        // Sidebar manual toggle for Large screens
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('manual-shrink');
        });

        // Clone menu for mobile offcanvas on load
        document.getElementById('mobileSidebarContent').innerHTML = document.getElementById('mainNavContent').innerHTML;
    </script>
</html>
<?php $this->endPage() ?>
