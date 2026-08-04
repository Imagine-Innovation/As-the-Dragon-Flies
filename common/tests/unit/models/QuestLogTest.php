<?php
declare(strict_types=1);

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\models\QuestLog;

class QuestLogTest extends Unit
{

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    public function testRulesDefinition(): void
    {
        $model = new QuestLog();
        $rules = $model->rules();

        $foundOutcomeId = false;
        $requiredAttributes = [];
        $integerAttributes = [];

        foreach ($rules as $rule) {
            $attributes = (array) $rule[0];
            $validatorName = $rule[1];

            if (in_array('outcome_id', $attributes, true)) {
                $foundOutcomeId = true;
            }

            if ($validatorName === 'required') {
                $requiredAttributes = array_merge($requiredAttributes, $attributes);
            }

            if ($validatorName === 'integer') {
                $integerAttributes = array_merge($integerAttributes, $attributes);
            }
        }

        // outcome_id must NOT be referenced in rules under the new schema
        $this->assertFalse($foundOutcomeId, 'outcome_id should not exist in the validation rules');

        // quest_id and player_id must be required
        $this->assertContains('quest_id', $requiredAttributes);
        $this->assertContains('player_id', $requiredAttributes);

        // quest_id, player_id, round, dc, and action_success must be integers
        $this->assertContains('quest_id', $integerAttributes);
        $this->assertContains('player_id', $integerAttributes);
        $this->assertContains('round', $integerAttributes);
        $this->assertContains('dice_roll', $integerAttributes);
        $this->assertContains('result', $integerAttributes);
    }

    public function testAttributeLabels(): void
    {
        $model = new QuestLog();
        $labels = $model->attributeLabels();

        // outcome_id should not exist in the attribute labels anymore
        $this->assertArrayNotHasKey('outcome_id', $labels, 'outcome_id should not be present in attribute labels');

        $this->assertEquals('Round', $labels['round']);
        $this->assertEquals('Chapter name', $labels['chapter_name']);
        $this->assertEquals('Mission name', $labels['mission_name']);
        $this->assertEquals('Action name', $labels['action_name']);
    }
}
