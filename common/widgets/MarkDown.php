<?php

namespace common\widgets;

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
     *
     * @param bool $opened
     * @param string $tag
     * @param array<string> $result
     * @return bool
     */
    protected function closeTag(bool $opened, string $tag, array &$result): bool
    {
        if ($opened) {
            $result[] = "</{$tag}>";
            return false;
        }
        return true;
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
        // 1. Escape HTML to prevent XSS as the first security layer
        $html = htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        // 2. Identify and process blocks
        $lines = explode("\n", $html);
        $result = [];
        $ulOpen = false;
        $olOpen = false;
        $pOpen = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if (empty($trimmed)) {
                $ulOpen = $this->closeTag($ulOpen, 'ul', $result);
                $olOpen = $this->closeTag($olOpen, 'ol', $result);
                $pOpen = $this->closeTag($pOpen, 'p', $result);
                continue;
            }

            // Headers (H1 - H6)
            if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmed, $matches)) {
                $ulOpen = $this->closeTag($ulOpen, 'ul', $result);
                $olOpen = $this->closeTag($olOpen, 'ol', $result);
                $pOpen = $this->closeTag($pOpen, 'p', $result);

                $level = strlen($matches[1]);
                $innerContent = $this->applyInlineStyles($matches[2]);
                $result[] = "<h{$level} class=\"mt-3 mb-2\">{$innerContent}</h{$level}>";
                continue;
            }

            // Unordered List item (*, -, +)
            if (preg_match('/^[\*\-\+]\s+(.+)$/', $trimmed, $matches)) {
                $ulOpen = $this->openTag($ulOpen, 'ul', $result);
                $olOpen = $this->closeTag($olOpen, 'ol', $result);
                $pOpen = $this->closeTag($pOpen, 'p', $result);

                $innerContent = $this->applyInlineStyles($matches[1]);
                $result[] = "<li>{$innerContent}</li>";
                continue;
            }

            // Ordered List item (1., 2., etc.)
            if (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $matches)) {
                $ulOpen = $this->closeTag($ulOpen, 'ul', $result);
                $olOpen = $this->openTag($olOpen, 'ol', $result);
                $pOpen = $this->closeTag($pOpen, 'p', $result);

                $innerContent = $this->applyInlineStyles($matches[1]);
                $result[] = "<li>{$innerContent}</li>";
                continue;
            }

            // Paragraph
            $ulOpen = $this->closeTag($ulOpen, 'ul', $result);
            $olOpen = $this->closeTag($olOpen, 'ol', $result);

            if (!$pOpen) {
                $result[] = '<p class="mb-3">' . $this->applyInlineStyles($trimmed);
                $pOpen = true;
            } else {
                $lastIdx = count($result) - 1;
                $result[$lastIdx] .= ' ' . $this->applyInlineStyles($trimmed);
            }
        }

        // Close any remaining tags
        $this->closeTag($ulOpen, 'ul', $result);
        $this->closeTag($olOpen, 'ol', $result);
        $this->closeTag($pOpen, 'p', $result);

        return implode("\n", $result);
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
