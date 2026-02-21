<?php

/** @var \yii\web\View $this */
use yii\helpers\Url;
?>
<nav id="sidebar" class="bg-body-tertiary border-end min-vh-100 d-none d-sm-block">
    <div class="p-3 border-bottom d-flex align-items-center justify-content-center justify-content-lg-start">
        <img src="Dragonfly32White.png" alt="<?= Yii::$app->name ?>">
        <span class="fw-bold fs-5 ms-2 logo-text"><?= Yii::$app->name ?></span>
    </div>

    <div id="mainNavContent" class="p-3">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item"><a href="#" class="nav-link active"><i class="bi bi-speedometer2 me-2"></i><span class="menu-text">Dashboard</span></a></li>
            <li><a href="#" class="nav-link text-white"><i class="bi bi-people me-2"></i><span class="menu-text">Users</span></a></li>
            <li><a href="#" class="nav-link text-white"><i class="bi bi-person-badge me-2"></i><span class="menu-text">Players</span></a></li>
            <li><a href="#" class="nav-link text-white"><i class="bi bi-journal-text me-2"></i><span class="menu-text">Stories</span></a></li>
            <li><a href="#" class="nav-link text-white"><i class="bi bi-flag me-2"></i><span class="menu-text">Quests</span></a></li>
        </ul>
        <hr>
        <small class="text-muted text-uppercase fw-bold sidebar-heading">Resources</small>
        <ul class="nav nav-pills flex-column">
            <li><a href="#" class="nav-link text-white"><i class="bi bi-lightning-charge me-2"></i><span class="menu-text">Abilities</span></a></li>
            <li><a href="#" class="nav-link text-white"><i class="bi bi-stars me-2"></i><span class="menu-text">Skills</span></a></li>
            <li><a href="#" class="nav-link text-white"><i class="bi bi-translate me-2"></i><span class="menu-text">Languages</span></a></li>        </ul>
        <hr>
        <small class="text-muted text-uppercase fw-bold sidebar-heading">Foundation</small>
        <ul class="nav nav-pills flex-column">
            <li><a href="#" class="nav-link text-white"><i class="bi bi-person-gear me-2"></i><span class="menu-text">Races</span></a></li>
            <li><a href="#" class="nav-link text-white"><i class="bi bi-shield-shaded me-2"></i><span class="menu-text">Classes</span></a></li>
        </ul>
        <hr>
        <small class="text-muted text-uppercase fw-bold sidebar-heading">Config</small>
        <ul class="nav nav-pills flex-column">
            <li><a href="#" class="nav-link text-white"><i class="bi bi-box-seam me-2"></i><span class="menu-text">Items</span></a></li>
            <li><a href="#" class="nav-link text-white"><i class="bi bi-magic me-2"></i><span class="menu-text">Spells</span></a></li>
            <li><a href="#" class="nav-link text-white"><i class="bi bi-bug me-2"></i><span class="menu-text">Creatures</span></a></li>
            <li><a href="#" class="nav-link text-white"><i class="bi bi-images me-2"></i><span class="menu-text">Images</span></a></li>
        </ul>
    </div>
</nav>
