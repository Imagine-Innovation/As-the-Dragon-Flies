<?php

namespace backend\widgets;

use Yii;
use yii\base\Widget;

class ActivityGraph extends Widget
{
    /* ==========================
     *  Widget configuration
     * ========================== */

    public int $windowMinutes = 60;
    public int $stepMinutes = 5;
    public int $graduations = 5;

    /** @var array<string> */
    public array $applications = [
        'app-backend',
        'app-frontend',
    ];

    /* ==========================
     *  Widget lifecycle
     * ========================== */

    /**
     *
     * @return string
     */
    public function run(): string
    {
        /** @var array<string, array<string, int>> */
        $data = $this->extractData();
        $layout = $this->computeLayout($data);

        return $this->buildSvg($data, $layout);
    }

    /* ==========================
     *  Data extraction
     * ========================== */

    /**
     *
     * @return array<string, array<string, int>>
     */
    private function extractData(): array
    {
        // to ajust to "round" number of minutes
        $step = $this->stepMinutes * 60;
        $now = floor(time() / $step) * $step;
        $from = $now - ($this->windowMinutes * 60);
        $stepSeconds = $this->stepMinutes * 60;

        $rows = Yii::$app->db->createCommand("
            SELECT
                FROM_UNIXTIME(FLOOR(action_at / :step) * :step, '%H:%i') AS slot,
                application,
                COUNT(*) AS total
            FROM user_log
            WHERE action_at >= :from
              AND application IN (" . $this->quoteArray($this->applications) . ")
            GROUP BY slot, application
            ORDER BY slot ASC
        ", [
                    ':from' => $from,
                    ':step' => $stepSeconds,
                ])->queryAll();

