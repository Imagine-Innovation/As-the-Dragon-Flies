<?php

declare(strict_types=1);

namespace common\tests\unit\widgets;

use common\components\AppStatus;
use common\widgets\TableFilter;
use common\tests\UnitTester;

class TableFilterTest extends \Codeception\Test\Unit
{

    protected UnitTester $tester;

    public function testNothingDisplayedWhenParametersOmitted(): void
    {
        $html = TableFilter::widget([]);

        $this->assertSame('', $html);
    }

    public function testNoFilterControlWhenFilterArrayOmitted(): void
    {
        $html = TableFilter::widget(['searchString' => true]);

        $this->assertStringNotContainsString('custom-control-input', $html);
        $this->assertStringNotContainsString('<select', $html);
    }

    public function testOnlySearchInputWhenFilterArrayEmpty(): void
    {
        $html = TableFilter::widget(['filterArray' => [], 'searchString' => true]);

        $this->assertStringContainsString('id="routeSearch"', $html);
        $this->assertStringNotContainsString('custom-control-input', $html);
        $this->assertStringContainsString('bi bi-search', $html);
    }

    public function testOnlyFilterControlWhenSearchStringOmitted(): void
    {
        $html = TableFilter::widget(['filterArray' => ['Backend']]);

        $this->assertStringNotContainsString('routeSearch', $html);
    }

    public function testCheckboxesAreDefaultForSmallFilterArray(): void
    {
        $html = TableFilter::widget(['filterArray' => ['Backend', 'Frontend']]);

        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringNotContainsString('type="radio"', $html);
        $this->assertStringContainsString('name="application"', $html);
        $this->assertStringContainsString('id="application-Backend"', $html);
        $this->assertStringContainsString('value="Backend"', $html);
        $this->assertStringContainsString('>Backend</label>', $html);
    }

    public function testCheckboxesWithThreeValues(): void
    {
        $html = TableFilter::widget(['filterArray' => ['One', 'Two', 'Three']]);

        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('id="application-One"', $html);
        $this->assertStringContainsString('id="application-Three"', $html);
        $this->assertStringNotContainsString('<select', $html);
    }

    public function testComboBoxWithMoreThanThreeValues(): void
    {
        $html = TableFilter::widget(['filterArray' => ['One', 'Two', 'Three', 'Four']]);

        $this->assertStringContainsString('<select', $html);
        $this->assertStringContainsString('</select>', $html);
        $this->assertStringContainsString('value="One"', $html);
        $this->assertStringContainsString('value="Four"', $html);
        $this->assertStringNotContainsString('type="checkbox"', $html);
    }

    public function testComboBoxMultipleAttribute(): void
    {
        $html = TableFilter::widget([
                    'filterArray' => ['One', 'Two', 'Three', 'Four', 'Five'],
                    'multipleValues' => true,
        ]);

        $this->assertStringContainsString(' multiple', $html);
    }

    public function testComboBoxSingleValue(): void
    {
        $html = TableFilter::widget([
                    'filterArray' => ['One', 'Two', 'Three', 'Four', 'Five'],
                    'multipleValues' => false,
        ]);

        $this->assertStringNotContainsString(' multiple', $html);
    }

    public function testRadioButtonsWhenMultipleValuesFalse(): void
    {
        $html = TableFilter::widget([
                    'filterArray' => ['Backend', 'Frontend'],
                    'multipleValues' => false,
        ]);

        $this->assertStringContainsString('type="radio"', $html);
        $this->assertStringNotContainsString('type="checkbox"', $html);
        $this->assertStringContainsString('custom-control-radio', $html);
    }

    public function testSearchStringTrue(): void
    {
        $html = TableFilter::widget(['searchString' => true]);

        $this->assertStringContainsString('id="routeSearch"', $html);
        $this->assertStringContainsString('name="routeSearch"', $html);
        $this->assertStringContainsString('placeholder="Search route"', $html);
        $this->assertStringContainsString('onkeydown', $html);
        $this->assertStringContainsString('event.preventDefault()', $html);
        $this->assertStringContainsString('bi bi-search', $html);
    }

