<?php

namespace common\components;

use common\models\QuestChat;
use common\helpers\Utilities;
use Yii;

class QuestMessages {

    public static function getLastMessages($questId, $playerId, $from = null) {
        $query = QuestChat::find()->where(['quest_id' => $questId]);

        if ($from) {
            Yii::debug("*** Debug *** prepareMessages - From=" . Utilities::formatDate($from));
            $query->andWhere(['>=', 'created_at', $from]);
        }

        $questChat = $query->orderBy(['created_at' => SORT_ASC])
                ->all();

        return self::prepareMessages($questChat, $playerId);
    }

    private static function prepareMessages($questChat, $playerId) {
        $messages = [];
        $data = [];
        $prevRoundedTime = 0;
        $prevSenderId = 0;
        $i = 0;
        foreach ($questChat as $chat) {
            $sender = $chat->sender;
            $roundedTime = floor($chat->created_at / 60) * 60;
            Yii::debug("*** Debug *** prepareMessages[$i] - sender=$sender->id, date=" . Utilities::formatDate($chat->created_at) . ", message=$chat->message");

            if (($roundedTime == $prevRoundedTime) && ($sender->id == $prevSenderId)) {
                Yii::debug("*** Debug *** prepareMessages[$i] -----------> same minute, same sender");
                $data['messages'][] = '<p>' . Utilities::encode($chat->message) . '</p>';
                $messages[$i] = $data;
            } else {
                $data = [
                    'is_author' => ($sender->id == $playerId),
                    'date_time' => Utilities::formatDate($chat->created_at),
                    'sender' => Utilities::encode($sender->name),
                    'messages' => ['<p>' . Utilities::encode($chat->message) . '</p>'],
                    'avatar' => "img/characters/" . $sender->image->file_name,
                    'div_id' => 'quest-chat-' . $roundedTime,
                ];

                $messages[++$i] = $data;
            }

            $prevRoundedTime = $roundedTime;
            $prevSenderId = $sender->id;
            Yii::debug("*** Debug *** prepareMessages[$i] - message=" . implode(" ", $messages[$i]['messages']));
        }
        return array_reverse($messages);
    }
}
