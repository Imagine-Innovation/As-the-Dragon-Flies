<?php

use common\helpers\Utilities;
use common\models\Menu;
use frontend\widgets\Button;
use yii\helpers\HtmlPurifier;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var Menu[] $menus */
$questId = Yii::$app->session->get('questId');
$quest = Yii::$app->session->get('currentQuest');
?>
<div class="row g-4">
    <?php foreach ($menus as $menu): ?>
        <?php



        if ($menu->card_title):
    
        $accessRight = $menu->accessRight;
    
        $route = "{$accessRight->route}/{$accessRight->action}";
    
        $href = Url::toRoute($route);
    
        ?>
            <div class="col-12 col-md-6 col-lg-4 col-xxl-3">
                <div class="card h-100">
                    <div class="actions">
                        <?=
        
        Button::widget([
            
        'mode' => 'icon',
            
        'url' => $href,
            
        'icon' => $menu->icon,
            
        'tooltip' => $menu->tooltip,
        
        ])
    
        ?>
                    </div>
                    <a href="<?= $href ?>">
                        <img class="card-img-top" src="<?= Utilities::toolImage($menu->image, $menu->is_context === 1) ?>">
                    </a>
                    <div class="card-body">
                        <h4 class="card-title"><?= $menu->card_title ?></h4>
                        <h6 class="card-subtitle"><?= $menu->is_context && $questId ? $quest->name : $menu->subtitle ?></h6>
        <?= HtmlPurifier::process($menu->is_context && $questId ? $quest->description : $menu->description) ?>
                        <p>
                            <a href="<?= $href ?>" role="button" class="btn btn-warning w-100 text-decoration">
                                <i class="bi <?= $menu->icon ?>"></i> <?= $menu->card_title ?>
                            </a>
                        </p>
                    </div>
                </div>
            </div>
    <?php endif; ?>
<?php endforeach; ?>
</div>
