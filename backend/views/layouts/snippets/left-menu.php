<?php

use common\components\AccessRightsManager;
use yii\helpers\Url;

/** @var \yii\web\View $this */
/** @var string|null $currentMenu */
$menuConfig = [
    'void' => [// No heading for the first group
        ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'url' => 'site/index'],
        ['label' => 'DbMonitor', 'icon' => 'bi-speedometer2', 'url' => 'db-monitor/index'],
    ],
    'Admin' => [
        ['label' => 'Access Rights', 'icon' => 'bi-shield-check', 'url' => 'access-right/index'],
        ['label' => 'Users', 'icon' => 'bi-people', 'url' => 'user/index'],
        ['label' => 'Players', 'icon' => 'bi-person-badge', 'url' => 'player/index'],
        ['label' => 'Stories', 'icon' => 'bi-journal-text', 'url' => 'story/index'],
        ['label' => 'Quests', 'icon' => 'bi-flag', 'url' => 'quest/index'],
        ['label' => 'Icons', 'icon' => 'bi-bootstrap', 'url' => 'site/icons'],
        ['label' => 'Fonts', 'icon' => 'bi-fonts', 'url' => 'site/fonts'],
        ['label' => 'Colors', 'icon' => 'bi-palette', 'url' => 'site/colors'],
    ],
    'Homebrew' => [
        ['label' => 'Items', 'icon' => 'bi-box-seam', 'url' => 'item/index'],
        ['label' => 'Spells', 'icon' => 'bi-magic', 'url' => 'spell/index'],
        ['label' => 'Creatures', 'icon' => 'bi-bug', 'url' => 'creature/index'],
        ['label' => 'Images', 'icon' => 'bi-images', 'url' => 'image/index'],
    ],
    'Resources' => [
        ['label' => 'Abilities', 'icon' => 'bi-lightning-charge', 'url' => 'ability/index'],
        ['label' => 'Skills', 'icon' => 'bi-stars', 'url' => 'skill/index'],
        ['label' => 'Languages', 'icon' => 'bi-translate', 'url' => 'language/index'],
        ['label' => 'Races', 'icon' => 'bi-person-gear', 'url' => 'race/index'],
        ['label' => 'Classes', 'icon' => 'bi-shield-shaded', 'url' => 'character-class/index'],
    ],
];

$allowedMenus = [];

foreach ($menuConfig as $chapter => $menus) {
    $chapterMenu = [];
    foreach ($menus as $menu) {
        $url = explode('/', $menu['url']);
        $accessRight = AccessRightsManager::checkAccess($url[0], $url[1]);
        if ($accessRight['denied'] === false) {
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
                        <a href="<?= Url::toRoute($menu['url']) ?>" class="nav-link <?=
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
