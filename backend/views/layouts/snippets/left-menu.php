<?php

use yii\helpers\Url;

/** @var \yii\web\View $this */
/** @var string|null $currentMenu */
$menuConfig = [
    'void' => [// No heading for the first group
        ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'url' => ['/site/index'], 'admin' => false],
        ['label' => 'DbMonitor', 'icon' => 'bi-speedometer2', 'url' => ['/db-monitor/index'], 'admin' => true],
    ],
    'Admin' => [
        ['label' => 'Users', 'icon' => 'bi-people', 'url' => ['/user/index'], 'admin' => true],
        ['label' => 'Players', 'icon' => 'bi-person-badge', 'url' => ['/player/index'], 'admin' => true],
        ['label' => 'Stories', 'icon' => 'bi-journal-text', 'url' => ['/story/index'], 'admin' => false],
        ['label' => 'Quests', 'icon' => 'bi-flag', 'url' => ['/quest/index'], 'admin' => false],
        ['label' => 'Icons', 'icon' => 'bi-bootstrap', 'url' => ['/site/icons'], 'admin' => true],
        ['label' => 'Fonts', 'icon' => 'bi-fonts', 'url' => ['/site/fonts'], 'admin' => true],
        ['label' => 'Colors', 'icon' => 'bi-palette', 'url' => ['/site/colors'], 'admin' => true],
    ],
    'Homebrew' => [
        ['label' => 'Items', 'icon' => 'bi-box-seam', 'url' => ['/item/index'], 'admin' => false],
        ['label' => 'Spells', 'icon' => 'bi-magic', 'url' => ['/spell/index'], 'admin' => false],
        ['label' => 'Creatures', 'icon' => 'bi-bug', 'url' => ['/creature/index'], 'admin' => false],
        ['label' => 'Images', 'icon' => 'bi-images', 'url' => ['/image/index'], 'admin' => true],
    ],
    'Resources' => [
        ['label' => 'Abilities', 'icon' => 'bi-lightning-charge', 'url' => ['/ability/index'], 'admin' => false],
        ['label' => 'Skills', 'icon' => 'bi-stars', 'url' => ['/skill/index'], 'admin' => false],
        ['label' => 'Languages', 'icon' => 'bi-translate', 'url' => ['/language/index'], 'admin' => false],
        ['label' => 'Races', 'icon' => 'bi-person-gear', 'url' => ['/race/index'], 'admin' => false],
        ['label' => 'Classes', 'icon' => 'bi-shield-shaded', 'url' => ['/class/index'], 'admin' => false],
    ],
];

$headings = array_keys($menuConfig);
$firstHeading = $headings[0];
$currentMenu = $currentMenu ?? $firstHeading;

$currentUser = Yii::$app->user->identity;
$isAdmin = $currentUser->is_admin;
?>

<nav id="sidebar" class="bg-body-tertiary border-end min-vh-100 d-none d-sm-block">
    <div class="p-3 border-bottom d-flex align-items-center justify-content-center justify-content-lg-start">
        <i class="bi dnd-logo h2"></i>
        <span class="fw-bold fs-5 ms-2 logo-text"><?= Yii::$app->name ?></span>
    </div>

    <div id="mainNavContent" class="p-3">
        <?php foreach ($menuConfig as $heading => $menuItems): ?>

            <?= ($firstHeading === $heading) ? '' : '<hr>' ?>

            <?php if ($heading !== 'void'): ?>
                <small class="text-muted text-uppercase fw-bold sidebar-heading"><?= $heading ?></small>
            <?php endif; ?>

            <ul class="nav nav-pills flex-column mb-auto">
                <?php foreach ($menuItems as $menuItem): ?>
                    <?php if (($menuItem['admin'] && $isAdmin) || (!$menuItem['admin'])): ?>
                        <li class="nav-item">
                            <a href="<?= Url::to($menuItem['url']) ?>" class="nav-link
                            <?=
                            ($menuItem['label'] === $currentMenu) ? 'active' : ''
                            ?>">
                                <i class="bi <?= $menuItem['icon'] ?> me-2"></i>
                                <span class="menu-text"><?= $menuItem['label'] ?></span>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </div>
</nav>
