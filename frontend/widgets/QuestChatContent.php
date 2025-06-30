<?php

namespace frontend\widgets;

use frontend\components\QuestMessages;
use Yii;
use yii\base\Widget;

class QuestChatContent extends Widget {

    public $questId;
    public $playerId;

    public function run() {
        $questChat = QuestMessages::getRecentChatMessages($this->questId);
        return $this->render('quest-chat', [
                    'questChat' => $questChat
        ]);
    }
}
