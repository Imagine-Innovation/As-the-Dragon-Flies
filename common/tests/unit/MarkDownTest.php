<?php

namespace common\tests\unit;

use common\widgets\MarkDown;
use Codeception\Test\Unit;

class MarkDownTest extends Unit
{
    private function normalize($html)
    {
        // Remove all newlines and multiple spaces for easier comparison
        $html = str_replace(["\r", "\n"], ' ', $html);
        return preg_replace('/\s+/', ' ', trim($html));
    }

    public function testRenderMarkdownWithBr()
    {
        $content = "Line 1<br />Line 2<br>Line 3";
        $expected = '<p class="mb-3">Line 1 Line 2 Line 3 </p>';
        $result = MarkDown::widget(['content' => $content]);
        $this->assertEquals($this->normalize($expected), $this->normalize($result));
    }

    public function testRenderMarkdownWithParagraphsAndBr()
    {
        $content = "Para 1<br /><br />Para 2";
        $result = MarkDown::widget(['content' => $content]);

        $normalizedResult = $this->normalize($result);
        $this->assertStringContainsString('<p class="mb-3">Para 1 </p>', $normalizedResult);
        $this->assertStringContainsString('<p class="mb-3">Para 2 </p>', $normalizedResult);
    }

    public function testListBugFix()
    {
        $content = "Just a paragraph";
        $result = MarkDown::widget(['content' => $content]);
        $this->assertStringNotContainsString('</ul>', $result);
        $this->assertStringNotContainsString('</ol>', $result);
    }
}
