<?php

use frontend\widgets\Button;
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
                    <?=
                    Button::widget([
                        'mode' => 'icon',
                        'url' => Url::toRoute(['story/create']),
                        'icon' => 'bi-journal-plus',
                        'tooltip' => "Create a new story"
                    ])
                    ?>
                </div>
            <?php endif; ?>
            <?php if ($stories): ?>
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4">
                    <?php foreach ($stories as $story): ?>
                        <div class="col">
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
            <?php endif; // if stories is not null     ?>
        </div>
    </div>
</div>