        return $this->normalizeTimeSeries($rows, $now);
    }

    /**
     *
     * @param array{slot: string, application: string, total: int} $rows
     * @param int $now
     * @return array<string, array<string, int>>
     */
    private function normalizeTimeSeries(array $rows, int $now): array
    {
        /** @var array<string, array<string, int>> */
        $data = [];

        for ($i = $this->windowMinutes; $i >= 0; $i -= $this->stepMinutes) {
            Yii::debug("normalizeTimeSeries now={$now}, i={$i}");
            $label = date('H:i', $now - ($i * 60));
            foreach ($this->applications as $application) {
                $data[$label][$application] = 0;
            }
        }

        /** @var array{slot: string, application: string, total: int} $row */
        foreach ($rows as $row) {
            $data[$row['slot']][$row['application']] = (int) $row['total'];
        }

        Yii::debug($data);
        return $data;
    }

    /* ==========================
     *  Layout computation
     * ========================== */

    /**
     *
     * @param array<string, array<string, int>> $data
     * @return int
     */
    private function getMaxValue(array $data): int
    {
        $max = 0;

        foreach ($data as $stat) {
            foreach ($stat as $application => $count) {
                $max = max($max, $count);
            }
        }

        return $max;
    }

    /**
     *
     * @param array<string, array<string, int>> $data
     * @return array{width: int, height: int, padding: int, labels: array<string>, series: array<string>, yMax: int, xStep: int, yScale: int}
     */
    private function computeLayout(array $data): array
    {
        $width = 1000;
        $height = 300;
        $padding = 50;

        //$maxValue = max(array_map('max', $data));
        $maxValue = $this->getMaxValue($data);
        $yMax = $this->computeYAxisMax($maxValue);

        $layout = [
            'width' => $width,
            'height' => $height,
            'padding' => $padding,
            'labels' => array_keys($data),
            'series' => $this->applications,
            'yMax' => $yMax,
            'xStep' => (int) ceil(($width - 2 * $padding) / (count($data) - 1)),
            'yScale' => (int) ceil(($height - 2 * $padding) / $yMax),
        ];

        Yii::debug($layout);
        return $layout;
    }

    /**
     *
     * @param int $maxValue
     * @return int
     */
    private function computeYAxisMax(int $maxValue): int
    {
        if ($maxValue === 0) {
            return $this->graduations;
        }

        $step = (int) ceil($maxValue / $this->graduations);
        return $step * $this->graduations;
    }

    /* ==========================
     *  SVG rendering
     * ========================== */

    /**
     *
     * @param array<string, array<string, int>> $data
     * @param array{width: int, height: int, padding: int, labels: array<string>, series: array<string>, yMax: int, xStep: int, yScale: int} $layout
     * @return string
     */
    private function buildSvg(array $data, array $layout): string
    {
        $svg = [];
        $svg[] = $this->svgOpen($layout);
        $svg[] = $this->drawYAxis($layout);
        $svg[] = $this->drawXAxis($layout);

        foreach ($layout['series'] as $application) {
            $points = $this->computePoints($data, $layout, $application);
            $svg[] = $this->drawBezierLine($points, $this->colorForApp($application));
            $svg[] = $this->drawPoints($points, $application);
        }

        $svg[] = '</svg>';

        return implode("\n", $svg);
    }

    /**
     *
     * @param array{width: int, height: int, padding: int, labels: array<string>, series: array<string>, yMax: int, xStep: int, yScale: int} $layout
     * @return string
     */
    private function svgOpen(array $layout): string
    {
        return "<svg viewBox='0 0 {$layout['width']} {$layout['height']}' width='100%' height='100%' xmlns='http://www.w3.org/2000/svg'>";
    }

    /**
     *
     * @param array{width: int, height: int, padding: int, labels: array<string>, series: array<string>, yMax: int, xStep: int, yScale: int} $layout
     * @return string
     */
    private function drawYAxis(array $layout): string
    {
        $out = [];
        $step = $layout['yMax'] / $this->graduations;

        for ($i = 0; $i <= $this->graduations; $i++) {
            $y = $layout['height'] - $layout['padding'] - ($i * $step * $layout['yScale']);
            $value = $i * $step;

            $x2 = $layout['width'] - $layout['padding'];
            $out[] = "<line x1='{$layout['padding']}' y1='{$y}' x2='{$x2}' y2='{$y}' stroke='#ddd' />";

            $y += 4;
            $out[] = "<text x='10' y='{$y}' font-size='12'>{$value}</text>";
        }

        return implode(PHP_EOL, $out);
    }

    /**
     *
     * @param array{width: int, height: int, padding: int, labels: array<string>, series: array<string>, yMax: int, xStep: int, yScale: int} $layout
     * @return string
     */
    private function drawXAxis(array $layout): string
    {
        $out = [];

        foreach ($layout['labels'] as $i => $label) {
            $x = $layout['padding'] + $i * $layout['xStep'];
            $y = $layout['height'] - 10;
            $out[] = "<text x='{$x}' y='{$y}' font-size='12' text-anchor='middle'>{$label}</text>";
        }

        return implode(PHP_EOL, $out);
    }

    /**
     *
     * @param array<string, array<string, int>> $data
     * @param array{width: int, height: int, padding: int, labels: array<string>, series: array<string>, yMax: int, xStep: int, yScale: int} $layout
     * @param string $application
     * @return list<array{x: int, y: int, value: int}>
     */
    private function computePoints(array $data, array $layout, string $application): array
    {
        /** @var list<array{x: int, y: int, value: int}> */
        $points = [];

        Yii::debug(['data' => $data, 'layout' => $layout]);
        foreach ($layout['labels'] as $i => $label) {
            $points[] = [
                'x' => (int) $layout['padding'] + ($i * $layout['xStep']),
                'y' => (int) $layout['height'] - $layout['padding'] - ($data[$label][$application] * $layout['yScale']),
                'value' => $data[$label][$application],
            ];
        }

        return $points;
    }

    /**
     *
     * @param list<array{x: int, y: int, value: int}> $points
     * @param string $color
     * @return string
     */
    private function drawBezierLine(array $points, string $color): string
    {
        $d = "M {$points[0]['x']} {$points[0]['y']}";

        for ($i = 1; $i < count($points); $i++) {
            $cx = ($points[$i - 1]['x'] + $points[$i]['x']) / 2;
            $d .= " C $cx {$points[$i - 1]['y']},
                       $cx {$points[$i]['y']},
                       {$points[$i]['x']} {$points[$i]['y']}";
        }

        return "<path d='{$d}' fill='none' stroke='{$color}' stroke-width='2' />";
    }

    /**
     *
     * @param list<array{x: int, y: int, value: int}> $points
     * @param string $application
     * @return string
     */
    private function drawPoints(array $points, string $application): string
    {
        $out = [];
        $color = $this->colorForApp($application);

        foreach ($points as $p) {
            $out[] = "<circle cx='{$p['x']}' cy='{$p['y']}' r='4' fill='{$color}'>"
                    . "<title>{$application} : {$p['value']}</title>"
                    . "</circle>";
        }

        return implode(PHP_EOL, $out);
    }

    /* ==========================
     *  Helpers
     * ========================== */

    /**
     *
     * @param string $application
     * @return string
     */
    private function colorForApp(string $application): string
    {
        return match ($application) {
            'app-backend' => 'var(--bs-primary)',
            'app-frontend' => 'var(--bs-success)',
            default => 'var(--bs-secondary)',
        };
    }

    /**
     *
     * @param array<string> $values
     * @return string
     */
    private function quoteArray(array $values): string
    {
        return implode(',', array_map(
                        fn($v) => Yii::$app->db->quoteValue($v),
                        $values
                ));
    }
}
