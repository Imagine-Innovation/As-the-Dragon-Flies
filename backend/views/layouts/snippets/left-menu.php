<?php

use common\components\AccessRightsManager;
use yii\helpers\Url;

/** @var \yii\web\View $this */
/** @var string|null $currentMenu */
$menuConfig = [
    'void' => [// No heading for the first group
        ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'site/index'],
    ],
    'Admin' => [
        ['label' => 'DbMonitor', 'icon' => 'bi-database-fill-exclamation', 'route' => 'db-monitor/index'],
        ['label' => 'Access Rights', 'icon' => 'bi-shield-check', 'route' => 'access-right/index'],
        ['label' => 'Users', 'icon' => 'bi-people', 'route' => 'user/index'],
        ['label' => 'Players', 'icon' => 'bi-person-badge', 'route' => 'player/index'],
    ],
    'Design' => [
        ['label' => 'Stories', 'icon' => 'bi-journal-text', 'route' => 'story/index'],
        ['label' => 'Quests', 'icon' => 'bi-flag', 'route' => 'quest/index'],
        ['label' => 'Icons', 'icon' => 'bi-bootstrap', 'route' => 'site/icons'],
        ['label' => 'Fonts', 'icon' => 'bi-fonts', 'route' => 'site/fonts'],
        ['label' => 'Colors', 'icon' => 'bi-palette', 'route' => 'site/colors'],
    ],
    'Homebrew' => [
        ['label' => 'Items', 'icon' => 'bi-box-seam', 'route' => 'item/index'],
        ['label' => 'Spells', 'icon' => 'bi-magic', 'route' => 'spell/index'],
        ['label' => 'Creatures', 'icon' => 'bi-bug', 'route' => 'creature/index'],
        ['label' => 'Images', 'icon' => 'bi-images', 'route' => 'image/index'],
    ],
    'Resources' => [
        ['label' => 'Abilities', 'icon' => 'bi-lightning-charge', 'route' => 'ability/index'],
        ['label' => 'Skills', 'icon' => 'bi-stars', 'route' => 'skill/index'],
        ['label' => 'Languages', 'icon' => 'bi-translate', 'route' => 'language/index'],
        ['label' => 'Races', 'icon' => 'bi-person-gear', 'route' => 'race/index'],
        ['label' => 'Classes', 'icon' => 'bi-shield-shaded', 'route' => 'character-class/index'],
    ],
];

$allowedMenus = [];

foreach ($menuConfig as $chapter => $menus) {
    $chapterMenu = [];
    foreach ($menus as $menu) {
        $url = explode('/', $menu['route']);
        $accessRight = AccessRightsManager::getAccessRight(AccessRightsManager::APP_BACKEND, $url[0], $url[1]);
        if (!empty($accessRight) || AccessRightsManager::isPublic($url[0], $url[1])) {
            $chapterMenu[] = $menu;
        }
    }
    if (!empty($chapterMenu)) {
        $allowedMenus[$chapter] = $chapterMenu;
    }
}
$chapters = array_keys($allowedMenus);
$firstHeading = $chapters[0];
$currentMenu = $currentMenu ?? $firstHeading;
?>

<nav id="sidebar" class="border-end min-vh-100 d-none d-sm-block">
    <div class="p-3 border-bottom d-flex align-items-center justify-content-center justify-content-lg-start">
        <i class="bi dnd-logo h2"></i>
        <span class="fw-bold fs-5 ms-2 logo-text"><?= Yii::$app->name ?></span>
    </div>

    <div id="mainNavContent" class="p-3 text-warning">
        <?php foreach ($allowedMenus as $chapter => $menus): ?>

            <?= ($firstHeading === $chapter) ? '' : '<hr>' ?>

            <?php if ($chapter !== 'void'): ?>
                <small class="text-muted text-uppercase fw-bold sidebar-heading"><?= $chapter ?></small>
            <?php endif; ?>

            <ul class="nav nav-pills flex-column mb-auto">
                <?php foreach ($menus as $menu): ?>
                    <li class="nav-item">
                        <a href="<?= Url::toRoute($menu['route']) ?>" class="nav-link <?=
                        ($menu['label'] === $currentMenu) ? 'active' : ''
                        ?>">
                            <span class="text-warning-emphasis">
                                <i class="bi <?= $menu['icon'] ?> me-2"></i>
                                <span class="menu-text"><?= $menu['label'] ?></span>
                            </span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endforeach; ?>
    </div>
</nav>
