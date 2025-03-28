<?php
/** @var array $questChat */
?>
<div class="d-none">
    <pre>
        <?= print_r($questChat) ?>
    </pre>
</div>
<!--div class="messages"-->
<div class="messages__body">
    <div class="messages__content" style="overflow-y: scroll">
        <div class="scrollbar" id="questChatContent">
            <?php if ($questChat): ?>
                <?php foreach ($questChat as $chat): ?>
                    <?php if ($chat['is_author']): ?>
                        <div class="messages__item messages__item--right">
                        <?php else: ?>
                            <div class="messages__item">
                                <img src="<?= $chat['avatar'] ?>" class="avatar-img" alt="">
                            <?php endif; ?>
                            <div class="messages__details">
                                <?= implode("", $chat['messages']) ?>
                                <small><i class="bi-clock"></i> <?= $chat['dateTime'] ?> - <?= $chat['sender'] ?></small>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="messages__reply">
            <form id="questChatMessageForm">
                <input type="text" class="form-control mb-2 mr-sm-2" id="questChatNewMessage" placeholder="Type a message...">
            </form>
        </div>
    </div>
