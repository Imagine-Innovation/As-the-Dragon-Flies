<?php

use common\components\QuestMessages;
use yii\web\View;

/** @var yii\web\View $this */
/** @var integer $questId */
/** @var integer $playerId */
$messages = QuestMessages::getLastMessages($questId, $playerId);
?>
<div class="card h-100 d-flex flex-column">
    <div class="card-body overflow-auto flex-grow-1">
        <div id="questChatContent">
            <?= $this->render('ajax-messages', ['messages' => $messages]) ?>
        </div>
    </div>
    <div class="card-footer" style="height: 5rem">
        <div class="messages">
            <div class="messages__reply">
                <form id="questChatMessageForm">
                    <input type="text" class="form-control" id="questChatNewMessage" placeholder="Type a message...">
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function () {
        //$.notificationHandler.handleNotification('new-message', null);
        NotificationHandler.handleNotification('new-message', null);

        $('#questChatNewMessage').on('keydown', function (e) {
            return cariageReturnStroke(e);
        });

        $('#questChatModal').on('keydown', '#questChatNewMessage', function (e) {
            return cariageReturnStroke(e);
        });

        function cariageReturnStroke(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                e.stopPropagation();
                let messageText = $('#questChatNewMessage').val();
                QuestManager.addMessage(<?= $questId ?>, <?= $playerId ?>, messageText);
                $('#questChatModal').modal('hide');
                return false;
            }
        }
    });
</script>
