<?php

use frontend\widgets\AjaxContainer;

/** @var yii\web\View $this */
/** @var common\models\Quest $model */
$this->title = $model->story->name;
$this->params['breadcrumbs'][] = ['label' => 'Quests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
//$user = Yii::$app->user->identity;
//$user = Yii::$app->session->get('user');
//$playerId = $user->current_player_id;
$playerId = Yii::$app->session->get('playerId');
?>
<script src="js/atdf-quest-manager.js"></script>
<div class="container-flex">
    <div id="questView">
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title text-decoration">Welcome to our tavern, your friends are waiting for you!</h4>
                    </div>
                    <img class="card-img-top" src="img/story/Tavern<?= mt_rand(1, 3) ?>.png"/>
                    <div class="card-body">
                        <p class="card-text">
                            The tavern is where all the adventurers meet before embarking on the quest.
                        </p>
                        <p class="card-text">
                            The quest can be started as soon as all the conditions have been met and the minimum number of players has entered the tavern.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <?=
                $this->renderFile('@app/views/quest/_chat.php', [
                    'questId' => $model->id,
                    'playerId' => $playerId
                ])
                ?>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <h4 class="card-title" id="tavernWelcomeMessage"></h4>
                <div class="actions">
                    <a class="actions__item bi-chat-left-dots" type="button" data-bs-toggle="modal" data-bs-target="#questChatModal"></a>
                </div>
                <?= AjaxContainer::widget(['name' => 'questTavernPlayersContainer']) ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="questChatModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h6 class="modal-title">Quest chat</h6>
                </div>
                <div class="modal-body">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-theme btn--icon" data-bs-dismiss="modal">
                        <i class="bi bi-x-square"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
