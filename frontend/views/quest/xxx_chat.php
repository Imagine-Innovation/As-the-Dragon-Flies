<?php

use frontend\components\QuestMessages;

/** @var yii\web\View $this */
/** @var integer $questId */
/** @var integer $playerId */
$messages = QuestMessages::getLastMessages($questId, $playerId);
?>
<div class="card h-100">
    <div class="card-body overflow-auto flex-grow-1">
        <div id="questChatContent">
            <?= $this->render('ajax-messages', ['messages' => $messages]) ?>
        </div>
        <!--
    </div>
    <div class="card-footer" style="height: 5rem">
        -->
        <div class="messages">
            <div class="messages__reply">
                <form id="questChatMessageForm">
                    <div class="input-group">
                        <input type="text" class="form-control" id="questChatInput" placeholder="Type a message...">
                        <span class="input-group-btn">
                            <button id="send-message" class="btn btn-primary" type="button">Send</button>
                        </span>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
