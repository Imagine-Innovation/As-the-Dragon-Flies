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
                ->onlyMethods(['getAttributes', '__get'])
                ->getMock();
        $quest->method('__get')->willReturnMap([
            ['id', self::QUEST_ID],
            ['status', AppStatus::COMPLETED->value],
        ]);

        $manager = $this->getMockBuilder(QuestManager::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getQuest'])
                ->getMock();

        $manager->method('getQuest')->willReturn($quest);

        $result = $manager->moveToNextMission();

        $this->assertFalse($result['error']);
        $this->assertStringContainsString('is already over with status Completed', $result['msg']);
    }

    public function testMoveToNextMissionReturnsEarlyWhenAborted()
    {
        $quest = $this->getMockBuilder(Quest::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getAttributes', '__get'])
                ->getMock();
        $quest->method('__get')->willReturnMap([
            ['id', self::QUEST_ID],
            ['status', AppStatus::ABORTED->value],
        ]);

        $manager = $this->getMockBuilder(QuestManager::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getQuest'])
                ->getMock();

        $manager->method('getQuest')->willReturn($quest);

        $result = $manager->moveToNextMission();

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
                ->onlyMethods(['getCurrentPlayer', '__get'])
                ->getMock();
        $quest->method('__get')->willReturnMap([
            ['id', self::QUEST_ID],
            ['current_player_id', self::CURRENT_PLAYER_ID],
            ['currentPlayer', $hero],
        ]);
        $quest->method('getCurrentPlayer')->willReturn($hero);

        $progress = $this->getMockBuilder(QuestProgress::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['__get'])
                ->getMock();
        $progress->method('__get')->willReturnMap([
            ['id', self::QUEST_PROGRESS_ID],
        ]);

        $manager = $this->getMockBuilder(QuestManager::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getQuest', 'getQuestProgress', 'endCurrentQuestProgress', 'addQuestProgress', 'gameOver'])
                ->getMock();

        $manager->method('getQuest')->willReturn($quest);
        $manager->method('getQuestProgress')->willReturn($progress);
        $manager->method('addQuestProgress')->willReturn(null);

        $manager->expects($this->once())
                ->method('gameOver')
                ->with(AppStatus::ABORTED)
                ->willReturn(['error' => false, 'msg' => 'Game Over Success']);

        $result = $manager->moveToNextMission(101); // Triggering setNextMission through moveToNextMission

        $this->assertEquals('Game Over Success', $result['msg']);
    }

    public function testMoveToNextMissionWhenReturningToPreviousMission()
    {
        $hero = $this->getMockBuilder(Player::class)->disableOriginalConstructor()->getMock();
        $hero->id = self::CURRENT_PLAYER_ID;
        $hero->name = 'Hero';

        $quest = $this->getMockBuilder(Quest::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getCurrentPlayer', '__get', 'save'])
                ->getMock();
        $quest->method('__get')->willReturnMap([
            ['id', self::QUEST_ID],
            ['current_player_id', self::CURRENT_PLAYER_ID],
            ['currentPlayer', $hero],
            ['status', AppStatus::PLAYING->value],
            ['current_chapter_id', 1],
        ]);
        $quest->method('getCurrentPlayer')->willReturn($hero);

        $currentProgress = $this->getMockBuilder(QuestProgress::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['__get'])
                ->getMock();
        $currentProgress->method('__get')->willReturnMap([
            ['id', self::QUEST_PROGRESS_ID],
            ['mission_id', 3], // currently on mission 3
        ]);

        $nextProgress = $this->getMockBuilder(QuestProgress::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['__get'])
                ->getMock();

        $mockAction = $this->getMockBuilder(\common\models\QuestAction::class)
                ->disableOriginalConstructor()
                ->getMock();

        $mockMission = $this->getMockBuilder(\common\models\Mission::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['__get'])
                ->getMock();
        $mockMission->method('__get')->willReturnMap([
            ['chapter_id', 1],
        ]);

        $nextProgress->method('__get')->willReturnMap([
            ['id', 123],
            ['mission_id', 1], // returning to mission 1
            ['remainingActions', [$mockAction]], // has remaining actions
            ['mission', $mockMission],
        ]);

        $mockEvent = $this->getMockBuilder(\common\models\events\NextMissionEvent::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['toArray'])
                ->getMock();
        $mockEvent->method('toArray')->willReturn(['status' => 'ok']);

        $manager = $this->getMockBuilder(QuestManager::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getQuest', 'getQuestProgress', 'endCurrentQuestProgress', 'addQuestProgress', 'synchronizeChapterId', 'getNextMissionDetail', 'createQuestEvent'])
                ->getMock();

        $manager->method('getQuest')->willReturn($quest);
        $manager->method('getQuestProgress')->willReturn($currentProgress);
        $manager->method('addQuestProgress')->with(1)->willReturn($nextProgress);
        $manager->method('createQuestEvent')->willReturn($mockEvent);

        $manager->method('getNextMissionDetail')->willReturn([
            'currentMissionId' => 3,
            'currentMissionName' => 'Mission 3',
            'currentPlayerId' => self::CURRENT_PLAYER_ID,
            'currentPlayerName' => 'Hero',
            'nextMissionId' => 1,
            'nextMissionName' => 'Mission 1',
            'nextPlayerId' => self::CURRENT_PLAYER_ID,
            'nextPlayerName' => 'Hero',
            'nextQuestProgressId' => 123,
            'timestamp' => time(),
        ]);

        $result = $manager->moveToNextMission(1);

        $this->assertFalse($result['error']);
        $this->assertEquals('next-mission', $result['event']);
    }
}
