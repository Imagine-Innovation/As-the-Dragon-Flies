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

    protected const QUEST_ID = 10;
    protected const QUEST_PROGRESS_ID = 24;
    protected const CURRENT_PLAYER_ID = 35;

    /**
     * @var \common\tests\UnitTester
     */
    protected $tester;

    public function testMoveToNextMissionReturnsEarlyWhenCompleted()
    {
        $quest = $this->getMockBuilder(Quest::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['attributes'])
                ->getMock();
        $quest->method('attributes')->willReturn(['id', 'status', 'current_player_id', 'current_chapter_id']);
        $quest->id = self::QUEST_ID;
        $quest->status = AppStatus::COMPLETED->value;

        $manager = $this->getMockBuilder(QuestManager::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getQuestProgress'])
                ->getMock();

        $manager->quest = $quest;

        $result = $manager->moveToNextMission(101);

        $this->assertFalse($result['error']);
        $this->assertStringContainsString('is already over with status Completed', $result['msg']);
    }

    public function testMoveToNextMissionReturnsEarlyWhenAborted()
    {
        $quest = $this->getMockBuilder(Quest::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['attributes'])
                ->getMock();
        $quest->method('attributes')->willReturn(['id', 'status', 'current_player_id', 'current_chapter_id']);
        $quest->id = self::QUEST_ID;
        $quest->status = AppStatus::ABORTED->value;

        $manager = $this->getMockBuilder(QuestManager::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getQuestProgress'])
                ->getMock();

        $manager->quest = $quest;

        $result = $manager->moveToNextMission(101);

        $this->assertFalse($result['error']);
        $this->assertStringContainsString('is already over with status Aborted', $result['msg']);
    }

    public function testSetNextMissionTriggersGameOverWhenAddQuestProgressFails()
    {
        $hero = $this->getMockBuilder(Player::class)->disableOriginalConstructor()->getMock();
        $hero->id = self::CURRENT_PLAYER_ID;
        $hero->name = 'Hero';

        $quest = $this->getMockBuilder(Quest::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getCurrentPlayer', 'attributes'])
                ->getMock();
        $quest->method('attributes')->willReturn(['id', 'status', 'current_player_id', 'current_chapter_id']);
        $quest->id = self::QUEST_ID;
        $quest->current_player_id = self::CURRENT_PLAYER_ID;
        $quest->method('getCurrentPlayer')->willReturn($hero);

        $progress = $this->getMockBuilder(QuestProgress::class)->disableOriginalConstructor()->getMock();
        $progress->id = self::QUEST_PROGRESS_ID;

        $manager = $this->getMockBuilder(QuestManager::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getQuestProgress', 'endCurrentQuestProgress', 'addQuestProgress', 'gameOver'])
                ->getMock();

        $manager->quest = $quest;
        $manager->method('getQuestProgress')->willReturn($progress);
        $manager->method('addQuestProgress')->willReturn(null);

        $manager->expects($this->once())
                ->method('gameOver')
                ->with(AppStatus::ABORTED)
                ->willReturn(['error' => false, 'msg' => 'Game Over Success']);

        $result = $manager->moveToNextMission(101); // Triggering setNextMission through moveToNextMission

        $this->assertEquals('Game Over Success', $result['msg']);
    }
}
