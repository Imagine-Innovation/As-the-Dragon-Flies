<?php

namespace common\models\events;

use Yii;

class StartQuestEvent extends Event
{

    public function getType(): string {
        return 'start_quest';
    }

    public function getTitle(): string {
        return 'The quest has started';
    }

    public function getMessage(): string {
        return "{$this->quest->story->name} has started";
    }

    public function getPayload(): array {
        return [
            'questId' => $this->quest->id,
            'questName' => $this->quest->story->name,
            'startTime' => date('Y-m-d H:i:s', $this->timestamp)
        ];
    }

    public function process(): void {
        Yii::debug("*** Debug *** StartQuestEvent - process");
        // Update quest status
        $this->quest->status = 'playing';
        $this->quest->started_at = time();
        $this->quest->save();

        // Create notification
        $notification = $this->createNotification();

        $this->savePlayerNotification($notification->id);

        $this->broadcast();
    }
}
