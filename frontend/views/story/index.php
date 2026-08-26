<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var common\models\Story[] $stories */
/** @var common\models\Player $player */
/** @var common\models\Quest $quest */
/** @var string|null $langFilter */
$user = Yii::$app->user->identity;

$this->title = 'Stories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <div class="actions">
                <div class="form-group mb-0 d-flex align-items-center">
                    <span class="me-2"><?= Yii::t('app', 'Any language') ?></span>
                    <div class="toggle-switch">
                        <input type="checkbox" class="toggle-switch__checkbox" id="lang-filter-toggle"
                        <?= $langFilter === Yii::$app->language ? 'checked' : '' ?>
                               onchange="if (this.checked) {
                                           window.location.href = '<?= Url::to(['story/index', 'lang' => Yii::$app->language]) ?>';
                                       } else {
                                           window.location.href = '<?= Url::to(['story/index', 'lang' => '']) ?>';
                                       }">
                        <i class="toggle-switch__helper"></i>
                    </div>
                    <span class="ms-2"><?= Yii::t('app', 'Current language') ?></span>
                </div>
            </div>
            <h4 class="card-title">List of available stories to start a quest</h4>
            <?php if ($stories): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-3xl-4 g-4">
                    <?php foreach ($stories as $story): ?>
                        <div class="col">
                            <?=
                            $this->renderFile('@app/views/story/snippets/card.php', [
                                'user' => $user,
                                'player' => $player,
                                'story' => $story,
                                'quest' => $quest,
                            ])
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                We're sorry. No story is available yet, but we're working on it!
            <?php endif; // if stories is not null       ?>
        </div>
    </div>
</div>
