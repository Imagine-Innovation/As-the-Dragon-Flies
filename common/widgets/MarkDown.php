<?php

namespace common\widgets;

use common\helpers\RichTextHelper;
use yii\base\Widget;

/**
 * MarkDown widget renders markdown content from a database field.
 * Supported: Headers (H1-H6), Bold, Italic, Lists (Ordered/Unordered), Links.
 *
 * Usage:
 * <?= \common\widgets\MarkDown::widget(['content' => $model->description]) ?>
 */
class MarkDown extends Widget
{

    /**
     * @var string|null The markdown content to render.
     */
    public ?string $content = null;

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        if (empty($this->content)) {
            return '';
        }

        return $this->renderMarkdown($this->content);
    }

    /**
     * Closes an HTML tag if it is currently marked as open.
     * Always returns false to indicate the tag is now closed.
     *
     * @param bool $opened Whether the tag is currently open.
     * @param string $tag The tag name to close.
     * @param array<string> $result The result array to append the closing tag to.
     * @return bool Always false.
     */
    protected function closeTag(bool $opened, string $tag, array &$result): bool
    {
        if ($opened) {
            $result[] = "</{$tag}>";
        }
        return false;
    }

    /**
     *
     * @param bool $opened
     * @param string $tag
     * @param array<string> $result
     * @return bool
     */
    protected function openTag(bool $opened, string $tag, array &$result): bool
    {
        if ($opened === false) {
            $result[] = "<{$tag} class=\"mb-3\">";
        }
        return true;
    }

    /**
     * Renders markdown content to HTML.
     *
     * @param string $content
     * @return string
     */
    protected function renderMarkdown(string $content): string
    {
        // 1. Normalize line breaks: replace <br> tags with newlines
        $content = RichTextHelper::normalizeLineBreaks($content);

        // 2. Escape HTML to prevent XSS as the first security layer
        $html = htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // 3. Identify and process blocks
        $lines = explode(PHP_EOL, $html);
        $result = [];
        $ulOpened = false;
        $olOpened = false;
        $pOpened = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (empty($trimmed)) {
                $ulOpened = $this->closeTag($ulOpened, 'ul', $result);
                $olOpened = $this->closeTag($olOpened, 'ol', $result);
                $pOpened = $this->closeTag($pOpened, 'p', $result);
                continue;
            }

            // Headers (H1 - H6)
            if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmed, $matches)) {
                $ulOpened = $this->closeTag($ulOpened, 'ul', $result);
                $olOpened = $this->closeTag($olOpened, 'ol', $result);
                $pOpened = $this->closeTag($pOpened, 'p', $result);

                $level = strlen($matches[1]);
                $innerContent = $this->applyInlineStyles($matches[2]);
                $result[] = "<h{$level} class=\"mt-3 mb-2\">{$innerContent}</h{$level}>";
                continue;
            }

            // Unordered List item (*, -, +)
            if (preg_match('/^[\*\-\+]\s+(.+)$/', $trimmed, $matches)) {
                $ulOpened = $this->openTag($ulOpened, 'ul', $result);
                $olOpened = $this->closeTag($olOpened, 'ol', $result);
                $pOpened = $this->closeTag($pOpened, 'p', $result);

                $innerContent = $this->applyInlineStyles($matches[1]);
                $result[] = "<li>{$innerContent}</li>";
                continue;
            }

            // Ordered List item (1., 2., etc.)
            if (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $matches)) {
                $ulOpened = $this->closeTag($ulOpened, 'ul', $result);
                $olOpened = $this->openTag($olOpened, 'ol', $result);
                $pOpened = $this->closeTag($pOpened, 'p', $result);

                $innerContent = $this->applyInlineStyles($matches[1]);
                $result[] = "<li>{$innerContent}</li>";
                continue;
            }

            // Paragraph
            $ulOpened = $this->closeTag($ulOpened, 'ul', $result);
            $olOpened = $this->closeTag($olOpened, 'ol', $result);

            if (!$pOpened) {
                $result[] = '<p class="mb-3">' . $this->applyInlineStyles($trimmed);
                $pOpened = true;
            } else {
                $lastIdx = count($result) - 1;
                $result[$lastIdx] .= ' ' . $this->applyInlineStyles($trimmed);
            }
        }

        // Close any remaining tags
        $this->closeTag($ulOpened, 'ul', $result);
        $this->closeTag($olOpened, 'ol', $result);
        $this->closeTag($pOpened, 'p', $result);

        return implode(PHP_EOL, $result);
    }

    /**
     * Applies inline styles (Links, Bold, Italic) to a text string.
     *
     * @param string $text
     * @return string
     */
    protected function applyInlineStyles(string $text): string
    {
        $links = [];

        // 1. Extract Links [text](url) to avoid matching bold/italic inside URLs
        $text = preg_replace_callback('/\[(.*?)\]\((.*?)\)/', function ($matches) use (&$links) {
            $idx = count($links);
            $inner = $this->applyBoldItalic($matches[1]);
            $url = trim($matches[2]);

            // Second security layer: basic URL validation to prevent javascript: and other malicious schemes
            // Allow relative paths (starting with /, ./, ../, or just word characters), anchors, and common protocols
            $isValid = preg_match('/^(https?:\/\/|mailto:|tel:|\/\/|\/|\.\.?\/|#)/i', $url) ||
                    preg_match('/^[^:]+?(\/|$)/', $url);

            if ($isValid) {
                $escapedUrl = htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $links[] = '<a href="' . $escapedUrl . '" class="link-primary" target="_blank" rel="noopener">' . $inner . '</a>';
            } else {
                $links[] = '[' . $inner . '](' . $url . ')';
            }
            return "\0LINK{$idx}\0";
        }, $text);

        // 2. Apply Bold and Italic to the remaining text
        $text = $this->applyBoldItalic((string) $text);

        // 3. Restore links
        foreach ($links as $idx => $link) {
            $text = str_replace("\0LINK{$idx}\0", $link, $text);
        }

        return $text;
    }

    /**
     * Applies Bold and Italic styles.
     *
     * @param string $text
     * @return string
     */
    protected function applyBoldItalic(string $text): string
    {
        // 1. Bold Italic (***text*** or ___text___)
        $text = (string) preg_replace('/\*\*\*(?=\S)(.*?)(?<=\S)\*\*\*/', '<strong><em>$1</em></strong>', $text);
        $text = (string) preg_replace('/___(?=\S)(.*?)(?<=\S)___/', '<strong><em>$1</em></strong>', $text);

        // 2. Handle mixed cases like **_text_** or __*text*__ (must run before plain bold/italic)
        $text = (string) preg_replace('/\*\*_(?=\S)(.*?)(?<=\S)_\*\*/', '<strong><em>$1</em></strong>', $text);
        $text = (string) preg_replace('/__\*(?=\S)(.*?)(?<=\S)\*__/', '<strong><em>$1</em></strong>', $text);

        // 3. Bold (**text** or __text__)
        $text = (string) preg_replace('/\*\*(?=\S)(.*?)(?<=\S)\*\*/', '<strong>$1</strong>', $text);
        $text = (string) preg_replace('/__(?=\S)(.*?)(?<=\S)__/', '<strong>$1</strong>', $text);

        // 4. Italic (*text* or _text_)
        // Avoid matching markers inside words unless it's *
        $text = (string) preg_replace('/(?<!\w)\*([^\s\*](?:[^*]*[^\s\*])?)\*(?!\w)/', '<em>$1</em>', $text);
        $text = (string) preg_replace('/(?<!\w)_([^\s_](?:[^_]*[^\s_])?)_(?!\w)/', '<em>$1</em>', $text);

        return $text;
    }
}
