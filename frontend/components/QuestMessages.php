<?php

namespace frontend\components;

use common\models\QuestChat;
use common\helpers\Utilities;
use Yii;

class QuestMessages {

    public static function getRecentChatMessages(int $questId, int $limit = 20): array {
        $recentChatMessages = QuestChat::find()
                ->where(['quest_id' => $questId])
                ->orderBy(['created_at' => SORT_DESC])
                ->limit($limit)
                ->all();

        $messages = [];
        foreach ($recentChatMessages as $chatMessage) {
            $messages[] = [
                'playerId' => $chatMessage->player_id,
                'playerName' => $chatMessage->player->name,
                'message' => $chatMessage->message,
                'timestamp' => $chatMessage->created_at
            ];
        }

        return $messages;
    }

    public static function getLastMessages(int $questId, int $playerId, int $from = null, int $limit = 20): string {
        $query = QuestChat::find()->where(['quest_id' => $questId]);

        if ($from) {
            Yii::debug("*** Debug *** prepareMessages - From=" . Utilities::formatDate($from));
            $query->andWhere(['>=', 'created_at', $from]);
        }

        $questChat = $query->orderBy(['created_at' => SORT_ASC])
                ->limit($limit)
                ->all();

        return self::prepareMessages($questChat, $playerId);
    }

    private static function prepareMessages(QuestChat $questChat, int $playerId) {
        $messages = [];
        $data = [];
        $prevRoundedTime = 0;
        $prevPlayerId = 0;
        $i = 0;
        foreach ($questChat as $chat) {
            $sender = $chat->sender;
            $roundedTime = floor($chat->created_at / 60) * 60;
            Yii::debug("*** Debug *** prepareMessages[$i] - sender=$sender->id, date=" . Utilities::formatDate($chat->created_at) . ", message=$chat->message");

            if (($roundedTime == $prevRoundedTime) && ($sender->id == $prevPlayerId)) {
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
            $prevPlayerId = $sender->id;
            Yii::debug("*** Debug *** prepareMessages[$i] - message=" . implode(" ", $messages[$i]['messages']));
        }
        return array_reverse($messages);
    }
}
