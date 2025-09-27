<?php
/** @var yii\web\View $this */
/** @var common\models\Dialog $dialog */
/** @var string $storyId */
/** @var string $chapterId */
?>
<ul>
    <li><?= nl2br($dialog->text) ?></li>
    <br>
    <?php if ($dialog->replies): ?>
        <ol>
            <?php foreach ($dialog->replies as $reply): ?>
                <li><?= nl2br($reply->text) ?></li>
                <?php if ($reply->next_dialog_id): ?>
                    <?= $this->renderFile('@app/views/mission/snippets/dialog.php', ['dialog' => $reply->nextDialog]) ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </ol>
    <?php endif; ?>
</ul>