    public function testOnChangeCallIsTriggered(): void
    {
        $html = TableFilter::widget(['filterArray' => ['Backend'], 'searchString' => true]);

        $this->assertStringContainsString("onchange='AccessRightManager.loadGenericAjaxTable(0);'", $html);
        $callCount = substr_count($html, 'AccessRightManager.loadGenericAjaxTable(0);');
        // Checkbox change, keydown, search button
        $this->assertSame(3, $callCount);
    }

    public function testCustomFilterNameAndSearchId(): void
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

    public function testValueLabelPairs(): void
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

    public function testValueLabelPairsWithIntegerFilters(): void
    {
        $html = TableFilter::widget([
                    'filterArray' => [
                        10 => 'Active',
                        9 => 'Inactive',
                        0 => 'Deleted',
                    ],
        ]);

        $this->assertStringContainsString('value="10"', $html);
        $this->assertStringContainsString('>Active</label>', $html);
        $this->assertStringContainsString('value="9"', $html);
        $this->assertStringContainsString('>Inactive</label>', $html);
        $this->assertStringContainsString('value="0"', $html);
        $this->assertStringContainsString('>Deleted</label>', $html);
        // The values to filter on are the array keys, not the labels
        $this->assertStringNotContainsString('value="Active"', $html);
    }

    public function testAppStatusUserStatusLabels(): void
    {
        $html = TableFilter::widget(['filterArray' => AppStatus::getUserStatus()]);

        $this->assertStringContainsString('value="10"', $html);
        $this->assertStringContainsString('value="9"', $html);
        $this->assertStringContainsString('value="0"', $html);
        $this->assertStringContainsString('>Active</label>', $html);
        $this->assertStringContainsString('>Inactive</label>', $html);
        $this->assertStringContainsString('>Deleted</label>', $html);
        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringNotContainsString('<select', $html);
    }

    public function testSecurity(): void
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

    public function testAppStatusValuesForUser(): void
    {
        $html = TableFilter::widget(['filterArray' => AppStatus::getValuesForUser()]);

        $this->assertStringContainsString('value="10"', $html);
        $this->assertStringContainsString('value="9"', $html);
        $this->assertStringContainsString('value="0"', $html);
        $this->assertStringContainsString('type="checkbox"', $html);
        $this->assertStringNotContainsString('<select', $html);
    }

    public function testNullFilterArrayRendersNothing(): void
    {
        $html = TableFilter::widget(['filterArray' => null]);

        $this->assertSame('', $html);
    }

    public function testNullFilterArrayStillRendersSearch(): void
    {
        $html = TableFilter::widget(['filterArray' => null, 'searchString' => true]);

        $this->assertStringContainsString('id="routeSearch"', $html);
        $this->assertStringNotContainsString('custom-control-input', $html);
        $this->assertStringNotContainsString('<select', $html);
    }

    public function testRadioButtonsCanBeCleared(): void
    {
        $html = TableFilter::widget([
                    'filterArray' => ['Backend', 'Frontend'],
                    'multipleValues' => false,
        ]);

        $this->assertStringContainsString('id="application-all"', $html);
        $this->assertStringContainsString('value=""', $html);
        $this->assertStringContainsString('>All</label>', $html);
        $this->assertStringContainsString('type="radio"', $html);
        $this->assertStringContainsString('custom-control-radio', $html);
    }

    public function testComboBoxStillClearedForSingleSelection(): void
    {
        $html = TableFilter::widget([
                    'filterArray' => ['One', 'Two', 'Three', 'Four'],
                    'multipleValues' => false,
        ]);

        $this->assertStringContainsString('<option value="" selected>', $html);
        $this->assertStringContainsString('>All</option>', $html);
        $this->assertStringContainsString('value="One"', $html);
        $this->assertStringContainsString('value="Four"', $html);
        $this->assertStringNotContainsString(' multiple', $html);
    }

    public function testSelectIsNamedForDefaultLoader(): void
    {
        $html = TableFilter::widget([
                    'filterArray' => ['One', 'Two', 'Three', 'Four'],
                    'multipleValues' => true,
        ]);

        $this->assertStringContainsString('name="application"', $html);
        $this->assertStringContainsString(' multiple', $html);
    }
}
