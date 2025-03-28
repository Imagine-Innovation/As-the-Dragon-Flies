<?php

use common\helpers\Utilities;
use yii\helpers\Url;
use yii\helpers\HtmlPurifier;

/** @var yii\web\View $this */
/** @var common\models\Menu[] $menus */
/** @var array $debugMode */
$questId = Yii::$app->session->get('questId');
$quest = Yii::$app->session->get('currentQuest');
$story = $questId ? $quest->story : null;
?>
<div class="row g-4">
    <?php foreach ($menus as $menu): ?>
        <?php if ($menu->card_title): ?>
            <div class="col-12 col-md-6 col-lg-4 col-xl-4 col-xxl-3">
                <div class="card h-100">
                    <div class="actions">
                        <a href="<?= Url::toRoute($menu->accessRight->route . '/' . $menu->accessRight->action) ?>" class="actions__item position-relative">
                            <span data-toggle="tooltip" title="<?= $menu->tooltip ?>" data-placement="bottom">
                                <i class="bi <?= $menu->icon ?>"></i>
                            </span>
                        </a>
                    </div>
                    <a href="<?= Url::toRoute($menu->accessRight->route . '/' . $menu->accessRight->action) ?>">
                        <img class="card-img-top" src="<?= Utilities::toolImage($menu->image, $menu->is_context) ?>">
                    </a>
                    <div class="card-body">
                        <h4 class="card-title"><?= $menu->card_title ?></h4>
                        <h6 class="card-subtitle"><?= ($menu->is_context && $questId) ? $story->name : $menu->subtitle ?></h6>
                        <?= HtmlPurifier::process(($menu->is_context && $questId) ? $story->description : $menu->description) ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>