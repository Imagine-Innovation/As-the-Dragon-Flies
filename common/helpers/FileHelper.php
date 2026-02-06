<?php

namespace common\helpers;

class FileHelper
{
    /**
     * Removes the last extension from a filename.
     *
     * @param string $filename The input filename.
     * @return string The filename without the last extension.
     */
    public static function removeExtension($filename)
    {
        $lastDotPosition = strrpos($filename, '.');

        if ($lastDotPosition === false) {
            // No extension found, return the original filename
            return $filename;
        }

        // Return the substring up to (but not including) the last dot
        return substr($filename, 0, $lastDotPosition);
    }
}
