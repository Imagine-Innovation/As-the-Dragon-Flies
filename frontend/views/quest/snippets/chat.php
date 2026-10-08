<?php

use common\components\gameplay\ChatManager;

/** @var yii\web\View $this */
/** @var int $questId */
/** @var int $playerId */
$chatManager = new ChatManager(['questId' => $questId, 'playerId' => $playerId]);
$messages = $chatManager->getLastMessages();
?>
<!-- Chat Panel -->
<div class="chat-panel-container">
    <div class="card p-4 h-100 d-flex flex-column">
        <div class="card-header">
            <h5 class="text-decoration"><?= Yii::t('app/game', 'Quest Chat') ?></h5>
        </div>
        <audio id="ding-sound" src="music/ding.mp3" preload="auto"></audio>

        <div class="mt-auto">
            <form id="questChatMessageForm">
                <div class="input-group">
                    <input type="text" class="form-control" id="questChatInput" placeholder="<?= Yii::t('app/game', 'Type your message...') ?>">
                    <button id="sendChatMessageButton" class="btn btn-primary" type="button"><?= Yii::t('app/game', 'Send') ?></button>
                </div>
            </form>
            <small class="text-muted mt-2 d-block"><?= Yii::t('app/game', 'Press Enter to send • Be respectful to fellow adventurers') ?></small>
        </div>

        <div class="card-body overflow-auto flex-grow-1 mb-3">
            <div id="questChatContent">
                <?= $this->render('../ajax/messages', ['messages' => $messages]) ?>
            </div>
        </div>

    </div>
</div>
