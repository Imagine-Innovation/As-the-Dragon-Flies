<?php

namespace common\components\gameplay;

use common\helpers\Utilities;
use common\models\Notification;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

/**
 * BaseManager
 *
 * Common utility for gameplay managers. Keeps code DRY by providing
 * helper methods and a standard way to trigger events.
 */
abstract class BaseManager extends Component
{

    const DEFAULT_LIMIT = 20;

    /**
     * Trigger an event (wrapper for consistency)
     *
     * @param string $name
     * @param \yii\base\Event|null $event
     */
    protected function raiseEvent(string $name, $event = null): void
    {
        $this->trigger($name, $event);
    }

    protected function save(\yii\db\ActiveRecord $model): bool
    {
        $successfullySaved = $model->save();
        if ($successfullySaved) {
            return true;
        }
        throw new \Exception(implode("<br />", ArrayHelper::getColumn($model->errors, 0, false)));
    }

    /**
     * Get every notification of a specific type for a specific quest.
     *
     * @param int $questId ID of the quest
     * @param string $type notification type ("chat", "action"...)
     * @param int|null $since optional parameter to get the notifications since a specific date
     * @param int|null $limit optional parameter to limit the number of returned values
     * @return \common\models\Notification[]|null
     */
    protected function getNotifications(int $questId, string $type, ?int $since = null, ?int $limit = null): ?array
    {

        Yii::debug("*** Debug *** getNotifications - questId={$questId}, type={$type}, since=" . ($since
                            ? Utilities::formatDate($since) : "null") . ", limit=" . ($limit ?? "null"));
        $query = Notification::find()
                ->where(['quest_id' => $questId])
                ->andWhere(['notification_type' => $type]);

        if ($since) {
            $query->andWhere(['>=', 'created_at', $since]);
        }

        $notifications = $query->orderBy(['created_at' => SORT_DESC])
                ->limit($limit ?? self::DEFAULT_LIMIT)
                ->all();

        $n = count($notifications);
        Yii::debug("*** Debug *** getNotifications - returns {$n} records");
        return $notifications;
    }
}
