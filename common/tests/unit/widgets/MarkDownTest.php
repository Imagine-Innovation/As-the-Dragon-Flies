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
}
