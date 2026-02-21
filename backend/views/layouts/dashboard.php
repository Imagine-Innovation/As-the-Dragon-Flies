<?php

use backend\assets\AppAsset;
use frontend\widgets\Button;
use yii\helpers\Html;
use yii\helpers\Url;

/** @var \yii\web\View $this */
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" data-bs-theme="dark">

    <?= $this->renderFile('@app/views/layouts/snippets/head.php') ?>

    <?php if (1 === 2): ?>
        <head>
            <meta charset="<?= Yii::$app->charset ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
            <link href="favicon.png" rel="icon">
            <meta content="<?= Yii::$app->request->scriptUrl ?>" name="script-url">

            <?= $this->registerCsrfMetaTags() ?>

            <title><?= Html::encode($this->title) ?></title>

            <?php $this->head() ?>
            <style>
                :root {
                    --sidebar-width: 260px;
                    --sidebar-shrinked: 75px;
                }

                body {
                    overflow-x: hidden;
                }
                #sidebar {
                    width: var(--sidebar-width);
                    transition: width 0.2s ease-in-out;
                    z-index: 1000;
                }

                /* Automatic Shrink on Medium Screens (576px to 991px) */
                @media (min-width: 576px) and (max-width: 991.98px) {
                    #sidebar {
                        width: var(--sidebar-shrinked);
                    }
                    #sidebar .menu-text, #sidebar .sidebar-heading, #sidebar .logo-text {
                        display: none;
                    }
                    #sidebar .nav-link {
                        text-align: center;
                        padding-left: 0;
                        padding-right: 0;
                    }
                    #sidebar .nav-link i {
                        margin-right: 0 !important;
                        font-size: 1.3rem;
                    }
                }

                /* Manual Shrink class for the Toggle button */
                #sidebar.manual-shrink {
                    width: var(--sidebar-shrinked);
                }
                #sidebar.manual-shrink .menu-text, #sidebar.manual-shrink .sidebar-heading, #sidebar.manual-shrink .logo-text {
                    display: none;
                }
                #sidebar.manual-shrink .nav-link {
                    text-align: center;
                    padding-left: 0;
                    padding-right: 0;
                }
                #sidebar.manual-shrink .nav-link i {
                    margin-right: 0 !important;
                }

                /* Hide sidebar on Mobile (handled by Offcanvas) */
                @media (max-width: 575.98px) {
                    #sidebar {
                        display: none;
                    }
                }

                .kpi-icon {
                    font-size: 2.5rem;
                    opacity: 0.3;
                    position: absolute;
                    right: 15px;
                    bottom: 10px;
                }
                .card-kpi {
                    position: relative;
                    overflow: hidden;
                }
            </style>
        </head>
    <?php endif; ?>
    <body>

        <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title"><i class="bi bi-dragon me-2"></i>Admin Menu</h5>
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
                            <li class="nav-item px-3 text-secondary border-end d-none d-sm-block">Admin Panel v1.0</li>
                            <li class="nav-item px-2 ms-2"><a href="#"><i class="bi bi-person-circle fs-4 text-white"></i></a></li>
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
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="card card-kpi bg-primary bg-gradient text-white border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 opacity-75">Active Users</h6>
                                    <h2 class="card-title mb-0">1,240</h2>
                                    <i class="bi bi-people-fill kpi-icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="card card-kpi bg-success bg-gradient text-white border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 opacity-75">Active Players</h6>
                                    <h2 class="card-title mb-0">856</h2>
                                    <i class="bi bi-controller kpi-icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="card card-kpi bg-info bg-gradient text-white border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 opacity-75">Available Stories</h6>
                                    <h2 class="card-title mb-0">42</h2>
                                    <i class="bi bi-book-half kpi-icon"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-6 col-xl-3">
                            <div class="card card-kpi bg-danger bg-gradient text-white border-0 shadow-sm">
                                <div class="card-body">
                                    <h6 class="card-subtitle mb-2 opacity-75">Total Quests</h6>
                                    <h2 class="card-title mb-0">312</h2>
                                    <i class="bi bi-sword kpi-icon"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-12 col-xxl-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                                    <span class="fw-bold"><i class="bi bi-journal-check me-2"></i>Active Quests</span>
                                    <button class="btn btn-sm btn-link text-decoration-none">View All</button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Quest</th>
                                                <th>Progress</th>
                                                <th>Initiator</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>The Lost Mine of Phandelver</td>
                                                <td>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-primary" style="width: 75%"></div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-secondary">Gundren R.</span></td>
                                            </tr>
                                            <tr>
                                                <td>Slaying the Cryovain</td>
                                                <td>
                                                    <div class="progress" style="height: 8px;">
                                                        <div class="progress-bar bg-warning" style="width: 15%"></div>
                                                    </div>
                                                </td>
                                                <td><span class="badge bg-secondary">Townmaster Harbin</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-xxl-6">
                            <div class="card shadow-sm">
                                <div class="card-header bg-transparent border-bottom">
                                    <span class="fw-bold"><i class="bi bi-trophy me-2 text-warning"></i>Top 10 Players</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>Player</th>
                                                <th>Lvl</th>
                                                <th>Class</th>
                                                <th>Race</th>
                                                <th>XP</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td><i class="bi bi-person-square text-info me-2"></i><strong>Elara</strong></td>
                                                <td>12</td>
                                                <td><small>Wizard</small></td>
                                                <td><small>Elf</small></td>
                                                <td><span class="text-success">65k</span></td>
                                            </tr>
                                            <tr>
                                                <td><i class="bi bi-person-square text-danger me-2"></i><strong>Korg</strong></td>
                                                <td>11</td>
                                                <td><small>Fighter</small></td>
                                                <td><small>Orc</small></td>
                                                <td><span class="text-success">58k</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
    </body>
</html>
<?php $this->endPage() ?>
