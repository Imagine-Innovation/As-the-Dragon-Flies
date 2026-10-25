<?php
declare(strict_types=1);

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\Item;
use Yii;

class ItemTest extends Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        parent::_before();
        Yii::$app->language = 'en';
    }

    public function testGetNameLocalization(): void
    {
        $item = new Item();
        $item->setAttribute('name', 'Ring Mail');

        Yii::$app->language = 'en';
        $this->assertEquals('Ring Mail', $item->name);

        Yii::$app->language = 'fr';
        $this->assertEquals('Côte de maille', $item->name);
    }

    public function testGetDescriptionLocalization(): void
    {
        $item = new Item();
        $item->setAttribute('name', 'Ring Mail');
        $item->setAttribute('description', 'Default description');

        Yii::$app->language = 'en';
        $this->assertStringContainsString('This armor is leather armor', $item->description);

        Yii::$app->language = 'fr';
        $this->assertStringContainsString('Cette armure est une armure en cuir', $item->description);
    }

    public function testFallbackWhenTranslationMissing(): void
    {
        $item = new Item();
        $item->setAttribute('name', 'Unknown Custom Item');
        $item->setAttribute('description', 'Custom Item Description');

        Yii::$app->language = 'fr';
        $this->assertEquals('Unknown Custom Item', $item->name);
        $this->assertEquals('Custom Item Description', $item->description);
    }

    public function testNullDescriptionWhenNoTranslationAndNullAttribute(): void
    {
        $item = new Item();
        $item->setAttribute('name', 'Basket');
        $item->setAttribute('description', null);

        Yii::$app->language = 'fr';
        $this->assertEquals('Panier', $item->name);
        $this->assertNull($item->description);
    }
}
