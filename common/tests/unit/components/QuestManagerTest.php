<?php

namespace common\tests\unit\components;

use common\components\AppStatus;
use common\components\gameplay\QuestManager;
use common\models\Quest;
use common\models\QuestProgress;
use common\models\Player;
use Yii;

class QuestManagerTest extends \Codeception\Test\Unit
{
    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    public function testMoveToNextMissionReturnsEarlyWhenCompleted()
    {
        $quest = $this->getMockBuilder(Quest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttributes'])
            ->getMock();
        $quest->id = 99;
        $quest->status = AppStatus::COMPLETED->value;

        $manager = $this->getMockBuilder(QuestManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuest'])
            ->getMock();

        $manager->method('getQuest')->willReturn($quest);

        $result = $manager->moveToNextMission();

        verify($result['error'])->false();
        verify($result['msg'])->contains('is already over with status Completed');
    }

    public function testMoveToNextMissionReturnsEarlyWhenAborted()
    {
        $quest = $this->getMockBuilder(Quest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getAttributes'])
            ->getMock();
        $quest->id = 99;
        $quest->status = AppStatus::ABORTED->value;

        $manager = $this->getMockBuilder(QuestManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuest'])
            ->getMock();

        $manager->method('getQuest')->willReturn($quest);

        $result = $manager->moveToNextMission();

        verify($result['error'])->false();
        verify($result['msg'])->contains('is already over with status Aborted');
    }

    public function testSetNextMissionTriggersGameOverWhenAddQuestProgressFails()
    {
        $hero = $this->getMockBuilder(Player::class)->disableOriginalConstructor()->getMock();
        $hero->id = 5;
        $hero->name = 'Hero';

        $quest = $this->getMockBuilder(Quest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getCurrentPlayer'])
            ->getMock();
        $quest->id = 99;
        $quest->current_player_id = 5;
        $quest->method('getCurrentPlayer')->willReturn($hero);
        $quest->currentPlayer = $hero;

        $progress = $this->getMockBuilder(QuestProgress::class)->disableOriginalConstructor()->getMock();
        $progress->id = 135;

        $manager = $this->getMockBuilder(QuestManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getQuest', 'getQuestProgress', 'endCurrentQuestProgress', 'addQuestProgress', 'gameOver'])
            ->getMock();

        $manager->method('getQuest')->willReturn($quest);
        $manager->method('getQuestProgress')->willReturn($progress);
        $manager->method('addQuestProgress')->willReturn(null);

        $manager->expects($this->once())
            ->method('gameOver')
            ->with(AppStatus::COMPLETED)
            ->willReturn(['error' => false, 'msg' => 'Game Over Success']);

        $result = $manager->moveToNextMission(101); // Triggering setNextMission through moveToNextMission

        verify($result['msg'])->equals('Game Over Success');
    }
}
