<?php

declare(strict_types=1);

namespace common\tests\unit\models;

use Codeception\Test\Unit;
use common\components\AppStatus;
use common\models\events\GameActionEvent;
use common\models\Player;
use common\models\Quest;
use Yii;

class GameActionEventTest extends Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    public function testGetOutcomeConclusionWithArrayOutcomes()
    {
        $player = $this->getMockBuilder(Player::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();
        $player->method('__get')->willReturnCallback(function ($name) {
            if ($name === 'name') {
                return 'Gandalf';
            }
            return null;
        });

        $quest = $this->getMockBuilder(Quest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $detail = [
            'status' => AppStatus::SUCCESS,
            'outcomes' => [
                [
                    'id' => 1,
                    'name' => 'Door Opens',
                    'description' => 'The heavy oak door creaks open slowly.',
                ],
                [
                    'id' => 2,
                    'name' => 'Found Gold',
                    'description' => 'You see 10 shiny gold pieces on the floor.',
                ]
            ]
        ];

        $event = new GameActionEvent('session-123', $player, $quest, 'Open the door', $detail);

        $conclusion = $event->getOutcomeConclusion();
        verify($conclusion)->equals('Door Opens: The heavy oak door creaks open slowly.; Found Gold: You see 10 shiny gold pieces on the floor.');
    }

    public function testGetOutcomeConclusionWithObjectOutcomes()
    {
        $player = $this->getMockBuilder(Player::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();
        $player->method('__get')->willReturnCallback(function ($name) {
            if ($name === 'name') {
                return 'Gandalf';
            }
            return null;
        });

        $quest = $this->getMockBuilder(Quest::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create standard mock objects for outcomes
        $outcome1 = new \stdClass();
        $outcome1->id = 1;
        $outcome1->name = 'Door Opens';
        $outcome1->description = '<p>The heavy oak door creaks open slowly.</p>';

        $outcome2 = new \stdClass();
        $outcome2->id = 2;
        $outcome2->name = 'Found Gold';
        $outcome2->description = 'You see 10 shiny gold pieces.';

        $detail = [
            'status' => AppStatus::SUCCESS,
            'outcomes' => [$outcome1, $outcome2],
        ];

        $event = new GameActionEvent('session-123', $player, $quest, 'Open the door', $detail);

        $conclusion = $event->getOutcomeConclusion();
        // Since we call strip_tags, <p> tags should be removed from the first description
        verify($conclusion)->equals('Door Opens: The heavy oak door creaks open slowly.; Found Gold: You see 10 shiny gold pieces.');
    }

    public function testGetLocalizedDescriptionEnglish()
    {
        $player = $this->getMockBuilder(Player::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();
        $player->method('__get')->willReturnCallback(function ($name) {
            if ($name === 'name') {
                return 'Gandalf';
            }
            return null;
        });

        $quest = $this->getMockBuilder(Quest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $detail = [
            'status' => AppStatus::SUCCESS,
            'outcomes' => [
                [
                    'id' => 1,
                    'name' => 'Door Opens',
                    'description' => 'The heavy oak door creaks open slowly.',
                ]
            ]
        ];

        $event = new GameActionEvent('session-123', $player, $quest, 'Open the door', $detail);

        $desc = $event->getLocalizedDescription('en');
        verify($desc)->equals('Gandalf tried Open the door and succeeded. Door Opens: The heavy oak door creaks open slowly.');
    }

    public function testGetLocalizedDescriptionFrench()
    {
        $player = $this->getMockBuilder(Player::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__get'])
            ->getMock();
        $player->method('__get')->willReturnCallback(function ($name) {
            if ($name === 'name') {
                return 'Gandalf';
            }
            return null;
        });

        $quest = $this->getMockBuilder(Quest::class)
            ->disableOriginalConstructor()
            ->getMock();

        $detail = [
            'status' => AppStatus::SUCCESS,
            'outcomes' => [
                [
                    'id' => 1,
                    'name' => 'Door Opens',
                    'description' => 'La lourde porte s\'ouvre lentement.',
                ]
            ]
        ];

        $event = new GameActionEvent('session-123', $player, $quest, 'Open the door', $detail);

        $desc = $event->getLocalizedDescription('fr');
        verify($desc)->equals('Gandalf a tenté Open the door et a réussi. Door Opens: La lourde porte s\'ouvre lentement.');
    }
}
