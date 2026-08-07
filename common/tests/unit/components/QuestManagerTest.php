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

        $manager = new TestQuestManager();
        $manager->mockQuest = $quest;

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

        $manager = new TestQuestManager();
        $manager->mockQuest = $quest;

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

        $manager = new TestQuestManager();
        $manager->mockQuest = $quest;
        $manager->mockQuestProgress = $progress;
        $manager->mockAddQuestProgressValue = null; // simulate fail returning null
        $manager->mockGameOverValue = ['error' => false, 'msg' => 'Game Over Success'];

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

        $manager = new TestQuestManager();
        $manager->mockQuest = $quest;
        $manager->mockQuestProgress = $currentProgress;
        $manager->mockAddQuestProgressValue = $nextProgress;
        $manager->mockCreateQuestEventValue = $mockEvent;
        $manager->mockGetNextMissionDetailValue = [
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
        ];

        $result = $manager->moveToNextMission(1);

        $this->assertFalse($result['error']);
        $this->assertEquals('next-mission', $result['event']);
    }
}

/**
 * Test-specific subclass of QuestManager used to override protected methods for testing,
 * satisfying the code review requirement to avoid mocking the main class directly.
 */
class TestQuestManager extends QuestManager
{
    public ?Quest $mockQuest = null;
    public ?QuestProgress $mockQuestProgress = null;
    public ?QuestProgress $mockAddQuestProgressValue = null;
    public ?array $mockGameOverValue = null;
    public ?array $mockGetNextMissionDetailValue = null;
    public ?\common\models\events\Event $mockCreateQuestEventValue = null;

    public function __construct()
    {
        // Disable original constructor's DB/property access
    }

    protected function getQuest(): Quest
    {
        if ($this->mockQuest !== null) {
            return $this->mockQuest;
        }
        throw new \RuntimeException('Mock Quest is missing in TestQuestManager.');
    }

    protected function getQuestProgress(): QuestProgress
    {
        if ($this->mockQuestProgress !== null) {
            return $this->mockQuestProgress;
        }
        throw new \RuntimeException('Mock QuestProgress is missing in TestQuestManager.');
    }

    protected function getPlayer(): Player
    {
        return $this->getQuest()->currentPlayer;
    }

    protected function endCurrentQuestProgress(QuestProgress $questProgress, AppStatus $status = AppStatus::TERMINATED): void
    {
        // No-op for testing
    }

    protected function addQuestProgress(int $missionId): ?QuestProgress
    {
        // Note: we can't type check the parameter in override if base class doesn't,
        // but both match `int $missionId`. Let's just return the mock value if configured.
        return $this->mockAddQuestProgressValue;
    }

    protected function gameOver(AppStatus $status): array
    {
        if ($this->mockGameOverValue !== null) {
            return $this->mockGameOverValue;
        }
        return parent::gameOver($status);
    }

    protected function getNextMissionDetail(QuestProgress $currentQuestProgress, QuestProgress $nextQuestProgress): array
    {
        if ($this->mockGetNextMissionDetailValue !== null) {
            return $this->mockGetNextMissionDetailValue;
        }
        return parent::getNextMissionDetail($currentQuestProgress, $nextQuestProgress);
    }

    protected function createQuestEvent(
            string $eventType,
            string $eventDescription,
            ?Player $initiator,
            array $detail = [],
    ): \common\models\events\Event
    {
        if ($this->mockCreateQuestEventValue !== null) {
            return $this->mockCreateQuestEventValue;
        }
        return parent::createQuestEvent($eventType, $eventDescription, $initiator, $detail);
    }
}
