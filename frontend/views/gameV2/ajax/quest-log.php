<?php

use common\widgets\MarkDown;

/** @var common\models\QuestLog[] $questLogs */
/** @var string $language */
foreach ($questLogs as $questLog) {
    echo '<p class="journal-entry">';
    echo "<time>Round {$questLog->round}</time>";
    echo MarkDown::widget(['content' => $questLog->description]);
    echo '</p>';
}
