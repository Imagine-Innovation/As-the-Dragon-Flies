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
        // Raw ActiveRecord attributes return stored canonical values
        $this->assertEquals('Ring Mail', $item->name);
        $this->assertEquals('Default raw description', $item->description);
    }

    public function testTranslationLookupForNameAndDescription(): void
    {
        $item = new Item();
        $item->setAttribute('name', 'Ring Mail');

        $frName = Yii::t('app/item', $item->name, [], 'fr');
        $this->assertEquals('Côte de maille', $frName);

        $enName = Yii::t('app/item', $item->name, [], 'en');
        $this->assertEquals('Ring Mail', $enName);

        $frDesc = Yii::t('app/item', $item->name . ' description', [], 'fr');
        $this->assertStringContainsString('Cette armure est une armure en cuir', $frDesc);

        $enDesc = Yii::t('app/item', $item->name . ' description', [], 'en');
        $this->assertStringContainsString('This armor is leather armor', $enDesc);
    }
}
