<?php

namespace common\tests\unit\components;

use common\components\AppStatus;
use common\components\gameplay\QuestManager;
use common\models\Quest;
use common\models\QuestProgress;
use common\models\Mission;
use common\models\Chapter;
use common\models\Player;
use Yii;

class TestQuestManager extends QuestManager
{
    public ?Quest $mockQuest = null;
    public ?QuestProgress $mockQuestProgress = null;
    public ?QuestProgress $mockAddQuestProgress = null;
    public ?array $mockGameOverResult = null;
    public bool $gameOverCalled = false;
    public ?AppStatus $gameOverStatus = null;

    public function getQuest(): Quest
    {
        return $this->mockQuest ?? parent::getQuest();
    }

    public function getQuestProgress(): QuestProgress
    {
        return $this->mockQuestProgress ?? parent::getQuestProgress();
    }

    public function addQuestProgress(int $nextMissionId): ?QuestProgress
    {
        return $this->mockAddQuestProgress;
    }

    public function endCurrentQuestProgress(QuestProgress $questProgress, AppStatus $status = AppStatus::TERMINATED): void
    {
    }

    public function gameOver(AppStatus $status): array
    {
        $this->gameOverCalled = true;
        $this->gameOverStatus = $status;
        return $this->mockGameOverResult ?? parent::gameOver($status);
    }

    protected function createQuestEvent(
            string $eventType,
            string $eventDescription,
            ?Player $initiator,
            array $detail = [],
    ): \common\models\events\Event
    {
        return new class('1', $initiator ?? new Player(), $this->quest) extends \common\models\events\Event {
            public function getType(): string { return 'test'; }
            public function getPayload(): array { return []; }
            public function getTitle(): string { return 'test'; }
            public function getDescription(): string { return 'test'; }
            public function getMessage(): string { return 'test'; }
            public function process(): void {}
            public function toArray(): array { return []; }
        };
    }
}

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
        $hero = new Player();
        $hero->id = self::CURRENT_PLAYER_ID;
        $hero->name = 'Hero';

        $quest = new Quest();
        $quest->id = self::QUEST_ID;
        $quest->setIsNewRecord(false);
        $quest->story_id = 1;
        $quest->name = 'Test Quest';
        $quest->status = AppStatus::COMPLETED->value;
        $quest->current_player_id = self::CURRENT_PLAYER_ID;
        $quest->populateRelation('currentPlayer', $hero);

        $chapter = new Chapter();
        $chapter->id = 1;
        $chapter->story_id = 1;

        $mission = new Mission();
        $mission->id = 1;
        $mission->chapter_id = 1;
        $mission->populateRelation('chapter', $chapter);

        $progress = new QuestProgress();
        $progress->id = self::QUEST_PROGRESS_ID;
        $progress->setIsNewRecord(false);
        $progress->quest_id = self::QUEST_ID;
        $progress->mission_id = 1;
        $progress->current_player_id = self::CURRENT_PLAYER_ID;
        $progress->populateRelation('quest', $quest);
        $progress->populateRelation('mission', $mission);
        $progress->populateRelation('currentPlayer', $hero);

        $manager = new TestQuestManager(['questProgress' => $progress]);
        $manager->mockQuest = $quest;
        $manager->mockQuestProgress = $progress;

        $result = $manager->moveToNextDefaultMission();

        verify($result['error'])->false();
        $this->assertStringContainsString('is already over with status Completed', $result['msg']);
    }

    public function testMoveToNextMissionReturnsEarlyWhenAborted()
    {
        $hero = new Player();
        $hero->id = self::CURRENT_PLAYER_ID;
        $hero->name = 'Hero';

        $quest = new Quest();
        $quest->id = self::QUEST_ID;
        $quest->setIsNewRecord(false);
        $quest->story_id = 1;
        $quest->name = 'Test Quest';
        $quest->status = AppStatus::ABORTED->value;
        $quest->current_player_id = self::CURRENT_PLAYER_ID;
        $quest->populateRelation('currentPlayer', $hero);

        $chapter = new Chapter();
        $chapter->id = 1;
        $chapter->story_id = 1;

        $mission = new Mission();
        $mission->id = 1;
        $mission->chapter_id = 1;
        $mission->populateRelation('chapter', $chapter);

        $progress = new QuestProgress();
        $progress->id = self::QUEST_PROGRESS_ID;
        $progress->setIsNewRecord(false);
        $progress->quest_id = self::QUEST_ID;
        $progress->mission_id = 1;
        $progress->current_player_id = self::CURRENT_PLAYER_ID;
        $progress->populateRelation('quest', $quest);
        $progress->populateRelation('mission', $mission);
        $progress->populateRelation('currentPlayer', $hero);

        $manager = new TestQuestManager(['questProgress' => $progress]);
        $manager->mockQuest = $quest;
        $manager->mockQuestProgress = $progress;

        $result = $manager->moveToNextDefaultMission();

        verify($result['error'])->false();
        $this->assertStringContainsString('is already over with status Aborted', $result['msg']);
    }

    public function testSetNextMissionTriggersGameOverWhenAddQuestProgressFails()
    {
        $hero = new Player();
        $hero->id = self::CURRENT_PLAYER_ID;
        $hero->name = 'Hero';

        $quest = new Quest();
        $quest->id = self::QUEST_ID;
        $quest->setIsNewRecord(false);
        $quest->story_id = 1;
        $quest->name = 'Test Quest';
        $quest->current_player_id = self::CURRENT_PLAYER_ID;
        $quest->populateRelation('currentPlayer', $hero);

        $chapter = new Chapter();
        $chapter->id = 1;
        $chapter->story_id = 1;

        $mission = new Mission();
        $mission->id = 1;
        $mission->chapter_id = 1;
        $mission->populateRelation('chapter', $chapter);

        $progress = new QuestProgress();
        $progress->id = self::QUEST_PROGRESS_ID;
        $progress->setIsNewRecord(false);
        $progress->quest_id = self::QUEST_ID;
        $progress->mission_id = 1;
        $progress->current_player_id = self::CURRENT_PLAYER_ID;
        $progress->populateRelation('quest', $quest);
        $progress->populateRelation('mission', $mission);
        $progress->populateRelation('currentPlayer', $hero);

        $manager = new TestQuestManager(['questProgress' => $progress]);
        $manager->mockQuest = $quest;
        $manager->mockQuestProgress = $progress;
        $manager->mockAddQuestProgress = null;
        $manager->mockGameOverResult = ['error' => false, 'msg' => 'Game Over Success'];

        $result = $manager->moveToNextMission(101);

        verify($manager->gameOverCalled)->true();
        verify($manager->gameOverStatus)->equals(AppStatus::ABORTED);
        verify($result['msg'])->equals('Game Over Success');
    }
}
