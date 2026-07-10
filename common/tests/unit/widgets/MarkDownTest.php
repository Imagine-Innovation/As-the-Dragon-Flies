<?php

namespace common\tests\unit\widgets;

use common\widgets\MarkDown;
use common\tests\UnitTester;

class MarkDownTest extends \Codeception\Test\Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    public function testRenderBasicMarkdown()
    {
        $content = "Line 1\nLine 2";
        $html = MarkDown::widget(['content' => $content]);

        $this->assertStringContainsString('<p class="mb-3">Line 1</p>', $html);
        $this->assertStringContainsString('<p class="mb-3">Line 2</p>', $html);
    }

    public function testRenderSpecialMarkers()
    {
        $content = "++Scroll line\n--Dwarvish line";
        $html = MarkDown::widget(['content' => $content]);

        $this->assertStringContainsString('<p class="mb-3 text-scroll">Scroll line</p>', $html);
        $this->assertStringContainsString('<p class="mb-3 text-dwarvish">Dwarvish line</p>', $html);
    }

    public function testRenderScrollBlock()
    {
        $content = "§§\nInside scroll\n§§";
        $html = MarkDown::widget(['content' => $content]);

        $this->assertStringContainsString('<div class="scroll">', $html);
        $this->assertStringContainsString('<p class="mb-3">Inside scroll</p>', $html);
        $this->assertStringContainsString('</div>', $html);
    }

    public function testRenderHorizontalRule()
    {
        $content = "Text before\n---\nText after";
        $html = MarkDown::widget(['content' => $content]);

        $this->assertStringContainsString('<hr class="my-4">', $html);
    }

    public function testSecurity()
    {
        $content = "<script>alert('XSS')</script> [Link](javascript:alert('XSS'))";
        $html = MarkDown::widget(['content' => $content]);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringNotContainsString('href="javascript:', $html);
    }

    public function testPlaceholderReplacements()
    {
        // 1. Example from user: It is *{playerName}'s* turn -> It is <em>Gandalf&#039;s</em> turn (or <strong> with **).
        // Let's test standard emphasis * and ** to be thorough.
        $content = "It is *{playerName}'s* turn";
        $html = MarkDown::widget(['content' => $content, 'placeholder:playerName' => 'Gandalf']);
        $this->assertStringContainsString("It is <em>Gandalf&#039;s</em> turn", $html);

        $contentBold = "It is **{playerName}'s** turn";
        $htmlBold = MarkDown::widget(['content' => $contentBold, 'placeholder:playerName' => 'Gandalf']);
        $this->assertStringContainsString("It is <strong>Gandalf&#039;s</strong> turn", $htmlBold);

        // 2. No placeholder provided
        $content = "It is *{playerName}'s* turn";
        $html = MarkDown::widget(['content' => $content]);
        // Since no placeholders were configured at all, return converted markdown as is (leaving {playerName})
        $this->assertStringContainsString("It is <em>{playerName}&#039;s</em> turn", $html);

        // 3. Null placeholder (should remove it if placeholders are configured)
        $content = "It is *{playerName}'s* turn for {questName}";
        $html = MarkDown::widget(['content' => $content, 'placeholder:playerName' => null, 'placeholder:questName' => 'Ring']);
        $this->assertStringContainsString("It is <em>&#039;s</em> turn for Ring", $html);

        // 4. Secure placeholder values (XSS protection)
        $content = "Hello {playerName}";
        $html = MarkDown::widget(['content' => $content, 'placeholder:playerName' => "<script>alert('XSS')</script>"]);
        $this->assertStringNotContainsString("<script>", $html);
        $this->assertStringContainsString("&lt;script&gt;alert(&#039;XSS&#039;)&lt;/script&gt;", $html);

        // 5. Arbitrary, multiple placeholders and case-sensitivity
        $content = "Hello {playerName}, welcome to {placeName}. Do you like {playername}?";
        $html = MarkDown::widget([
            'content' => $content,
            'placeholder:playerName' => 'Frodo',
            'placeholder:placeName' => 'the Shire',
            'placeholder:playername' => 'Sam'
        ]);
        $this->assertStringContainsString("Hello Frodo, welcome to the Shire. Do you like Sam?", $html);

        // 6. Unknown config option without prefix should throw an UnknownPropertyException from Yii base class
        try {
            MarkDown::widget([
                'content' => 'Hello',
                'invalidConfigKey' => 'Some Value'
            ]);
            $this->fail('Expected UnknownPropertyException was not thrown for invalidConfigKey');
        } catch (\yii\base\UnknownPropertyException $e) {
            $this->assertStringContainsString('invalidConfigKey', $e->getMessage());
        }

        // 7. Empty placeholder name (just "placeholder:") should treat it as invalid config key and throw UnknownPropertyException
        try {
            MarkDown::widget([
                'content' => 'Hello',
                'placeholder:' => 'Some Value'
            ]);
            $this->fail('Expected UnknownPropertyException was not thrown for empty placeholder name');
        } catch (\yii\base\UnknownPropertyException $e) {
            $this->assertStringContainsString('placeholder:', $e->getMessage());
        }
    }
}
