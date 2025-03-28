<?php

namespace frontend\widgets;

use common\models\Player;
use common\models\QuestChat;
use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class QuestChatContent extends Widget {

    public $questId;
    public $playerId;

    public function run() {
        $questChat = QuestChat::find()
                ->where(['quest_id' => $this->questId])
                ->orderBy(['created_at' => SORT_ASC])
                ->all();

        return $this->render('quest-chat', [
                    'questChat' => $this->prepareChat($questChat)
        ]);
    }

    private function prepareChat($questChat) {
        $chatData = [];
        $data = [];
        $prevDateTime = "";
        $prevSenderId = 0;
        foreach ($questChat as $chat) {
            $player = $chat->sender;
            $dateTime = Yii::$app->formatter->asDateTime($chat->created_at, 'dd/MM/yyyy HH:mm');

            if (($dateTime == $prevDateTime) && ($player->id == $prevSenderId)) {
                $data['messages'][] = '<p>'. Html::encode($chat->message) . '</p>';
                $chatData[0] = $data;
            } else {
                $data = [
                    'is_author' => ($player->id == $this->playerId),
                    'dateTime' => $dateTime,
                    'sender' => Html::encode($player->name),
                    'messages' => ['<p>'. Html::encode($chat->message) . '</p>'],
                    'avatar' => "img/characters/" . $player->image->file_name,
                ];

                $chatData[] = $data;
            }

            $prevDateTime = $dateTime;
            $prevSenderId = $player->id;
        }
        return array_reverse($chatData);
    }
}
