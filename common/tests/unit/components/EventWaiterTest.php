<?php

declare(strict_types=1);

namespace common\tests\unit\components;

use Codeception\Test\Unit;
use common\components\gameplay\EventWaiter;
use common\models\Notification;

final class EventWaiterTest extends Unit
{

    public function testBuildEventMessageReconstructsWireFormat(): void
    {
        $notification = new Notification();
        $notification->id = 42;
        $notification->initiator_id = 7;
        $notification->quest_id = 3;
        $notification->notification_type = 'next-turn';
        $notification->title = 'Next turn';
        $notification->message = 'It is your turn';
        $notification->created_at = 1700000000;
        $notification->is_private = 0;
        $notification->payload = json_encode([
            'playerName' => 'Aragorn',
            'sessionId' => 'session-abc',
            'detail' => ['nextPlayerId' => 7, 'nextPlayerName' => 'Aragorn'],
        ]);

        $waiter = new EventWaiter(['questId' => 3]);
        $message = $waiter->buildEventMessage($notification);

        $this->assertSame('next-turn', $message['type']);
        $this->assertSame(42, $message['notificationId']);
        $this->assertSame('session-abc', $message['sessionId']);
        $this->assertSame(7, $message['playerId']);
        $this->assertSame(3, $message['questId']);
        $this->assertSame(1700000000, $message['timestamp']);
        $this->assertSame('Aragorn', $message['playerName']);
        $this->assertSame(['nextPlayerId' => 7, 'nextPlayerName' => 'Aragorn'], $message['detail']);
    }

    public function testBuildEventMessageHandlesLegacyPayloadWithoutSessionId(): void
    {
        $notification = new Notification();
        $notification->id = 1;
        $notification->initiator_id = 2;
        $notification->quest_id = 3;
        $notification->notification_type = 'game-action';
        $notification->title = 'Game action';
        $notification->message = 'Rolled a dice';
        $notification->created_at = 1700000001;
        $notification->is_private = 0;
        $notification->payload = json_encode(['playerName' => 'Gimli']);

        $waiter = new EventWaiter(['questId' => 3]);
        $message = $waiter->buildEventMessage($notification);

        $this->assertSame('game-action', $message['type']);
        $this->assertNull($message['sessionId']);
        $this->assertSame('Gimli', $message['playerName']);
        $this->assertSame(2, $message['playerId']);
    }

    public function testBuildEventMessageHandlesNullPayload(): void
    {
        $notification = new Notification();
        $notification->id = 5;
        $notification->initiator_id = 2;
        $notification->quest_id = 3;
        $notification->notification_type = 'player-quit';
        $notification->title = 'Player quit';
        $notification->message = 'Boromir left';
        $notification->created_at = 1700000002;
        $notification->is_private = 0;
        $notification->payload = null;

        $waiter = new EventWaiter(['questId' => 3]);
        $message = $waiter->buildEventMessage($notification);

        $this->assertSame('player-quit', $message['type']);
        $this->assertNull($message['sessionId']);
        $this->assertSame(5, $message['notificationId']);
    }

    public function testConstructorRequiresQuestId(): void
    {
        $this->expectException(\Exception::class);
        new EventWaiter();
    }
}
