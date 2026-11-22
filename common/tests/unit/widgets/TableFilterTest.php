<?php

namespace common\tests\unit\widgets;

use common\components\AppStatus;
use common\widgets\TableFilter;
use common\tests\UnitTester;

class TableFilterTest extends \Codeception\Test\Unit
{

    /**
     * @var UnitTester
     */
    protected $tester;

    public function testNothingDisplayedWhenParametersOmitted()
    {
        $html = TableFilter::widget([]);

        $this->assertSame('', $html);
    }

    public function testNoFilterControlWhenFilterArrayOmitted()
    {
        $html = TableFilter::widget(['searchString' => true]);

        $this->assertStringNotContainsString('custom-control-input', $html);
        $this->assertStringNotContainsString('<select', $html);
    }

    public function testOnlySearchInputWhenFilterArrayEmpty()
    {
        $html = TableFilter::widget(['filterArray' => [], 'searchString' => true]);

        $this->assertStringContainsString('id="routeSearch"', $html);
        $this->assertStringNotContainsString('custom-control-input', $html);
        $this->assertStringContainsString('bi bi-search', $html);
    }

    public function testOnlyFilterControlWhenSearchStringOmitted()
    {
        $html = TableFilter::widget(['filterArray' => ['Backend']]);

        $this->assertStringNotContainsString('routeSearch', $html);
    }

    public function testCheckboxesAreDefaultForSmallFilterArray()
    {
        $html = TableFilter::widget(['filterArray' => ['Backend', 'Frontend']]);

        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringNotContainsString('type="radio"', $html);
        $this->assertStringContainsString('name="application"', $html);
        $this->assertStringContainsString('id="application-Backend"', $html);
        $this->assertStringContainsString('value="Backend"', $html);
        $this->assertStringContainsString('>Backend</label>', $html);
    }

    public function testCheckboxesWithThreeValues()
    {
        $html = TableFilter::widget(['filterArray' => ['One', 'Two', 'Three']]);

        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('id="application-One"', $html);
        $this->assertStringContainsString('id="application-Three"', $html);
        $this->assertStringNotContainsString('<select', $html);
    }

    public function testComboBoxWithMoreThanThreeValues()
    {
        $html = TableFilter::widget(['filterArray' => ['One', 'Two', 'Three', 'Four']]);

        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('</select>', $html);
        $this->assertStringContainsString('value="One"', $html);
        $this->assertStringContainsString('value="Four"', $html);
        $this->assertStringNotContainsString('type="checkbox"', $html);
    }

    public function testComboBoxMultipleAttribute()
    {
        $html = TableFilter::widget([
                    'filterArray' => ['One', 'Two', 'Three', 'Four', 'Five'],
                    'multipleValues' => true,
        ]);

        $this->assertStringContainsString(' multiple', $html);
    }

    public function testComboBoxSingleValue()
    {
        $html = TableFilter::widget([
                    'filterArray' => ['One', 'Two', 'Three', 'Four', 'Five'],
                    'multipleValues' => false,
        ]);

        $this->assertStringNotContainsString(' multiple', $html);
    }

    public function testRadioButtonsWhenMultipleValuesFalse()
    {
        $html = TableFilter::widget([
                    'filterArray' => ['Backend', 'Frontend'],
                    'multipleValues' => false,
        ]);

        $this->assertStringContainsString('type="radio"', $html);
        $this->assertStringNotContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('custom-control-radio', $html);
    }

    public function testSearchStringTrue()
    {
        $html = TableFilter::widget(['searchString' => true]);

        $this->assertStringContainsString('id="routeSearch"', $html);
        $this->assertStringContainsString('name="routeSearch"', $html);
        $this->assertStringContainsString('placeholder="Search route"', $html);
        $this->assertStringContainsString('onkeydown', $html);
        $this->assertStringContainsString('event.preventDefault()', $html);
        $this->assertStringContainsString('bi bi-search', $html);
    }

    public function testOnChangeCallIsTriggered()
    {
        $html = TableFilter::widget(['filterArray' => ['Backend'], 'searchString' => true]);

        $this->assertStringContainsString("onchange='AccessRightManager.loadGenericAjaxTable(0);'", $html);
        $callCount = substr_count($html, 'AccessRightManager.loadGenericAjaxTable(0);');
        // Checkbox change, keydown, search button
        $this->assertSame(3, $callCount);
    }

    public function testCustomFilterNameAndSearchId()
    {
        $html = TableFilter::widget([
                    'filterArray' => ['Backend'],
                    'searchString' => true,
                    'filterName' => 'status',
                    'searchId' => 'userSearch',
                    'searchPlaceholder' => 'Search user',
        ]);

        $this->assertStringContainsString('name="status"', $html);
        $this->assertStringContainsString('id="status-Backend"', $html);
        $this->assertStringContainsString('id="userSearch"', $html);
        $this->assertStringContainsString('placeholder="Search user"', $html);
        $this->assertStringNotContainsString('routeSearch', $html);
    }

    public function testValueLabelPairs()
    {
        $html = TableFilter::widget([
                    'filterArray' => [
                        'backend' => 'Backend routes',
                        'frontend' => 'Frontend routes',
                    ],
        ]);

        $this->assertStringContainsString('value="backend"', $html);
        $this->assertStringContainsString('>Backend routes</label>', $html);
        $this->assertStringContainsString('value="frontend"', $html);
        $this->assertStringContainsString('>Frontend routes</label>', $html);
    }

    public function testSecurity()
    {
        $html = TableFilter::widget([
                    'filterArray' => [
                        '<b>bold</b>' => '<script>alert(1)</script>',
                    ],
                    'searchString' => true,
                    'searchPlaceholder' => '<i>inject</i>',
        ]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<i>inject</i>', $html);
        $this->assertStringContainsString('&lt;script&gt;alert(1)&lt;/script&gt;</label>', $html);
        $this->assertStringContainsString('placeholder="&lt;i&gt;inject&lt;/i&gt;"', $html);
    }

    public function testAppStatusValuesForUser()
    {
        $html = TableFilter::widget(['filterArray' => AppStatus::getValuesForUser()]);

        $this->assertStringContainsString('value="10"', $html);
        $this->assertStringContainsString('value="9"', $html);
        $this->assertStringContainsString('value="0"', $html);
        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringNotContainsString('<select', $html);
    }
}
