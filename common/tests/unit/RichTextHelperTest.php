<?php

namespace common\tests\unit;

use common\helpers\RichTextHelper;
use Codeception\Test\Unit;

class RichTextHelperTest extends Unit
{
    public function testSanitizeDoesNotAddBr()
    {
        $content = "Line 1\nLine 2";
        $result = RichTextHelper::sanitize($content);
        $this->assertStringNotContainsString('<br', $result);
    }

    public function testSanitizeMarkdownStripsTags()
    {
        $content = "Line 1<br />Line 2<b>Bold</b>";
        $result = RichTextHelper::sanitizeMarkdown($content);
        $this->assertEquals("Line 1\nLine 2Bold", $result);
    }
}
