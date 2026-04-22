<?php

namespace common\helpers;

use Yii;
use yii\helpers\HtmlPurifier;

class RichTextHelper
{

    /**
     * Sanitizes and caches the result to prevent CPU spikes.
     *
     * @param string|null $content The raw HTML
     * @param int $duration How long to cache in seconds (0 = forever)
     * @return string
     */
    public static function sanitizeWithCache(?string $content, int $duration = 3600): string
    {
        if (empty($content)) {
            return '';
        }

        // Generate a unique key based on the content itself
        $cacheKey = 'purified_html_' . md5($content);

        return Yii::$app->cache->getOrSet(
                        $cacheKey,
                        function () use ($content) {
                            return self::sanitize($content);
                        },
                        $duration,
                );
    }

    /**
     * Sanitizes rich text content.
     * Allows: Basic formatting, Links, Lists, Images, and Tables.
     * Denies: Scripts, Forms, Iframes, and Inline JS.
     *
     * @param string|null $content The raw HTML string from the editor.
     * @return string
     */
    public static function sanitize(?string $content): string
    {
        if (empty($content)) {
            return '';
        }

        $html = HtmlPurifier::process($content, [
            'HTML.Allowed' => 'div[class],p[class],span[class|style],h1,h2,h3,h4,h5,h6,hr,br,b,strong,i,em,u,a[href|title|target],ul,ol,li,img[src|alt|width|height],table,thead,tbody,tr,th,td',
            // Allows any class name string
            'Attr.AllowedClasses' => null,
            'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align,width,height,border,border-collapse,border-spacing',
            'AutoFormat.RemoveEmpty' => true,
            'HTML.Nofollow' => true,
            // IMPORTANT: Change this ID whenever you modify HTML.Allowed
            // to force HTMLPurifier to refresh its internal cache.
            'HTML.DefinitionID' => 'rich-text-v2',
            'HTML.DefinitionRev' => 1,
        ]);

        return $html;
    }

    /**
     * Sanitizes Markdown and caches the result.
     *
     * @param string|null $markdown
     * @param int $duration How long to cache in seconds (0 = forever)
     * @return string
     */
    public static function sanitizeMarkdownWithCache(?string $markdown, int $duration = 3600): string
    {
        if (empty($markdown)) {
            return '';
        }

        $cacheKey = 'purified_md_' . md5($markdown);

        return Yii::$app->cache->getOrSet(
                        $cacheKey,
                        function () use ($markdown) {
                            return self::sanitizeMarkdown($markdown);
                        },
                        $duration,
                );
    }

    /**
     * Normalizes line breaks by replacing <br> tags with PHP_EOL.
     *
     * @param string $text
     * @return string
     */
    public static function normalizeLineBreaks(string $text): string
    {
        return (string) preg_replace('/<br\s*\/?>/i', PHP_EOL, $text);
    }

    /**
     * Sanitizes Markdown by stripping HTML tags and normalizing newlines.
     *
     * @param string|null $markdown
     * @return string
     */
    public static function sanitizeMarkdown(?string $markdown): string
    {
        if ($markdown === null || $markdown === '') {
            return '';
        }

        // 1. Replace <br> with newlines before stripping tags
        $markdown = self::normalizeLineBreaks($markdown);

        // 2. Strip any HTML tags.
        $markdown = strip_tags($markdown);

        // 2. Basic cleanup: normalize newlines and trim
        $markdown = str_replace(["\r\n", "\r"], "\n", $markdown);

        // 3. Ensure we don't have too many consecutive newlines
        $markdown = (string) preg_replace("/\n{3,}/", "\n\n", $markdown);

        return trim($markdown);
    }
}
