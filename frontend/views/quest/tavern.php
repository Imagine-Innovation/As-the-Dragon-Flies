<?php

use frontend\components\QuestMessages;
use frontend\widgets\AjaxContainer;

/** @var yii\web\View $this */
/** @var common\models\Quest $model */
$player = Yii::$app->session->get('currentPlayer');
$playerId = $player->id;
$playerName = $player->name;
$avatar = $player->image->file_name;

$questName = $model->story->name;

$this->title = $questName;
$this->params['breadcrumbs'][] = ['label' => 'Quests', 'url' => ['index']];
$this->params['breadcrumbs'][] = $questName;
$messages = QuestMessages::getLastMessages($model->id, $playerId);
?>
<div class="row g-3">
    <!-- Quest Panel -->
    <div class="col-md-6 col-xl-8">
        <div class="card p-4 mb-3">
            <div class="card-header">
                <h5 class="text-decoration">Welcome <?= $playerName ?> in <?= $questName ?> Quest</h5>
                <p class="text-decoration mb-3" id="tavernWelcomeMessage"></p>
            </div>
            <div class="card-body">
                <p class="text-decoration mb-3"><?= $model->story->description ?></p>
                <p class="mb-3">This quest allows <?= $model->story->companySize ?> <?= strtolower($model->story->requiredLevels) ?> to take part in the game.</p>
                <p class="mb-3" id="tavernMissingPlayers"></p>
                <p class="mb-0" id="tavernMissingClasses"></p>
            </div>
        </div>

        <!-- Party Panel -->
        <div class="card p-4">
            <div class="card-header">
                <h5 class="text-decoration">The adventuring companionship that is building up</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <?= AjaxContainer::widget(['name' => 'tavernPlayersContainer']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-4">
        <?= $this->renderFile('@app/views/quest/_chat.php', ['questId' => $model->id, 'playerId' => $playerId]) ?>
    </div>
    <?php if (1 === 2) : ?>
        <!-- Chat Panel -->
        <div class="col-md-6 col-xl-4">
            <div class="chat-panel-container">
                <div class="card p-4 h-100 d-flex flex-column">
                    <div class="card-header">
                        <h5 class="text-decoration">Tavern Chat</h5>
                    </div>

                    <div class="mt-auto">
                        <form id="questChatMessageForm">
                            <div class="input-group">
                                <input type="text" class="form-control" id="questChatInput" placeholder="Type your message...">
                                <button id="sendChatMessageButton" class="btn btn-primary" type="button">Send</button>
                            </div>
                        </form>
                        <small class="text-muted mt-2 d-block">Press Enter to send • Be respectful to fellow adventurers</small>
                    </div>

                    <div class="card-body overflow-auto flex-grow-1 mb-3">
                        <div id="questChatContent">
                            <?= $this->render('ajax-messages', ['messages' => $messages]) ?>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
