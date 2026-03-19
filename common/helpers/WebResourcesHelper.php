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
     * @return string
     */
    public static function storyRootPath(int $storyId): string
    {
        return "/common/web/resources/story-{$storyId}";
    }
}
