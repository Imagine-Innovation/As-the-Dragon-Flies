<?php
declare(strict_types=1);

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\Player;
use common\models\PlayerItem;

class PlayerTest extends Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    public function testRulesDefinition(): void
    {
        $model = new Player();
        $rules = $model->rules();

        $slots = ['head_item_id', 'chest_item_id', 'right_hand_item_id', 'left_hand_item_id', 'back_item_id'];
        $validatedExistSlots = [];

        foreach ($rules as $rule) {
            $attributes = (array) $rule[0];
            $validatorName = $rule[1];

            if ($validatorName === 'exist' && isset($rule['targetClass']) && $rule['targetClass'] === PlayerItem::class) {
                // The attribute list should only contain the slot attributes, NOT the primary key
                $this->assertNotContains('id', $attributes, 'id must not be validated in slot exist rule');
                foreach ($attributes as $attr) {
                    $validatedExistSlots[] = $attr;
                }
            }
        }

        foreach ($slots as $slot) {
            $this->assertContains($slot, $validatedExistSlots, "Slot {$slot} must have an exist validator for PlayerItem");
        }
    }

    public function testValidationPassesWithNullSlots(): void
    {
        // Find existing player or create mock scenario
        $player = Player::find()->one();
        if ($player === null) {
            $this->markTestSkipped('No player in database');
        }

        $player->head_item_id = null;
        $player->chest_item_id = null;
        $player->right_hand_item_id = null;
        $player->left_hand_item_id = null;
        $player->back_item_id = null;

        $isValid = $player->validate();
        $this->assertTrue($isValid, 'Player should be valid when all equipment slots are null: ' . json_encode($player->errors));
    }

    public function testValidationFailsOnSlotWhenItemNotOwned(): void
    {
        $player = Player::find()->one();
        if ($player === null) {
            $this->markTestSkipped('No player in database');
        }

        $player->back_item_id = 99999999; // non-existent item

        $isValid = $player->validate();
        $this->assertFalse($isValid, 'Player must fail validation when an unowned item is equipped');
        $this->assertArrayHasKey('back_item_id', $player->errors, 'The validation error should be on back_item_id');
    }
}
