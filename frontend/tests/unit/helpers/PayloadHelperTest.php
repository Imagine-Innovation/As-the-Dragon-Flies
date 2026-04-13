<?php

namespace frontend\tests\unit\helpers;

use Codeception\Test\Unit;
use common\helpers\PayloadHelper;

class PayloadHelperTest extends Unit
{
    public function testExtractPayloadFromDataWithWrappedPayload(): void
    {
        $data = [
            'event' => 'next-turn',
            'payload' => [
                'type' => 'next-turn',
                'detail' => ['nextPlayerId' => 42],
            ],
        ];

        $payload = PayloadHelper::extractPayloadFromData($data);

        $this->assertSame('next-turn', $payload['type']);
        $this->assertSame(42, $payload['detail']['nextPlayerId']);
    }

    public function testExtractPayloadFromDataWithFlatPayload(): void
    {
        $data = [
            'type' => 'next-turn',
            'detail' => ['nextPlayerId' => 7],
        ];

        $payload = PayloadHelper::extractPayloadFromData($data);

        $this->assertSame($data, $payload);
    }

    public function testExtractArrayFromPayloadUsesAlternativePayload(): void
    {
        $detail = ['nextPlayerId' => 9, 'nextPlayerName' => 'Aria'];
        $array = PayloadHelper::extractArrayFromPayload('detail', [], ['detail' => $detail]);

        $this->assertSame($detail, $array);
    }
}
