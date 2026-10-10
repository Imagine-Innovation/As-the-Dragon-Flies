<?php

namespace common\tests\unit\models\events;

use Codeception\Test\Unit;
use common\models\events\GameOverEvent;
use common\models\events\NextMissionEvent;
use common\models\events\NextTurnEvent;
use common\models\events\PlayerJoiningEvent;
use common\models\events\PlayerQuittingEvent;
use common\models\events\QuestStartingEvent;
use common\models\Player;
use common\models\Quest;

class TestPlayer extends Player
{
    public string $name = '';
    public ?int $id = 1;

    public function __construct(string $name = 'Player1')
    {
        $this->name = $name;
        $this->id = 1;
    }
}

class TestQuest extends Quest
{
    public string $name = '';
    public ?int $id = 1;

    public function __construct(string $name = 'Quest1')
    {
        $this->name = $name;
        $this->id = 1;
    }
}

class EventMessageTest extends Unit
{
    public function testPlayerJoiningEventMessageLocalization(): void
    {
        $player = new TestPlayer('Aragorn');
        $quest = new TestQuest('The Ring');

        $eventEn = new PlayerJoiningEvent('session1', $player, $quest, ['language' => 'en']);
        $this->assertEquals('Aragorn joins the quest', $eventEn->getMessage());

        $eventFr = new PlayerJoiningEvent('session1', $player, $quest, ['language' => 'fr']);
        $this->assertEquals('Aragorn rejoint la quête', $eventFr->getMessage());
    }

    public function testPlayerQuittingEventMessageLocalization(): void
    {
        $player = new TestPlayer('Gimli');
        $quest = new TestQuest('Moria');

        $eventEn = new PlayerQuittingEvent('session1', $player, $quest, 'Need rest', ['language' => 'en']);
        $this->assertEquals('Gimli quits the quest', $eventEn->getMessage());

        $eventFr = new PlayerQuittingEvent('session1', $player, $quest, 'Need rest', ['language' => 'fr']);
        $this->assertEquals('Gimli quitte la quête', $eventFr->getMessage());
    }

    public function testQuestStartingEventMessageLocalization(): void
    {
        $player = new TestPlayer('Frodo');
        $quest = new TestQuest('Doom');

        $eventEn = new QuestStartingEvent('session1', $player, $quest, ['language' => 'en']);
        $this->assertEquals('Quest Doom starting', $eventEn->getMessage());

        $eventFr = new QuestStartingEvent('session1', $player, $quest, ['language' => 'fr']);
        $this->assertEquals('Lancement de la quête « Doom »', $eventFr->getMessage());
    }

    public function testGameOverEventMessageLocalization(): void
    {
        $player = new TestPlayer('Boromir');
        $quest = new TestQuest('Gondor');

        $detail = [
            'playerName' => 'Boromir',
            'questName' => 'Gondor',
            'status' => 'ABORTED',
        ];

        $eventEn = new GameOverEvent('session1', $player, $quest, 'Game over', $detail);
        $eventEn->language = 'en';
        $this->assertEquals('Boromir ended quest “Gondor” (ABORTED)', $eventEn->getMessage());

        $eventFr = new GameOverEvent('session1', $player, $quest, 'Game over', $detail);
        $eventFr->language = 'fr';
        $this->assertEquals('Boromir a terminé la quête « Gondor » (ABORTED)', $eventFr->getMessage());
    }

    public function testNextMissionEventMessageLocalization(): void
    {
        $player = new TestPlayer('Legolas');
        $quest = new TestQuest('Mirkwood');

        $detail = [
            'currentPlayerName' => 'Legolas',
            'currentMissionName' => 'Mission 1',
            'nextPlayerName' => 'Gimli',
            'nextMissionName' => 'Mission 2',
        ];

        $eventEn = new NextMissionEvent('session1', $player, $quest, 'Next mission', $detail);
        $eventEn->language = 'en';
        $this->assertEquals('Legolas completed “Mission 1”. Gimli’s turn: “Mission 2”', $eventEn->getMessage());

        $eventFr = new NextMissionEvent('session1', $player, $quest, 'Next mission', $detail);
        $eventFr->language = 'fr';
        $this->assertEquals('Legolas a terminé « Mission 1 ». Tour de Gimli : « Mission 2 »', $eventFr->getMessage());
    }

    public function testNextTurnEventMessageLocalization(): void
    {
        $player = new TestPlayer('Gandalf');
        $quest = new TestQuest('Isengard');

        $detail = [
            'currentPlayerName' => 'Gandalf',
            'nextPlayerName' => 'Pippin',
        ];

        $eventEn = new NextTurnEvent('session1', $player, $quest, 'Next turn', $detail);
        $eventEn->language = 'en';
        $this->assertEquals('Gandalf finished turn. Pippin’s turn to play.', $eventEn->getMessage());

        $eventFr = new NextTurnEvent('session1', $player, $quest, 'Next turn', $detail);
        $eventFr->language = 'fr';
        $this->assertEquals('Gandalf a fini son tour. Au tour de Pippin !', $eventFr->getMessage());
    }
}
