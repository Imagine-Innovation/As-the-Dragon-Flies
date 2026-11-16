<?php

declare(strict_types=1);

namespace common\tests\unit\helpers;

use common\helpers\JsonHelper;
use Codeception\Test\Unit;

/**
 * Run with:
 *   php vendor/bin/codecept run common/tests/unit/helpers/JsonHelperTest.php
 */
final class JsonHelperTest extends Unit
{
	public function testDecodesAJsonString(): void
	{
		$payload = JsonHelper::decode('{"playerName":"Aragorn"}');

		$this->assertSame(['playerName' => 'Aragorn'], $payload);
	}

	public function testReturnsTheArrayWhenAlreadyDecoded(): void
	{
		$payload = JsonHelper::decode(['playerName' => 'Aragorn']);

		$this->assertSame(['playerName' => 'Aragorn'], $payload);
	}

	public function testDecodesLegacyDoubleEncodedPayload(): void
	{
		$doubleEncoded = json_encode(json_encode(['playerName' => 'Perrin Tealeaf']));

		$payload = JsonHelper::decode($doubleEncoded);

		$this->assertSame(['playerName' => 'Perrin Tealeaf'], $payload);
	}

	public function testReturnsEmptyArrayForNull(): void
	{
		$this->assertSame([], JsonHelper::decode(null));
	}

	public function testReturnsEmptyArrayForEmptyString(): void
	{
		$this->assertSame([], JsonHelper::decode(''));
	}

	public function testReturnsEmptyArrayForInvalidJson(): void
	{
		$this->assertSame([], JsonHelper::decode('{not-json'));
	}
}
