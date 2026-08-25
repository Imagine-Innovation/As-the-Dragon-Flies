<?php

namespace common\tests\unit\components;

use Codeception\Test\Unit;
use common\widgets\MarkDown;

class DialogLogTest extends Unit
{
    public function testDialogLogLineFormattingAndAccumulation(): void
    {
        $playerName = 'Gandalf';
        $npcName = 'Gurdil';

        $dialogLog = null;
        $lines = [];

        if (!empty($dialogLog)) {
            $lines[] = $dialogLog;
        }

        // Turn 1
        $reply1 = 'Hello, dwarf!';
        $dialog1 = 'Greetings, wizard!';
        $lines[] = "**{$playerName}**: {$reply1}";
        $lines[] = "**{$npcName}**: {$dialog1}";

        $dialogLog = implode("\n", $lines);

        $expectedTurn1 = "**Gandalf**: Hello, dwarf!\n**Gurdil**: Greetings, wizard!";
        $this->assertEquals($expectedTurn1, $dialogLog);

        // Turn 2 accumulation
        $lines = [$dialogLog];
        $reply2 = 'Do you have any secret weapons?';
        $dialog2 = 'Only the finest axes in the kingdom.';
        $lines[] = "**{$playerName}**: {$reply2}";
        $lines[] = "**{$npcName}**: {$dialog2}";

        $dialogLog = implode("\n", $lines);

        $expectedTurn2 = "**Gandalf**: Hello, dwarf!\n**Gurdil**: Greetings, wizard!\n**Gandalf**: Do you have any secret weapons?\n**Gurdil**: Only the finest axes in the kingdom.";
        $this->assertEquals($expectedTurn2, $dialogLog);
    }

    public function testDialogLogMarkdownRendering(): void
    {
        $dialogLog = "**Gandalf**: Hello, dwarf!\n**Gurdil**: Greetings, wizard!";
        $html = MarkDown::widget(['content' => $dialogLog]);

        $this->assertStringContainsString('<strong>Gandalf</strong>: Hello, dwarf!', $html);
        $this->assertStringContainsString('<strong>Gurdil</strong>: Greetings, wizard!', $html);
    }
}
