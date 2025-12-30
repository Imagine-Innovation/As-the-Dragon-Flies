<?php

namespace common\helpers;

class ModelHelper
{

    const SCANNING_PATHS = [
        "common\\models",
        "common\\components",
        "frontend\\components",
        "backend\\components",
    ];

    /**
     * Checks if a class name corresponds to an existing class.
     *
     * This function constructs model and component class names based on the
     * provided class name and checks if either of them exists. It returns true
     * if either the model or component class exists, otherwise returns false.
     *
     * @param string $className The class name to check.
     * @return bool True if the class name corresponds to an existing class, false otherwise.
     */
    public static function exists(string $className): bool {
        $match = false;

        foreach (self::SCANNING_PATHS as $path) {
            // Check if either model or component class exists
            if (class_exists($path . '\\' . $className)) {
                return true;
            }
        }
        return $match;
    }

    /**
     *
     * @param string $className
     * @return string|null
     */
    public static function path(string $className): ?string {
        foreach (self::SCANNING_PATHS as $path) {
            // Check if either model or component class exists
            if (class_exists($path . '\\' . $className)) {
                return $path;
            }
        }
        return null;
    }
}
