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

    public function testRawAttributesRemainBoundToStoredValues(): void
    {
        $item = new Item();
        $item->setAttribute('name', 'Ring Mail');
        $item->setAttribute('description', 'Default raw description');

        Yii::$app->language = 'fr';
        // Raw ActiveRecord attribute getters must return stored canonical values
        $this->assertEquals('Ring Mail', $item->getAttribute('name'));
        $this->assertEquals('Default raw description', $item->getAttribute('description'));
    }

    public function testGetNameLocalizationWithAppLanguage(): void
    {
        $item = new Item();
        $item->setAttribute('name', 'Ring Mail');

        Yii::$app->language = 'en';
        $this->assertEquals('Ring Mail', $item->getName());

        Yii::$app->language = 'fr';
        $this->assertEquals('Côte de maille', $item->getName());
    }

    public function testGetNameLocalizationWithExplicitLanguage(): void
    {
        $item = new Item();
        $item->setAttribute('name', 'Ring Mail');

        Yii::$app->language = 'en';
        $this->assertEquals('Côte de maille', $item->getName('fr'));
        $this->assertEquals('Ring Mail', $item->getName('en'));
    }

    public function testGetDescriptionLocalizationWithExplicitLanguage(): void
    {
        $item = new Item();
        $item->setAttribute('name', 'Ring Mail');
        $item->setAttribute('description', 'Default raw description');

        $this->assertStringContainsString('This armor is leather armor', $item->getDescription('en'));
        $this->assertStringContainsString('Cette armure est une armure en cuir', $item->getDescription('fr'));
    }

    public function testFallbackWhenTranslationMissing(): void
    {
        $item = new Item();
        $item->setAttribute('name', 'Unknown Custom Item');
        $item->setAttribute('description', 'Custom Item Description');

        $this->assertEquals('Unknown Custom Item', $item->getName('fr'));
        $this->assertEquals('Custom Item Description', $item->getDescription('fr'));
    }

    public function testNullDescriptionWhenNoTranslationAndNullAttribute(): void
    {
        $item = new Item();
        $item->setAttribute('name', 'Basket');
        $item->setAttribute('description', null);

        $this->assertEquals('Panier', $item->getName('fr'));
        $this->assertNull($item->getDescription('fr'));
    }
}
