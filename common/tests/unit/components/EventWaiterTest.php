<?php

declare(strict_types=1);

namespace common\tests\unit\components;

use Codeception\Test\Unit;
use common\components\gameplay\EventWaiter;

final class EventWaiterTest extends Unit
{
    public function testBuildEventMessageReconstructsWireFormat(): void
    {
        $notification = [
            'id' => 42,
            'initiator_id' => 7,
            'quest_id' => 3,
            'notification_type' => 'next-turn',
            'created_at' => 1700000000,
            'payload' => json_encode([
                'playerName' => 'Aragorn',
                'detail' => ['nextPlayerId' => 7, 'nextPlayerName' => 'Aragorn'],
            ]),
        ];

        $waiter = new EventWaiter(['questId' => 3]);
        $message = $waiter->buildEventMessage($notification);

        $this->assertSame('next-turn', $message['type']);
        $this->assertSame(42, $message['notificationId']);
        $this->assertSame(7, $message['playerId']);
        $this->assertSame(3, $message['questId']);
        $this->assertSame(1700000000, $message['timestamp']);
        $this->assertSame('Aragorn', $message['playerName']);
        $this->assertSame(['nextPlayerId' => 7, 'nextPlayerName' => 'Aragorn'], $message['detail']);
    }

    public function testBuildEventMessageHandlesLegacyPayloadWithoutSessionId(): void
    {
        $notification = [
            'id' => 1,
            'initiator_id' => 2,
            'quest_id' => 3,
            'notification_type' => 'game-action',
            'created_at' => 1700000001,
            'payload' => json_encode(['playerName' => 'Gimli']),
        ];

        $waiter = new EventWaiter(['questId' => 3]);
        $message = $waiter->buildEventMessage($notification);

        $this->assertSame('game-action', $message['type']);
        $this->assertSame('Gimli', $message['playerName']);
        $this->assertSame(2, $message['playerId']);
    }

    public function testBuildEventMessageHandlesNullPayload(): void
    {
        $notification = [
            'id' => 5,
            'initiator_id' => 2,
            'quest_id' => 3,
            'notification_type' => 'player-quit',
            'created_at' => 1700000002,
            'payload' => null,
        ];

        $waiter = new EventWaiter(['questId' => 3]);
        $message = $waiter->buildEventMessage($notification);

        $this->assertSame('player-quit', $message['type']);
        $this->assertSame(5, $message['notificationId']);
    }

    public function testConstructorRequiresQuestId(): void
    {
        $this->expectException(\Exception::class);
        new EventWaiter();
    }
}
