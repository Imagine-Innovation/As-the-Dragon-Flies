<?php

namespace common\tests\unit\extensions\EventHandler\dtos;

use PHPUnit\Framework\TestCase;
use common\extensions\EventHandler\dtos\RuleOutcomeDto;

class RuleOutcomeDtoTest extends TestCase
{
    public function testConstructorWithAllParameters()
    {
        $type = 'TEST_EVENT';
        $data = ['key' => 'value', 'num' => 123];
        $status = 'failure';
        $messageKey = 'test.failure.key';
        $broadcastScope = 'player';
        $broadcastTargetId = 'player123';
        $timestamp = time() - 3600; // Specific timestamp

        $dto = new RuleOutcomeDto(
            $type,
            $data,
            $status,
            $messageKey,
            $broadcastScope,
            $broadcastTargetId,
            $timestamp
        );

        $this->assertSame($type, $dto->type);
        $this->assertSame($data, $dto->data);
        $this->assertSame($status, $dto->status);
        $this->assertSame($messageKey, $dto->messageKey);
        $this->assertSame($broadcastScope, $dto->broadcastScope);
        $this->assertSame($broadcastTargetId, $dto->broadcastTargetId);
        $this->assertSame($timestamp, $dto->timestamp);
    }

    public function testConstructorWithDefaultParameters()
    {
        $type = 'DEFAULT_TEST_EVENT';
        $data = ['info' => 'some data']; // Provide data to ensure it's not mixed with default empty array

        $beforeTimestamp = time();
        $dto = new RuleOutcomeDto($type, $data);
        $afterTimestamp = time();

        $this->assertSame($type, $dto->type);
        $this->assertSame($data, $dto->data); // Check that provided data is used
        $this->assertSame('success', $dto->status, "Default status should be 'success'");
        $this->assertNull($dto->messageKey, "Default messageKey should be null");
        $this->assertSame('quest', $dto->broadcastScope, "Default broadcastScope should be 'quest'");
        $this->assertNull($dto->broadcastTargetId, "Default broadcastTargetId should be null");

        $this->assertGreaterThanOrEqual($beforeTimestamp, $dto->timestamp, "Timestamp should be current time");
        $this->assertLessThanOrEqual($afterTimestamp, $dto->timestamp, "Timestamp should be current time");
    }

    public function testConstructorOnlyRequiredParameters()
    {
        $type = 'MINIMAL_EVENT';

        $beforeTimestamp = time();
        $dto = new RuleOutcomeDto($type);
        $afterTimestamp = time();

        $this->assertSame($type, $dto->type);
        $this->assertSame([], $dto->data, "Default data should be an empty array");
        $this->assertSame('success', $dto->status);
        $this->assertNull($dto->messageKey);
        $this->assertSame('quest', $dto->broadcastScope);
        $this->assertNull($dto->broadcastTargetId);
        $this->assertGreaterThanOrEqual($beforeTimestamp, $dto->timestamp);
        $this->assertLessThanOrEqual($afterTimestamp, $dto->timestamp);
    }
}
