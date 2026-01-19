<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Menu[] $menus */
?>
<li class="dropdown top-nav__notifications">
    <a href="#" data-bs-toggle="dropdown">
        <i class="bi bi-grid"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-right dropdown-menu--block" role="menu">
        <div class="row app-shortcuts">
            <?php foreach ($menus as $menu): ?>
                <a class="col-3 app-shortcuts__item" href="<?= Url::toRoute($menu->accessRight->route . '/' . $menu->accessRight->action) ?>">
                    <i class="bi <?= $menu->icon ?>"></i>
                    <small><?= $menu->label ?></small>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</li>
