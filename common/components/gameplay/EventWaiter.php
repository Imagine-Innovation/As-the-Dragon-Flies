<?php

declare(strict_types=1);

namespace common\components\gameplay;

use common\components\AppStatus;
use common\helpers\JsonHelper;
use common\models\Notification;
use common\models\Quest;
use common\models\QuestSession;
use Yii;

/**
 * EventWaiter
 *
 * Server-side long polling service. Replaces the external WebSocket
 * (Ratchet) event daemon: the waiting loop lives in the controller and
 * blocks until a new notification is persisted for the quest, or until
 * the timeout expires.
 *
 * The notification table auto-increment primary key is used as the event
 * cursor: each client sends back the last event id it has seen ("after")
 * and receives every newer event, which guarantees no loss and no
 * duplication, even across reconnections.
 */
final class EventWaiter extends BaseManager
{
    public const POLL_INTERVAL_MICROSECONDS = 300_000;
    public const DEFAULT_TIMEOUT_SECONDS    = 25;

    /**
     * ID of the quest whose events are waited for.
     *
     * @var int|null
     */
    public ?int $questId = null;

    /**
     * @param array<string, mixed> $config configuration with a mandatory questId
     * @throws \Exception when questId is missing
     */
    public function __construct($config = [])
    {
        parent::__construct($config);

        if (!$this->questId) {
            throw new \Exception('EventWaiter requires a questId.');
        }
    }

    /**
     * Waits for new quest events.
     *
     * Blocks up to $timeoutSeconds, polling the notification table at
     * regular intervals. Returns as soon as at least one new event is
     * available.
     *
     * @param int      $after          last event id seen by the client
     * @param int|null $timeoutSeconds max seconds to block (defaults to DEFAULT_TIMEOUT_SECONDS)
     * @return array{events: array<int, array<string, mixed>>, after: int}
     * @throws \Exception when the quest was not provided
     */
    public function wait(int $after, ?int $timeoutSeconds = null): array
    {
        $timeout = $timeoutSeconds ?? $this->defaultTimeout();
        Yii::debug("*** Debug *** EventWaiter.wait - questId={$this->questId}, after={$after}, timeout={$timeout}");

        $deadline = microtime(true) + $timeout;

        while (microtime(true) < $deadline) {
            $events = $this->fetchEvents((int) $this->questId, $after);

            if ($events !== []) {
                $lastEvent = end($events);
                $cursor = is_array($lastEvent) ? (int) ($lastEvent['notificationId'] ?? 0) : 0;
                $ids = implode(',', array_column($events, 'notificationId'));
                Yii::info("EventWaiter.wait - delivering " . count($events) . " event(s) for quest {$this->questId} (after={$after}): ids [{$ids}]");
                return ['events' => $events, 'after' => max($after, $cursor)];
            }

            usleep(self::POLL_INTERVAL_MICROSECONDS);
        }

        return ['events' => [], 'after' => $after];
    }

    /**
     * Polls the quest event store on behalf of the controller endpoint.
     *
     * A null "after" means the client has no cursor yet (freshly loaded
     * page): the bootstrap answer returns immediately with the current
     * cursor, WITHOUT replaying the quest history. Otherwise the PHP
     * session lock is released first and the call blocks in wait() until
     * an event is available or the timeout expires.
     *
     * @param mixed $after raw "after" request parameter (null, or numeric string)
     * @return array{error: bool, msg: string, events: array<int, array<string, mixed>>, after: int, questOver: bool}
     */
    public function poll(mixed $after): array
    {
        if ($after === null) {
            // Bootstrap: a freshly loaded page has no cursor yet. It must
            // not replay the quest history, only wait for live events. The
            // quest status is reconciled BEFORE the client advances its
            // cursor onto the game-over notification (if any), otherwise a
            // page loaded just after the quest ended would skip the
            // game-over event and stay on a stale game screen forever.
            return [
                'error' => false,
                'msg' => '',
                'events' => [],
                'after' => $this->currentCursor(),
                'questOver' => $this->isQuestOver(),
            ];
        }

        // Release the PHP session lock first so that parallel requests
        // from the same player are not serialized.
        Yii::$app->session->close();
        set_time_limit(0);

        $result = $this->wait((int) $after);
        // The quest can end while the client was blocked: the game-over
        // event is delivered by wait(), the questOver flag covers the case
        // where the notification was already played and removed.
        $result['questOver'] = $this->isQuestOver();

        return $result;
    }

