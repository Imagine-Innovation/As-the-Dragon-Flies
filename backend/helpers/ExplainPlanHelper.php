<?php

namespace backend\helpers;

class ExplainPlanHelper
{

    /**
     * @param array<string, mixed> $node
     * @param string $label
     * @return array<string, mixed>
     */
    public static function buildTree(array $node, string $label = 'query_block'): array
    {
        $details = [];
        $children = [];

        foreach ($node as $key => $value) {
            if (is_array($value)) {
                /** @var array<string, mixed> $value */
                $children[] = self::buildTree($value, (string) $key);
            } else {
                /** @var scalar|null $value */
                $details[(string) $key] = $value;
            }
        }

        [$cost, $costLabel] = self::computeCost($details);

        $totalCost = $cost;
        foreach ($children as $child) {
            $childCost = self::intVal($child['totalCost']);
            $totalCost += $childCost;
        }

        return [
            'label' => $label,
            'details' => $details,
            'children' => $children,
            'cost' => $cost,
            'costLabel' => $costLabel,
            'totalCost' => $totalCost,
        ];
    }

    /**
     * @param array<string, mixed> $tree
     */
    public static function renderTree(array $tree): string
    {
        return self::renderSummary($tree)
                . '<div class="explain-plan-container">' . self::renderNode($tree) . '</div>';
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @return string
     */
    private static function getDetailClass(string $key, mixed $value): string
    {
        if ($key === 'access_type' && (self::strVal($value) === 'ALL')) {
            return 'text-warning fw-bold';
        }

        if ($key === 'rows' && (self::intVal($value) > 1000)) {
            return 'text-danger fw-bold';
        }
        return 'text-muted';
    }

    /**
     * @param array<string, mixed> $node
     */
    private static function renderNodeDetail(array $node): string
    {
        $html = '<ul class="list-unstyled small mb-2">';
        /** @var array<string, string> $details */
        $details = $node['details'];
        foreach ($details as $key => $value) {
            $html .= '<li class="' . self::getDetailClass($key, $value) . '">';
            $html .= '<span class="fw-semibold">' . self::encode($key) . ':</span> ' . self::encode($value);
            $html .= '</li>';
        }
        return $html . '</ul>';
    }

    /**
     * @param array<string, mixed> $node
     */
    private static function renderNodeChildren(array $node): string
    {
        $html = '<div class="ps-3 border-start">';
        /** @var array<string, mixed> $children */
        $children = $node['children'];
        foreach ($children as $child) {
            /** @var array<string, mixed> $child */
            $html .= self::renderNode($child);
        }
        return $html . '</div>';
    }

    /**
     * @param array<string, mixed> $node
     */
    private static function renderNodeBody(array $node): string
    {
        $html = '<div class="card-body py-2">';

        if ($node['details'] !== []) {
            $html .= self::renderNodeDetail($node);
        }

        if ($node['children'] !== []) {
            $html .= self::renderNodeChildren($node);
        }

        return $html . '</div>';
    }

    /**
     * @param array<string, mixed> $node
     */
    private static function renderNodeHeader(array $node): string
    {
        $html = '<div class="card-header py-2 fw-bold d-flex justify-content-between align-items-center">';
        $label = self::strVal($node['label']);
        $html .= '<span class="text-primary">' . self::encode($label) . '</span>';
        $cost = self::intVal($node['cost']);
        $costLabel = self::strVal($node['costLabel']);
        $html .= self::renderCostBadge($cost, $costLabel);
        return $html . '</div>';
    }

    /**
     * @param array<string, mixed> $node
     */
    private static function renderNode(array $node): string
    {
        $html = '<div class="card shadow-sm mb-3 me-3 d-inline-block align-top" style="min-width:240px;">';

        $html .= self::renderNodeHeader($node);
        $html .= self::renderNodeBody($node);

        return $html . '</div>';
    }

    /**
     *
     * @param array<string, list<string>> $analysis
     * @return string
     */
    private static function renderAnalysisIssues(array $analysis): string
    {
        $html = '<div class="alert alert-danger">';
        $html .= '<strong>Issues detected:</strong>';
        $html .= '<ul class="mb-0">';
        foreach ($analysis['issues'] as $issue) {
            $html .= '<li>' . self::encode($issue) . '</li>';
        }
        $html .= '</ul>';
        return $html . '</div>';
    }

    /**
     *
     * @param array<string, list<string>> $analysis
     * @return string
     */
    private static function renderAnalysisSuggestions(array $analysis): string
    {
        $html = '<div class="alert alert-success">';
        $html .= '<strong>Suggestions:</strong>';
        $html .= '<ul class="mb-0">';
        foreach ($analysis['suggestions'] as $suggestion) {
            $html .= '<li>' . self::encode($suggestion) . '</li>';
        }
        $html .= '</ul>';
        return $html . '</div>';
    }

    /**
     * @param array<string, mixed> $tree
     */
    private static function renderSummary(array $tree): string
    {
        $analysis = self::analyze($tree);

        $html = '<div class="mb-3">';

        // Total cost
        $html .= '<div class="alert alert-secondary">';
        $totalCost = self::intVal($tree['totalCost']);
        $html .= '<strong>Total Cost:</strong> ' . $totalCost;
        $html .= '</div>';

// Issues
        if ($analysis['issues'] !== []) {
            $html .= self::renderAnalysisIssues($analysis);
        }

// Suggestions
        if ($analysis['suggestions'] !== []) {
            $html .= self::renderAnalysisSuggestions($analysis);
        }

        return $html . '</div>';
    }

    /**
     * @param array<string, mixed> $node
     * @return array<string, list<string>>
     */
    public static function analyze(array $node): array
    {
        $issues = [];
        $suggestions = [];

        self::walkAnalyze($node, $issues, $suggestions);

        return [
            'issues' => array_values(array_unique($issues)),
            'suggestions' => array_values(array_unique($suggestions)),
        ];
    }

    /**
     * @param array<string, scalar|null> $details
     * @param list<string> $issues
     * @param list<string> $suggestions
     */
    private static function checkForFullScan(array $details, array &$issues, array &$suggestions): void
    {
        if (($details['access_type'] ?? null) === 'ALL') {
            $issues[] = 'Full table scan detected';

            if (isset($details['table_name'], $details['attached_condition'])) {
                $suggestions[] = "Consider adding index on {$details['table_name']} for condition: {$details['attached_condition']}";
            }
        }
    }

    /**
     * @param array<string, scalar|null> $details
     * @param list<string> $issues
     * @param list<string> $suggestions
     */
    private static function checkForLargeRowScan(array $details, array &$issues, array &$suggestions): void
    {
        $threshold = 5000;
        if (isset($details['rows'])) {
            $rows = self::intVal($details['rows']);
            if ($rows > $threshold) {
                $issues[] = "Large row scan ({$details['rows']} rows)";
                $suggestions[] = 'Reduce scanned rows using better WHERE conditions or indexes';
            }
        }
    }

    /**
     * @param array<string, scalar|null> $details
     * @param list<string> $issues
     * @param list<string> $suggestions
     */
    private static function checkForFileSort(array $details, array &$issues, array &$suggestions): void
    {
        if (isset($details['sort_key'])) {
            $issues[] = 'Filesort detected';
            $suggestions[] = "Add index to support ORDER BY: {$details['sort_key']}";
        }
    }

    /**
     * @param array<string, scalar|null> $details
     * @param list<string> $issues
     * @param list<string> $suggestions
     */
    private static function checkForLowFiltering(array $details, array &$issues, array &$suggestions): void
    {
        $threshold = 50;
        if (isset($details['filtered'])) {
            $filtered = self::intVal($details['filtered']);
            if ($filtered < $threshold) {
                $issues[] = "Low filtering efficiency ({$details['filtered']}%)";
                $suggestions[] = 'Improve selectivity with better indexes';
            }
        }
    }

    /**
     * @param array<string, mixed> $node
     * @param list<string> $issues
     * @param list<string> $suggestions
     */
    private static function walkAnalyze(array $node, array &$issues, array &$suggestions): void
    {
        /** @var array<string, scalar|null> $details */
        $details = $node['details'];

        self::checkForFullScan($details, $issues, $suggestions);
        self::checkForLargeRowScan($details, $issues, $suggestions);
        self::checkForFileSort($details, $issues, $suggestions);
        self::checkForLowFiltering($details, $issues, $suggestions);

        /** @var array<string, mixed> $children */
        $children = $node['children'];
        foreach ($children as $child) {
            /** @var array<string, mixed> $child */
            self::walkAnalyze($child, $issues, $suggestions);
        }
    }

    /**
     * @param array<string, scalar|null> $details
     * @return array{0:int,1:string}
     */
    private static function computeCost(array $details): array
    {
        $cost = 0;

        if (isset($details['rows'])) {
            $rows = self::intVal($details['rows']);
            $cost += min(50, (int) (log10(max(1, $rows)) * 10));
        }

        if (($details['access_type'] ?? null) === 'ALL') {
            $cost += 40;
        }

        if (isset($details['sort_key'])) {
            $cost += 20;
        }

        if (isset($details['filtered'])) {
            $filtered = self::intVal($details['filtered']);
            if ($filtered < 50) {
                $cost += 15;
            }
        }

        $label = $cost >= 70 ? 'HIGH' : ($cost >= 40 ? 'MEDIUM' : 'LOW');

        return [$cost, $label];
    }

    private static function renderCostBadge(int $cost, string $label): string
    {
        $class = match ($label) {
            'HIGH' => 'bg-danger',
            'MEDIUM' => 'bg-warning text-dark',
            default => 'bg-success',
        };

        return '<span class="badge ' . $class . '">' . $label . ' (' . $cost . ')</span>';
    }

    /**
     * @param scalar|null $value
     */
    private static function encode($value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     *
     * @param mixed $value
     * @return int
     */
    private static function intVal(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     *
     * @param mixed $value
     * @return string
     */
    private static function strVal(mixed $value): string
    {
        return is_string($value) ? (string) $value : '';
    }
}
