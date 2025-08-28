<?php

namespace common\models\events;

use Yii;

class QuestStartedEvent extends Event
{
    public function getType(): string
    {
        return 'quest_started';
    }

    public function getTitle(): string
    {
        return 'The quest has started';
    }

    public function getMessage(): string
    {
        return "{$this->quest->story->name} has started";
    }

    public function getPayload(): array
    {
        return [
            'questId' => $this->quest->id,
            'questName' => $this->quest->story->name,
            'startTime' => date('Y-m-d H:i:s', $this->timestamp),
            'redirectUrl' => '/frontend/web/index.php?r=game/view&id=' . $this->quest->id,
        ];
    }

    public function process(): void
    {
        Yii::debug("*** Debug *** QuestStartedEvent - process");
        
        // Create notification
        $notification = $this->createNotification();
        $this->savePlayerNotification($notification->id);

        $this->broadcast();
    }
}
