<?php

declare(strict_types=1);

namespace common\tests\unit\components;

use common\components\AjaxRequest;
use common\tests\UnitTester;

class AjaxRequestTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    public function testPostedStatusesWithValidValues(): void
    {
        $statuses = AjaxRequest::postedStatuses(['5', '3'], [1, 2, 3]);

        $this->assertSame([3], $statuses);
    }

    public function testPostedStatusesWithMalformedValues(): void
    {
        // Non-integer strings must be dropped, not cast (e.g. 'garbage' -> 0)
        $this->assertNull(AjaxRequest::postedStatuses(['garbage', '10oops'], [0, 1, 10]));
        $this->assertNull(AjaxRequest::postedStatuses(['1.5'], [1]));
        $this->assertNull(AjaxRequest::postedStatuses([''], [0]));
    }

    public function testPostedStatusesWithOutOfWhitelistValues(): void
    {
        $this->assertNull(AjaxRequest::postedStatuses(['100', '200'], [0, 1]));
    }

    public function testPostedStatusesKeepsZero(): void
    {
        $this->assertSame([0], AjaxRequest::postedStatuses(['0'], [0, 1]));
    }

    public function testPostedStatusesDeduplicatesValues(): void
    {
        $this->assertSame([2], AjaxRequest::postedStatuses([2, '2', '2'], [2]));
    }

    public function testPostedStatusesWithNonArrayInput(): void
    {
        $this->assertNull(AjaxRequest::postedStatuses(null, [0, 1]));
        $this->assertNull(AjaxRequest::postedStatuses('applications', [0, 1]));
        $this->assertNull(AjaxRequest::postedStatuses(10, [0, 1]));
        $this->assertNull(AjaxRequest::postedStatuses(new \stdClass(), [0, 1]));
    }

    public function testPostedStatusesWithEmptyValues(): void
    {
        $this->assertNull(AjaxRequest::postedStatuses([], [0, 1]));
    }
}
