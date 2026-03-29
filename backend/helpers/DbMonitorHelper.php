<?php

namespace backend\helpers;

use Yii;

/**
 * Helper to render an EXPLAIN plan as an indented tree (Oracle-like).
 *
 */
final class DbMonitorHelper
{

    /**
     * @param array<string, mixed> $node
     * @param int $level
     * @return string
     */
    public static function renderNode(array $node, int $level = 0): string
    {
        $indent = str_repeat('  ', $level);

        /** @var string $label */
        $label = $node['operation'] ?? $node['table'] ?? 'Step';
        Yii::debug("*** debug *** renderNode level={$level}, label={$label} " . print_r($node, true));

        /** @var array<string> $extras */
        $extras = [];
        self::extractProperty($node, 'table', $extras, '[%s]');
        Yii::debug(print_r($extras, true));
        self::extractProperty($node, 'index', $extras);
        Yii::debug(print_r($extras, true));
        self::extractProperty($node, 'rows', $extras);
        Yii::debug(print_r($extras, true));
        self::extractProperty($node, 'cost', $extras);
        Yii::debug(print_r($extras, true));

        $info = implode(' ', $extras);
        $line = "{$indent}- {$label} {$info}\n";

        if (isset($node['children'])) {
            /** @var array<int, array<string,mixed>> $children */
            $children = is_array($node['children']) ? $node['children'] : [];
            foreach ($children as $child) {
                $line .= self::renderNode($child, $level + 1);
            }
        }

        return $line;
    }

    /**
     *
     * @param array<string, mixed> $node
     * @param string $propertyName
     * @param array<string> $extras
     * @param string $placeHolder
     * @return void
     */
    private static function extractProperty(array $node, string $propertyName, array &$extras, string $placeHolder = '(%s)'): void
    {
        if (isset($node[$propertyName])) {
            $property = is_string($node[$propertyName]) ? (string) $node[$propertyName] : 'Unknown';
            $extra = sprintf($placeHolder, $property);
            Yii::debug("*** debug *** extractProperty propertyName={$propertyName}" . print_r($node, true));
            $extras[] = $extra;
        }
    }

    /**
     * Convert uptime seconds to a string like "3d 14h 22m".
     */
    public static function formatUptime(int $seconds): string
    {
        $days = intdiv($seconds, 86400);
        $seconds %= 86400;

        $hours = intdiv($seconds, 3600);
        $seconds %= 3600;

        $minutes = intdiv($seconds, 60);

        return sprintf('%dd %dh %dm', $days, $hours, $minutes);
    }
}
