<?php

declare(strict_types=1);

namespace common\tests\unit\components;

use common\components\AppStatus;
use common\tests\UnitTester;

class AppStatusTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    public function testGetValuesForPlayerReturnsWhitelistedStatuses(): void
    {
        $this->assertSame([10, 9, 0], AppStatus::getValuesForPlayer());
    }

    public function testGetPlayerStatusIndexesLabelsByValue(): void
    {
        $statuses = AppStatus::getPlayerStatus();

        $this->assertSame(AppStatus::ACTIVE->getLabel(), $statuses[AppStatus::ACTIVE->value]);
        $this->assertSame(AppStatus::INACTIVE->getLabel(), $statuses[AppStatus::INACTIVE->value]);
        $this->assertSame(AppStatus::DELETED->getLabel(), $statuses[AppStatus::DELETED->value]);
    }

    public function testGetPlayerStatusKeysMatchWhitelistedValues(): void
    {
        $this->assertSame(AppStatus::getValuesForPlayer(), array_keys(AppStatus::getPlayerStatus()));
    }
}
