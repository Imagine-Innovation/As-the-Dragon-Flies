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
                if ($ulOpen) {
                    $result[] = '</ul>';
                    $ulOpen = false;
                }
                if ($olOpen) {
                    $result[] = '</ol>';
                    $olOpen = false;
                }
                if ($pOpen) {
                    $result[] = '</p>';
                    $pOpen = false;
                }
                continue;
            }

            // Headers (H1 - H6)
            if (preg_match('/^(#{1,6})\s+(.+)$/', $trimmed, $matches)) {
                if ($ulOpen) {
                    $result[] = '</ul>';
                    $ulOpen = false;
                }
                if ($olOpen) {
                    $result[] = '</ol>';
                    $olOpen = false;
                }
                if ($pOpen) {
                    $result[] = '</p>';
                    $pOpen = false;
                }

                $level = strlen($matches[1]);
                $innerContent = $this->applyInlineStyles($matches[2]);
                $result[] = "<h{$level} class=\"mt-3 mb-2\">{$innerContent}</h{$level}>";
                continue;
            }

            // Unordered List item (*, -, +)
            if (preg_match('/^[\*\-\+]\s+(.+)$/', $trimmed, $matches)) {
                if ($pOpen) {
                    $result[] = '</p>';
                    $pOpen = false;
                }
                if ($olOpen) {
                    $result[] = '</ol>';
                    $olOpen = false;
                }
                if (!$ulOpen) {
                    $result[] = '<ul class="mb-3">';
                    $ulOpen = true;
                }
                $innerContent = $this->applyInlineStyles($matches[1]);
                $result[] = "<li>{$innerContent}</li>";
                continue;
            }

            // Ordered List item (1., 2., etc.)
            if (preg_match('/^\d+\.\s+(.+)$/', $trimmed, $matches)) {
                if ($pOpen) {
                    $result[] = '</p>';
                    $pOpen = false;
                }
                if ($ulOpen) {
                    $result[] = '</ul>';
                    $ulOpen = false;
                }
                if (!$olOpen) {
                    $result[] = '<ol class="mb-3">';
                    $olOpen = true;
                }
                $innerContent = $this->applyInlineStyles($matches[1]);
                $result[] = "<li>{$innerContent}</li>";
                continue;
            }

            // Paragraph
            if ($ulOpen) {
                $result[] = '</ul>';
                $ulOpen = false;
            }
            if ($olOpen) {
                $result[] = '</ol>';
                $olOpen = false;
            }

            if (!$pOpen) {
                $result[] = '<p class="mb-3">' . $this->applyInlineStyles($trimmed);
                $pOpen = true;
            } else {
                $lastIdx = count($result) - 1;
                $result[$lastIdx] .= ' ' . $this->applyInlineStyles($trimmed);
            }
        }

        // Close any remaining tags
        if ($ulOpen) {
            $result[] = '</ul>';
        }
        if ($olOpen) {
            $result[] = '</ol>';
        }
        if ($pOpen) {
            $result[] = '</p>';
        }

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
            // Allow relative paths, anchors, and common protocols
            if (preg_match('/^(https?:\/\/|mailto:|tel:|\/|#)/i', $url)) {
                $links[] = '<a href="' . $url . '" class="link-primary" target="_blank" rel="noopener">' . $inner . '</a>';
            } else {
                $links[] = '[' . $inner . '](' . $url . ')';
            }
            return "\0LINK{$idx}\0";
        }, $text);

        // 2. Apply Bold and Italic to the remaining text
        $text = $this->applyBoldItalic($text);

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
        // Bold (**text** or __text__)
        $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
        $text = preg_replace('/__(.*?)__/', '<strong>$1</strong>', $text);

        // Italic (*text* or _text_)
        $text = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $text);
        $text = preg_replace('/_(.*?)_/', '<em>$1</em>', $text);

        return $text;
    }
}
