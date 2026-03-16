<?php

namespace backend\helpers;

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

        /** @var array<string> $extras */
        $extras = [];
        self::extractProperty($node, 'table', $extras, '[%s]');
        self::extractProperty($node, 'index', $extras);
        self::extractProperty($node, 'rows', $extras);
        self::extractProperty($node, 'cost', $extras);

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
            $extras[] = sprintf($placeHolder, $property);
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
