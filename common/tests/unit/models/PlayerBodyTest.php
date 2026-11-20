<?php
declare(strict_types=1);

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\Player;
use common\models\PlayerBody;
use common\models\PlayerItem;

class PlayerBodyTest extends Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    public function testRulesDefinition(): void
    {
        $model = new PlayerBody();
        $rules = $model->rules();

        $slots = ['head_item_id', 'chest_item_id', 'right_hand_item_id', 'left_hand_item_id', 'back_item_id'];
        $validatedExistSlots = [];

        foreach ($rules as $rule) {
            $attributes = (array) $rule[0];
            $validatorName = $rule[1];

            if ($validatorName === 'exist' && isset($rule['targetClass']) && $rule['targetClass'] === PlayerItem::class) {
                // The attribute list should only contain the slot attribute, NOT player_id
                $this->assertNotContains('player_id', $attributes, 'player_id must not be validated in slot exist rule');
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

        $playerBody = PlayerBody::findOne(['player_id' => $player->id]) ?? new PlayerBody();
        $playerBody->player_id = $player->id;
        $playerBody->head_item_id = null;
        $playerBody->chest_item_id = null;
        $playerBody->right_hand_item_id = null;
        $playerBody->left_hand_item_id = null;
        $playerBody->back_item_id = null;

        $isValid = $playerBody->validate();
        $this->assertTrue($isValid, 'PlayerBody should be valid when all equipment slots are null: ' . json_encode($playerBody->errors));
    }

    public function testValidationFailsOnSlotWhenItemNotOwned(): void
    {
        $player = Player::find()->one();
        if ($player === null) {
            $this->markTestSkipped('No player in database');
        }

        $playerBody = PlayerBody::findOne(['player_id' => $player->id]) ?? new PlayerBody();
        $playerBody->player_id = $player->id;
        $playerBody->back_item_id = 99999999; // non-existent item

        $isValid = $playerBody->validate();
        $this->assertFalse($isValid, 'PlayerBody must fail validation when an unowned item is equipped');
        $this->assertArrayHasKey('back_item_id', $playerBody->errors, 'The validation error should be on back_item_id');
        $this->assertArrayNotHasKey('player_id', $playerBody->errors, 'player_id should not receive an error for an invalid slot item');
    }
}

