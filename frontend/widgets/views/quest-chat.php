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
                <?php foreach ($questChat as $chatMessage): ?>
                    <div class="messages__item<?= ($chatMessage['isAuthor']) ? ' messages__item--right' : '' ?>" id="quest-chat-<?= $chatMessage['roundedTime'] ?>">
                        <?php if (!$chatMessage['isAuthor']): ?>
                            <img src="img/characters/<?= $chatMessage['avatar'] ?>" class="avatar-img" alt="">
                        <?php endif; ?>
                        <div class="messages__details">
                            <?php foreach ($chatMessage['messages'] as $chat): ?>
                                <p><?= $chat ?></p>
                            <?php endforeach; ?>
                            <small><i class="bi-clock"></i> <?= $chatMessage['displayedDateTime'] ?> - <?= $chatMessage['sender'] ?></small>
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