    /**
     * Returns every event of the quest newer than $after, in wire format.
     *
     * The wire format is identical to the one previously pushed by the
     * WebSocket daemon: Event::toArray() (type, notificationId, playerId,
     * questId, timestamp + event payload).
     *
     * @param int $questId ID of the quest
     * @param int $after   last event id seen by the client
     * @return array<int, array<string, mixed>>
     */
    protected function fetchEvents(int $questId, int $after): array
    {
        $events = [];
        foreach ($this->findNotifications($questId, $after) as $notification) {
            $events[] = $this->buildEventMessage($notification);
        }

        return $events;
    }

    /**
     * Returns the quest notifications persisted after the given event id,
     * as raw rows (no ActiveRecord hydration in the polling loop).
     *
     * @param int $questId ID of the quest
     * @param int $after   last event id seen by the client
     * @return array<int, array<string, mixed>>
     */
    protected function findNotifications(int $questId, int $after): array
    {
        return Notification::find()
            ->where(['quest_id' => $questId])
            ->andWhere(['>', 'id', $after])
            ->andWhere(['is_private' => 0])
            ->orderBy(['id' => SORT_ASC])
            ->asArray()
            ->all();
    }

    /**
     * Rebuilds the client message from a persisted notification row.
     *
     * @param array<string, mixed> $notification notification row to convert
     * @return array<string, mixed>
     */
    public function buildEventMessage(array $notification): array
    {
        $payload = JsonHelper::decode($notification['payload'] ?? null);

        return array_merge($payload, [
            'type' => $notification['notification_type'],
            'notificationId' => (int) $notification['id'],
            'playerId' => (int) $notification['initiator_id'],
            'questId' => (int) $notification['quest_id'],
            'timestamp' => (int) $notification['created_at'],
        ]);
    }

    /**
     * Tells whether the quest is already finished (completed or aborted).
     *
     * Returned with every polling answer so that a client can stop
     * polling and join the quest summary when its event stream climbed
     * past the game-over notification (e.g. a page loaded after the quest
     * ended, or a game-over notification removed by the purge).
     */
    public function isQuestOver(): bool
    {
        $status = Quest::find()
            ->select('status')
            ->where(['id' => (int) $this->questId])
            ->scalar();

        return in_array((int) $status, [AppStatus::COMPLETED->value, AppStatus::ABORTED->value], true);
    }

    /**
     * Returns the current cursor of the quest, i.e. the id of the last
     * persisted notification. A freshly loaded client bootstraps on it so
     * that it only receives live events, never the quest history.
     */
    public function currentCursor(): int
    {
        return (int) (Notification::find()
            ->where(['quest_id' => (int) $this->questId])
            ->max('id') ?? 0);
    }

    /**
     * Clears the quest event store when the quest is over.
     *
     * Called by the first client that processes the game-over event:
     * every notification and quest session of the quest is deleted, EXCEPT
     * the latest game-over notification, so that the other clients still
     * long-polling can receive it before they stop polling.
     *
     * A null quest id (session already cleared by a previous call) is a
     * tolerated no-op.
     *
     * @param int|null $questId ID of the quest to clear
     * @return array{keptGameOverId: int, deletedNotifications: int, deletedQuestSessions: int}
     */
    public static function purgeQuestNotifications(?int $questId): array
    {
        if ($questId === null || $questId <= 0) {
            return ['keptGameOverId' => 0, 'deletedNotifications' => 0, 'deletedQuestSessions' => 0];
        }

        $keptGameOverId = (int) (Notification::find()
            ->where(['quest_id' => $questId, 'notification_type' => 'game-over'])
            ->max('id') ?? 0);

        $notificationCondition = ['quest_id' => $questId];
        if ($keptGameOverId > 0) {
            $notificationCondition = ['and', ['quest_id' => $questId], ['!=', 'id', $keptGameOverId]];
        }

        $deletedNotifications = Notification::deleteAll($notificationCondition);
        $deletedQuestSessions = QuestSession::deleteAll(['quest_id' => $questId]);

        Yii::info("EventWaiter.purgeQuestNotifications - quest {$questId}: kept game-over notification #{$keptGameOverId}, deleted {$deletedNotifications} notification(s), {$deletedQuestSessions} quest session(s).");

        return ['keptGameOverId' => $keptGameOverId, 'deletedNotifications' => $deletedNotifications, 'deletedQuestSessions' => $deletedQuestSessions];
    }

    /**
     * Returns the blocking timeout configured for the waiter.
     */
    protected function defaultTimeout(): int
    {
        $timeout = Yii::$app->params['eventWaitTimeoutSeconds'] ?? self::DEFAULT_TIMEOUT_SECONDS;
        return is_int($timeout) && $timeout > 0 ? $timeout : self::DEFAULT_TIMEOUT_SECONDS;
    }
}
