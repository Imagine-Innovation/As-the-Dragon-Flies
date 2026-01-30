<?php

namespace common\components\gameplay;

use common\components\ContextManager;
use common\helpers\JsonHelper;
use common\helpers\PayloadHelper;
use common\helpers\Utilities;
use common\models\Notification;
use Yii;

class ChatManager extends BaseManager
{

    const CHAT_NOTIFICATION_TYPE = 'new-message';
    const ROUNDED_SECONDS = 60;  // rounded to the same minute by default
    const DEFAULT_AVATAR = 'human-male-1.png';

    // Context data
    // Public facade
    public ?int $questId = null;
    public ?int $playerId = null;

    /**
     *
     * @param array<string, mixed> $config
     * @throws \Exception
     */
    public function __construct($config = []) {
        parent::__construct($config);

        $missingParam = [];
        if (!$this->questId) {
            $missingParam[] = 'questId';
        }
        if (!$this->playerId) {
            $missingParam[] = 'playerId';
        }
        if (!empty($missingParam)) {
            Yii::debug($config);
            Yii::debug(ContextManager::getContext());
            throw new \Exception('Missing params: ' . implode(', ', $missingParam) . '!!!');
        }
    }

    /**
     *
     * @param int|null $time
     * @return int
     */
    private function roundedTime(?int $time = null): int {
        $timestamp = $time ?? time();
        return intval(floor($timestamp / self::ROUNDED_SECONDS) * self::ROUNDED_SECONDS);
    }

    /**
     * Prepare a new entry for the char message array based on the cha event payload
     *
     * @param \common\models\Notification $chatNotification
     * @param int $playerId
     * @return array<string, mixed>
     */
    private function newChatEntry(Notification $chatNotification, int $playerId): array {
        if ($chatNotification->notification_type !== self::CHAT_NOTIFICATION_TYPE) {
            return [];
        }

        $payload = JsonHelper::decode($chatNotification->payload);

        Yii::debug($chatNotification->payload);
        Yii::debug($payload);
        $roundedTime = PayloadHelper::extractIntFromPayload('roundedTime', $payload) ?? $this->roundedTime();
        $sender = PayloadHelper::extractStringFromPayload('playerName', $payload);
        $message = PayloadHelper::extractStringFromPayload('message', $payload, '');
        $avatar = PayloadHelper::extractStringFromPayload('avatar', $payload, self::DEFAULT_AVATAR);
        return [
            'isAuthor' => ($chatNotification->initiator_id === $playerId), // defines is the current player is the one who initiate the chat message
            'displayedDateTime' => Utilities::formatDate($roundedTime),
            'sender' => $sender,
            'messages' => [$message], // first entry of the message array
            'avatar' => $avatar,
            'roundedTime' => $roundedTime,
        ];
    }

    /**
     *
     * @param int|null $since
     * @param int|null $limit
     * @return non-empty-array<'message'|int<0, max>, string>|array{}
     */
    public function getLastMessages(?int $since = null, ?int $limit = null): array {

        $chatNotifications = $this->getNotifications((int) $this->questId, self::CHAT_NOTIFICATION_TYPE, $since, $limit);

        if (!$chatNotifications) {
            return [];
        }

        /** @var array{int, 'message': array<string>} $chatMessages */
        $chatMessages = [];
        $previousRoundedTime = 0;
        $previousInitiatorId = 0;
        $i = 0;
        foreach ($chatNotifications as $chatNotification) {
            $roundedTime = $this->roundedTime($chatNotification->created_at);

            if (($roundedTime !== $previousRoundedTime) || ($chatNotification->initiator_id !== $previousInitiatorId)) {
                // If the message is not sent during the same minute by the same user then create a new entry
                $chatMessages[++$i] = $this->newChatEntry($chatNotification, (int) $this->playerId);
            } else {
                // otherwise, append the chat message to the current entry
                $chatMessages[$i]['messages'][] = Utilities::encode($chatNotification->message);
            }

            $previousRoundedTime = $roundedTime;
            $previousInitiatorId = $chatNotification->initiator_id;
        }
        //return array_reverse($chatMessages);
        return $chatMessages;
    }
}
