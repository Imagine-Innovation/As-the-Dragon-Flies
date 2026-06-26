<?php

namespace common\helpers;

class WebResourcesHelper
{

    /**
     *
     * @return string
     */
    public static function imagePath(): string
    {
        return '/common/web/img';
    }

    /**
     *
     * @return string
     */
    public static function resourcePath(): string
    {
        return '/common/web/resources';
    }

    /**
     *
     * @param int|null $storyId
     * @return string
     */
    public static function storyRootPath(?int $storyId = null): string
    {
        if ($storyId) {
            return "/common/web/resources/story-{$storyId}";
        }
        return '/common/web/img';
    }
}
