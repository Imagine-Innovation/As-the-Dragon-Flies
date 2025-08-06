<?php

use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var integer $questId */
$stories = $dataProvider->getModels();
$user = Yii::$app->user->identity;
$player = Yii::$app->session->get('currentPlayer');
$quest = Yii::$app->session->get('quest');

$this->title = 'Stories';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="container-fluid">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">List of available stories to start a quest</h4>
            <?php if ($user->is_designer): ?>
                <div class="actions">
                    <a href="<?= Url::toRoute(['story/create']) ?>" class="actions__item position-relative">
                        <span data-bs-toggle="tooltip" title="Create a new story" data-placement="bottom"
                              <i class="bi bi-journal-plus"></i>
                        </span>
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($stories): ?>
                <div class="row g-4">
                    <?php foreach ($stories as $story): ?>
                        <div class="col-12 col-md-6 col-lg-4">
                            <?=
                            $this->renderFile('@app/views/story/snippets/card.php', [
                                'user' => $user,
                                'player' => $player,
                                'story' => $story,
                                'quest' => $quest,
                                'isDesigner' => $user->is_designer,
                                'isPlayer' => $user->is_player,
                            ])
                            ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                We're sorry. No story is available yet, but we're working on it!
            <?php endif; // if stories is not null    ?>
        </div>
    </div>
</div>
