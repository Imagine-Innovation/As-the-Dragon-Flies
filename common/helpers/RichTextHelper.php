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
            'HTML.Allowed' => 'div[class],p[class],span[class|style],h1,h2,h3,h4,h5,h6,br,b,strong,i,em,u,a[href|title|target],ul,ol,li,img[src|alt|width|height],table,thead,tbody,tr,th,td',
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

        return nl2br($html);
    }
}
