<?php

namespace common\tests\unit\extensions\EventHandler\factories;

use PHPUnit\Framework\TestCase;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\extensions\EventHandler\dtos\RuleOutcomeDto;

class BroadcastMessageFactoryTest extends TestCase
{
    private BroadcastMessageFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new BroadcastMessageFactory();
    }

    public function testCreateRuleOutcomeMessageWithAllParameters()
    {
        $outcomeType = 'USER_UPDATED';
        $outcomeData = ['user_id' => 1, 'new_level' => 5];
        $outcomeStatus = 'success';
        $messageKey = 'user.level.up';
        $broadcastScope = 'player';
        $broadcastTargetId = 'client123';

        $beforeTimestamp = time();
        $dto = $this->factory->createRuleOutcomeMessage(
            $outcomeType,
            $outcomeData,
            $outcomeStatus,
            $messageKey,
            $broadcastScope,
            $broadcastTargetId
        );
        $afterTimestamp = time();

        $this->assertInstanceOf(RuleOutcomeDto::class, $dto);
        $this->assertSame($outcomeType, $dto->type);
        $this->assertSame($outcomeData, $dto->data);
        $this->assertSame($outcomeStatus, $dto->status);
        $this->assertSame($messageKey, $dto->messageKey);
        $this->assertSame($broadcastScope, $dto->broadcastScope);
        $this->assertSame($broadcastTargetId, $dto->broadcastTargetId);
        $this->assertGreaterThanOrEqual($beforeTimestamp, $dto->timestamp);
        $this->assertLessThanOrEqual($afterTimestamp, $dto->timestamp);
    }

    public function testCreateRuleOutcomeMessageWithDefaultParameters()
    {
        $outcomeType = 'QUEST_COMPLETED';
        $outcomeData = ['quest_id' => 'q101', 'rewards' => ['gold' => 100]];

        $beforeTimestamp = time();
        $dto = $this->factory->createRuleOutcomeMessage(
            $outcomeType,
            $outcomeData
        );
        $afterTimestamp = time();

        $this->assertInstanceOf(RuleOutcomeDto::class, $dto);
        $this->assertSame($outcomeType, $dto->type);
        $this->assertSame($outcomeData, $dto->data);
        $this->assertSame('success', $dto->status, "Default status should be 'success'");
        $this->assertNull($dto->messageKey, "Default messageKey should be null");
        $this->assertSame('quest', $dto->broadcastScope, "Default broadcastScope should be 'quest'");
        $this->assertNull($dto->broadcastTargetId, "Default broadcastTargetId should be null");
        $this->assertGreaterThanOrEqual($beforeTimestamp, $dto->timestamp);
        $this->assertLessThanOrEqual($afterTimestamp, $dto->timestamp);
    }

    public function testCreateRuleOutcomeMessageOverridingSomeDefaults()
    {
        $outcomeType = 'ITEM_CONSUMED';
        $outcomeData = ['item_id' => 'pot7'];
        $outcomeStatus = 'processed'; // Custom status
        $messageKey = 'item.consumed.info';
        // broadcastScope and broadcastTargetId will use defaults

        $dto = $this->factory->createRuleOutcomeMessage(
            $outcomeType,
            $outcomeData,
            $outcomeStatus,
            $messageKey
        );

        $this->assertInstanceOf(RuleOutcomeDto::class, $dto);
        $this->assertSame($outcomeType, $dto->type);
        $this->assertSame($outcomeData, $dto->data);
        $this->assertSame($outcomeStatus, $dto->status);
        $this->assertSame($messageKey, $dto->messageKey);
        $this->assertSame('quest', $dto->broadcastScope, "Default broadcastScope should be 'quest'");
        $this->assertNull($dto->broadcastTargetId, "Default broadcastTargetId should be null");
    }
}
