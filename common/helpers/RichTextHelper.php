<?php

namespace common\helpers;

use Yii;
use yii\helpers\HtmlPurifier;

class RichTextHelper
{

    /**
     * Sanitizes and caches the result to prevent CPU spikes.
     *
     * @param string $content The raw HTML
     * @param int $duration How long to cache in seconds (0 = forever)
     * @return string
     */
    public static function sanitizeWithCache(string $content, int $duration = 3600): string
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
                        $duration
                );
    }

    /**
     * Sanitizes rich text content.
     * Allows: Basic formatting, Links, Lists, Images, and Tables.
     * Denies: Scripts, Forms, Iframes, and Inline JS.
     *
     * @param string $content The raw HTML string from the editor.
     * @return string
     */
    public static function sanitize(string $content): string
    {
        if (empty($content)) {
            return '';
        }

        return HtmlPurifier::process($content, [
                    // Removed 'iframe' and associated attributes
                    'HTML.Allowed' => 'p,b,strong,i,em,u,a[href|title|target],ul,ol,li,br,span[style],img[src|alt|width|height],table,thead,tbody,tr,th,td',
                    // Allow basic styling for tables and text alignment
                    'CSS.AllowedProperties' => 'font,font-size,font-weight,font-style,font-family,text-decoration,padding-left,color,background-color,text-align,width,height,border,border-collapse,border-spacing',
                    'AutoFormat.RemoveEmpty' => true,
                    'HTML.Nofollow' => true, // Good practice for SEO/Security on user-generated links
        ]);
    }
}
