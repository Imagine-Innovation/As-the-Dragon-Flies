<?php

use common\helpers\Utilities;
use frontend\widgets\Button;
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
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                <div class="card h-100">
                    <div class="actions">
                        <?=
                        Button::widget([
                            'mode' => 'icon',
                            'url' => Url::toRoute($menu->accessRight->route . '/' . $menu->accessRight->action),
                            'icon' => $menu->icon,
                            'tooltip' => $menu->tooltip
                        ])
                        ?>
                    </div>
                    <a href="<?= Url::toRoute($menu->accessRight->route . '/' . $menu->accessRight->action) ?>">
                        <img class="card-img-top" src="<?= Utilities::toolImage($menu->image, $menu->is_context) ?>">
                    </a>
                    <div class="card-body">
                        <h4 class="card-title"><?= $menu->card_title ?></h4>
                        <h6 class="card-subtitle"><?= ($menu->is_context && $questId) ? $story->name : $menu->subtitle ?></h6>
                        <?= HtmlPurifier::process(($menu->is_context && $questId) ? $story->description : $menu->description) ?>
                        <p>
                            <a href="<?= Url::toRoute($menu->accessRight->route . '/' . $menu->accessRight->action) ?>" role="button" class="btn btn-warning w-100 text-decoration">
                                <i class="bi <?= $menu->icon ?>"></i> <?= $menu->card_title ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
