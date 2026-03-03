<?php

/**
 * ActivityGraph – SVG line chart widget for the backend dashboard.
 *
 * Displays the number of log entries per application over a rolling time
 * window (default: last 60 minutes, one point every 5 minutes).
 *
 * Usage in a view:
 *   <?= ActivityGraph::widget() ?>
 *
 * Custom parameters:
 *   <?= ActivityGraph::widget([
 *       'windowMinutes' => 120,
 *       'stepMinutes'   => 10,
 *       'graduations'   => 5,
 *   ]) ?>
 */

namespace backend\widgets;

use Yii;
use yii\base\Widget;

class ActivityGraph extends Widget
{
    /* =========================================================
     *  Public configuration
     * ========================================================= */

    /** Rolling window size in minutes */
    public int $windowMinutes = 60;

    /** Bucket size in minutes */
    public int $stepMinutes = 5;

    /** Number of Y-axis graduations (horizontal grid lines) */
    public int $graduations = 5;

    /** Applications to track – order determines colour assignment */
    public array $applications = [
        'app-backend',
        'app-frontend',
    ];

    /* =========================================================
     *  Internal SVG canvas constants
     * =========================================================
     *
     *  All coordinates are expressed inside a fixed viewBox so the
     *  SVG can scale freely to any container width without any JS.
     *
     *  Canvas layout (px inside viewBox):
     *
     *   ┌──────────────────────────────────────────────────────┐
     *   │  PAD_TOP                                             │
     *   │  ┌─────────────────────────────────────────────────┐ │
     *   │  │                  plot area                      │ │
     *   │  │                                                 │ │
     *   │  └─────────────────────────────────────────────────┘ │
     *   │  PAD_BOTTOM (x labels + legend)                      │
     *   └──────────────────────────────────────────────────────┘
     *     PAD_LEFT (y labels)                       PAD_RIGHT
     */

    private const VB_WIDTH    = 800;   // viewBox width
    private const VB_HEIGHT   = 400;   // viewBox height
    private const PAD_LEFT    = 50;    // room for Y-axis labels
    private const PAD_RIGHT   = 20;    // breathing room on the right
    private const PAD_TOP     = 20;    // breathing room on top
    private const PAD_BOTTOM  = 60;    // room for X-axis labels + legend

    /**
     * Bootstrap CSS variable colours, assigned in order to series.
     * Fallback to --bs-secondary for any overflow.
     */
    private const BS_COLORS = [
        'var(--bs-primary)',
        'var(--bs-success)',
        'var(--bs-danger)',
        'var(--bs-warning)',
        'var(--bs-info)',
        'var(--bs-secondary)',
    ];

    /* =========================================================
     *  Widget lifecycle
     * ========================================================= */

    public function run(): string
    {
        $data   = $this->extractData();
        $layout = $this->computeLayout($data);

        return $this->buildSvg($data, $layout);
    }

    /* =========================================================
     *  Data extraction
     * ========================================================= */

    /**
     * Query user_log and return a two-dimensional associative array:
     *
     *   [ 'HH:MM' => [ 'app-backend' => <int>, 'app-frontend' => <int> ], … ]
     *
     * The time labels are built from the user/app timezone so that the
     * displayed times match what the operator sees on their clock.
     *
     * @return array<string, array<string, int>>
     */
    private function extractData(): array
    {
        $tz          = $this->resolveTimezone();
        $stepSeconds = $this->stepMinutes * 60;

        // Align "now" to the nearest lower slot boundary (UTC timestamp)
        $nowUtc  = (int) floor(time() / $stepSeconds) * $stepSeconds;
        $fromUtc = $nowUtc - ($this->windowMinutes * 60);

        // Build the complete ordered slot skeleton using local time labels
        $skeleton = $this->buildSkeleton($nowUtc, $tz);

        // Fetch aggregated counts from DB
        // We pass the UTC timestamps directly; MySQL FROM_UNIXTIME() converts
        // them to the session timezone automatically when the connection
        // timezone is configured, but we handle formatting in PHP to stay
        // independent of the MySQL timezone setting.
        $rows = Yii::$app->db->createCommand(
            'SELECT
                FLOOR(action_at / :step) * :step AS slot_ts,
                application,
                COUNT(*) AS total
             FROM user_log
             WHERE action_at >= :from
               AND action_at <= :to
               AND application IN (' . $this->quoteList($this->applications) . ')
             GROUP BY slot_ts, application
             ORDER BY slot_ts ASC',
            [
                ':step' => $stepSeconds,
                ':from' => $fromUtc,
                ':to'   => $nowUtc,
            ]
        )->queryAll();

        // Merge DB results into the skeleton
        foreach ($rows as $row) {
            $label = $this->tsToLabel((int) $row['slot_ts'], $tz);
            $app   = $row['application'];

            if (isset($skeleton[$label][$app])) {
                $skeleton[$label][$app] = (int) $row['total'];
            }
        }

        return $skeleton;
    }

    /**
     * Build the empty time-slot skeleton (all values = 0).
     *
     * @return array<string, array<string, int>>
     */
    private function buildSkeleton(int $nowUtc, \DateTimeZone $tz): array
    {
        $skeleton    = [];
        $stepSeconds = $this->stepMinutes * 60;

        for ($i = $this->windowMinutes; $i >= 0; $i -= $this->stepMinutes) {
            $ts    = $nowUtc - ($i * 60);
            $label = $this->tsToLabel($ts, $tz);

            $skeleton[$label] = [];
            foreach ($this->applications as $app) {
                $skeleton[$label][$app] = 0;
            }
        }

        return $skeleton;
    }

    /**
     * Convert a UTC unix timestamp to a "HH:MM" label in the given timezone.
     */
    private function tsToLabel(int $ts, \DateTimeZone $tz): string
    {
        $dt = new \DateTime('@' . $ts);   // always UTC when prefixed with @
        $dt->setTimezone($tz);
        return $dt->format('H:i');
    }

    /**
     * Resolve the timezone to use for label formatting.
     *
     * Priority:
     *   1. Yii::$app->formatter->timeZone  (standard Yii2 app setting)
     *   2. date_default_timezone_get()      (PHP ini / server default)
     */
    private function resolveTimezone(): \DateTimeZone
    {
        $tzId = null;

        if (
            isset(Yii::$app->formatter) &&
            !empty(Yii::$app->formatter->timeZone)
        ) {
            $tzId = Yii::$app->formatter->timeZone;
        }

        if ($tzId === null) {
            $tzId = date_default_timezone_get() ?: 'UTC';
        }

        try {
            return new \DateTimeZone($tzId);
        } catch (\Exception $e) {
            return new \DateTimeZone('UTC');
        }
    }

    /* =========================================================
     *  Layout computation
     * ========================================================= */

    /**
     * Pre-compute all reusable layout numbers so the rendering
     * methods stay free of arithmetic.
     *
     * Returned array shape:
     * {
     *   plotX, plotY, plotW, plotH,   – plot area rectangle
     *   labels: string[],             – ordered X-axis labels
     *   series: string[],             – application names
     *   yMax: int,                    – Y-axis ceiling value
     *   yStep: int,                   – value between each graduation
     *   xStep: float,                 – px between consecutive X points
     *   yScale: float,                – px per unit on Y axis
     *   colors: string[],             – Bootstrap CSS vars indexed by series
     * }
     *
     * @param  array<string, array<string, int>> $data
     * @return array<string, mixed>
     */
    private function computeLayout(array $data): array
    {
        $plotX = self::PAD_LEFT;
        $plotY = self::PAD_TOP;
        $plotW = self::VB_WIDTH  - self::PAD_LEFT - self::PAD_RIGHT;
        $plotH = self::VB_HEIGHT - self::PAD_TOP  - self::PAD_BOTTOM;

        $maxValue = $this->getMaxValue($data);
        $yMax     = $this->computeYAxisMax($maxValue);
        $yStep    = (int) ($yMax / $this->graduations);

        $labels   = array_keys($data);
        $pointCnt = count($labels);

        // Distribute points evenly across the full plot width.
        // With N labels we have N-1 intervals; guard against N=1.
        $xStep  = $pointCnt > 1 ? $plotW / ($pointCnt - 1) : $plotW;
        $yScale = $yMax > 0 ? $plotH / $yMax : 1.0;

        // Assign Bootstrap colours in series order
        $colors = [];
        foreach (array_values($this->applications) as $idx => $app) {
            $colors[$app] = self::BS_COLORS[$idx] ?? self::BS_COLORS[array_key_last(self::BS_COLORS)];
        }

        return [
            'plotX'  => $plotX,
            'plotY'  => $plotY,
            'plotW'  => $plotW,
            'plotH'  => $plotH,
            'labels' => $labels,
            'series' => $this->applications,
            'yMax'   => $yMax,
            'yStep'  => $yStep,
            'xStep'  => $xStep,
            'yScale' => $yScale,
            'colors' => $colors,
        ];
    }

    /** Return the largest single value across all series/slots */
    private function getMaxValue(array $data): int
    {
        $max = 0;
        foreach ($data as $slot) {
            foreach ($slot as $count) {
                if ($count > $max) {
                    $max = $count;
                }
            }
        }
        return $max;
    }

    /**
     * Round the raw maximum up to the nearest multiple of $graduations.
     *
     * Example: max=17, graduations=5 → ceil(17/5)=4 → 4×5=20
     */
    private function computeYAxisMax(int $maxValue): int
    {
        if ($maxValue === 0) {
            return $this->graduations; // sensible default when data is all-zero
        }
        $step = (int) ceil($maxValue / $this->graduations);
        return $step * $this->graduations;
    }

    /* =========================================================
     *  SVG rendering
     * ========================================================= */

    /**
     * Entry-point: assemble the full SVG string.
     *
     * @param  array<string, array<string, int>> $data
     * @param  array<string, mixed>              $layout
     */
    private function buildSvg(array $data, array $layout): string
    {
        $uid = 'ag-' . $this->getId(); // unique prefix for this widget instance

        $parts = [];
        $parts[] = $this->svgOpen($uid);
        $parts[] = $this->svgDefs();
        $parts[] = $this->drawGrid($layout);
        $parts[] = $this->drawYAxis($layout);
        $parts[] = $this->drawXAxis($layout);

        foreach ($layout['series'] as $app) {
            $pts     = $this->computePoints($data, $layout, $app);
            $color   = $layout['colors'][$app];
            $parts[] = $this->drawBezierLine($pts, $color);
        }

        // Draw dots after all lines so they sit on top
        foreach ($layout['series'] as $app) {
            $pts     = $this->computePoints($data, $layout, $app);
            $color   = $layout['colors'][$app];
            $parts[] = $this->drawPoints($pts, $app, $color, $uid);
        }

        $parts[] = $this->drawTooltipLayer($uid);
        $parts[] = $this->drawLegend($layout);
        $parts[] = '</svg>';
        $parts[] = $this->inlineScript($uid);

        return implode("\n", $parts);
    }

    // ---------------------------------------------------------
    //  SVG shell
    // ---------------------------------------------------------

    private function svgOpen(string $uid): string
    {
        $w = self::VB_WIDTH;
        $h = self::VB_HEIGHT;

        // width="100%" makes the SVG fill its container; height="auto" keeps
        // the aspect ratio so nothing gets clipped on any screen width.
        return <<<SVG
<svg id="{$uid}"
     viewBox="0 0 {$w} {$h}"
     width="100%"
     height="auto"
     xmlns="http://www.w3.org/2000/svg"
     style="display:block;overflow:visible">
SVG;
    }

    private function svgDefs(): string
    {
        // Drop-shadow filter reused by the tooltip box
        return <<<SVG
  <defs>
    <filter id="ag-shadow" x="-20%" y="-20%" width="140%" height="140%">
      <feDropShadow dx="2" dy="2" stdDeviation="3" flood-opacity="0.25"/>
    </filter>
  </defs>
SVG;
    }

    // ---------------------------------------------------------
    //  Axes & grid
    // ---------------------------------------------------------

    /**
     * Horizontal dashed grid lines (one per graduation).
     *
     * @param array<string, mixed> $layout
     */
    private function drawGrid(array $layout): string
    {
        $out   = [];
        $out[] = '  <!-- grid lines -->';

        $x1 = $layout['plotX'];
        $x2 = $layout['plotX'] + $layout['plotW'];

        for ($i = 0; $i <= $this->graduations; $i++) {
            $y = $this->yCoord($i * $layout['yStep'], $layout);

            $opacity = ($i === 0) ? '0.4' : '0.15';
            $out[]   = "  <line"
                . " x1=\"{$x1}\" y1=\"{$y}\""
                . " x2=\"{$x2}\" y2=\"{$y}\""
                . " stroke=\"currentColor\" stroke-opacity=\"{$opacity}\""
                . " stroke-dasharray=\"4 4\""
                . " stroke-width=\"1\"/>";
        }

        return implode("\n", $out);
    }

    /**
     * Y-axis: graduation labels on the left.
     *
     * @param array<string, mixed> $layout
     */
    private function drawYAxis(array $layout): string
    {
        $out   = [];
        $out[] = '  <!-- y-axis labels -->';
        $xLbl  = $layout['plotX'] - 8; // right-align just inside padding

        for ($i = 0; $i <= $this->graduations; $i++) {
            $value = $i * $layout['yStep'];
            $y     = $this->yCoord($value, $layout);

            $out[] = "  <text"
                . " x=\"{$xLbl}\" y=\"{$y}\""
                . " font-size=\"12\" fill=\"currentColor\" fill-opacity=\"0.7\""
                . " text-anchor=\"end\" dominant-baseline=\"middle\">"
                . htmlspecialchars((string) $value)
                . "</text>";
        }

        return implode("\n", $out);
    }

    /**
     * X-axis: time labels below the plot.
     *
     * To avoid label overlap on narrow viewBoxes we print every other label
     * when there are more than 8 slots.
     *
     * @param array<string, mixed> $layout
     */
    private function drawXAxis(array $layout): string
    {
        $out      = [];
        $out[]    = '  <!-- x-axis labels -->';
        $yLbl     = $layout['plotY'] + $layout['plotH'] + 18;
        $count    = count($layout['labels']);
        $skipEven = $count > 8; // thin out labels when there are many

        foreach ($layout['labels'] as $i => $label) {
            if ($skipEven && $i % 2 !== 0) {
                continue;
            }

            $x     = $this->xCoord($i, $layout);
            $out[] = "  <text"
                . " x=\"{$x}\" y=\"{$yLbl}\""
                . " font-size=\"11\" fill=\"currentColor\" fill-opacity=\"0.7\""
                . " text-anchor=\"middle\">"
                . htmlspecialchars($label)
                . "</text>";
        }

        return implode("\n", $out);
    }

    // ---------------------------------------------------------
    //  Series rendering
    // ---------------------------------------------------------

    /**
     * Compute the SVG (x, y) pixel coordinate for each data point of a series.
     *
     * @param  array<string, array<string, int>> $data
     * @param  array<string, mixed>              $layout
     * @return list<array{x: float, y: float, value: int, label: string}>
     */
    private function computePoints(array $data, array $layout, string $app): array
    {
        $points = [];

        foreach ($layout['labels'] as $i => $label) {
            $value    = $data[$label][$app] ?? 0;
            $points[] = [
                'x'     => $this->xCoord($i, $layout),
                'y'     => $this->yCoord($value, $layout),
                'value' => $value,
                'label' => $label,
            ];
        }

        return $points;
    }

    /**
     * Render a smooth cubic Bézier polyline through all points.
     *
     * The control-point trick: for each segment [P(i-1) → P(i)] we place
     * both control points at x = midpoint, y = the respective endpoint y.
     * This produces smooth horizontal "entry/exit" tangents without
     * overshooting on steep slopes.
     *
     * @param list<array{x: float, y: float, value: int, label: string}> $points
     */
    private function drawBezierLine(array $points, string $color): string
    {
        if (count($points) < 2) {
            return '';
        }

        $d = sprintf('M %.2f %.2f', $points[0]['x'], $points[0]['y']);

        for ($i = 1, $n = count($points); $i < $n; $i++) {
            $x0  = $points[$i - 1]['x'];
            $y0  = $points[$i - 1]['y'];
            $x1  = $points[$i]['x'];
            $y1  = $points[$i]['y'];
            $cx  = ($x0 + $x1) / 2;

            $d .= sprintf(
                ' C %.2f %.2f, %.2f %.2f, %.2f %.2f',
                $cx, $y0,   // control point 1
                $cx, $y1,   // control point 2
                $x1, $y1    // end point
            );
        }

        return "  <path d=\"{$d}\" fill=\"none\" stroke=\"{$color}\" stroke-width=\"2.5\" stroke-linejoin=\"round\"/>";
    }

    /**
     * Render the interactive dot for each data point.
     *
     * Each circle carries data-* attributes read by the JS tooltip handler.
     *
     * @param list<array{x: float, y: float, value: int, label: string}> $points
     */
    private function drawPoints(array $points, string $app, string $color, string $uid): string
    {
        $out   = [];
        $out[] = "  <!-- dots: {$app} -->";

        foreach ($points as $p) {
            $cx    = round($p['x'], 2);
            $cy    = round($p['y'], 2);
            $label = htmlspecialchars($p['label']);
            $appH  = htmlspecialchars($app);
            $val   = $p['value'];

            $out[] = "  <circle"
                . " cx=\"{$cx}\" cy=\"{$cy}\" r=\"5\""
                . " fill=\"{$color}\""
                . " stroke=\"var(--bs-body-bg,#fff)\" stroke-width=\"2\""
                . " class=\"ag-dot\""
                . " data-ag=\"{$uid}\""
                . " data-app=\"{$appH}\""
                . " data-time=\"{$label}\""
                . " data-value=\"{$val}\""
                . " style=\"cursor:pointer\""
                . "/>";
        }

        return implode("\n", $out);
    }

    // ---------------------------------------------------------
    //  Tooltip overlay (pure SVG, animated via JS)
    // ---------------------------------------------------------

    /**
     * Invisible tooltip group.  JS repositions & populates it on mouseenter.
     */
    private function drawTooltipLayer(string $uid): string
    {
        return <<<SVG

  <!-- tooltip overlay -->
  <g id="{$uid}-tip" style="display:none;pointer-events:none">
    <rect id="{$uid}-tip-bg"
          rx="6" ry="6"
          fill="var(--bs-body-bg,#fff)"
          stroke="var(--bs-border-color,#dee2e6)"
          stroke-width="1"
          filter="url(#ag-shadow)"
          width="150" height="50"/>
    <text id="{$uid}-tip-app"
          x="12" y="20"
          font-size="12" font-weight="600"
          fill="var(--bs-body-color,#212529)"></text>
    <text id="{$uid}-tip-val"
          x="12" y="38"
          font-size="12"
          fill="var(--bs-secondary-color,#6c757d)"></text>
  </g>
SVG;
    }

    // ---------------------------------------------------------
    //  Legend
    // ---------------------------------------------------------

    /**
     * Colour-coded legend rendered inside the SVG below the X-axis labels.
     *
     * @param array<string, mixed> $layout
     */
    private function drawLegend(array $layout): string
    {
        $out   = [];
        $out[] = '  <!-- legend -->';

        $y        = self::VB_HEIGHT - 14;
        $swatchSz = 10;
        $spacing  = 160; // px between legend items
        $series   = $layout['series'];
        $n        = count($series);

        // Centre the legend group horizontally
        $startX = (self::VB_WIDTH - ($n - 1) * $spacing) / 2;

        foreach ($series as $idx => $app) {
            $color = $layout['colors'][$app];
            $x     = $startX + $idx * $spacing;
            $xText = $x + $swatchSz + 6;

            // Colour swatch (small rounded rect)
            $out[] = "  <rect"
                . " x=\"{$x}\" y=\"" . ($y - $swatchSz + 2) . "\""
                . " width=\"{$swatchSz}\" height=\"{$swatchSz}\""
                . " rx=\"2\""
                . " fill=\"{$color}\"/>";

            // Label text
            $out[] = "  <text"
                . " x=\"{$xText}\" y=\"{$y}\""
                . " font-size=\"12\" fill=\"currentColor\" fill-opacity=\"0.85\""
                . " dominant-baseline=\"auto\">"
                . htmlspecialchars($app)
                . "</text>";
        }

        return implode("\n", $out);
    }

    // ---------------------------------------------------------
    //  Inline JavaScript for tooltip behaviour
    // ---------------------------------------------------------

    /**
     * Tiny self-contained script that:
     * - Shows the tooltip box on mouseenter over any .ag-dot belonging to
     *   this widget instance.
     * - Positions the tooltip so it never clips outside the SVG viewport.
     * - Hides it on mouseleave.
     *
     * Using SVG client coordinates keeps everything consistent regardless
     * of how the SVG is scaled by the browser.
     */
    private function inlineScript(string $uid): string
    {
        // Use a JS IIFE so multiple widget instances on the same page don't
        // collide with each other.
        return <<<JS
<script>
(function () {
  const svgEl  = document.getElementById('{$uid}');
  const tipG   = document.getElementById('{$uid}-tip');
  const tipBg  = document.getElementById('{$uid}-tip-bg');
  const tipApp = document.getElementById('{$uid}-tip-app');
  const tipVal = document.getElementById('{$uid}-tip-val');

  if (!svgEl || !tipG) return;

  // SVG viewBox constants (must match PHP constants)
  const VB_W = {self::VB_WIDTH};
  const VB_H = {self::VB_HEIGHT};
  const TIP_W = 150;
  const TIP_H = 50;
  const MARGIN = 10;

  /**
   * Convert a mouse event position to SVG user-space coordinates.
   */
  function toSvgCoords(evt) {
    const pt  = svgEl.createSVGPoint();
    pt.x = evt.clientX;
    pt.y = evt.clientY;
    return pt.matrixTransform(svgEl.getScreenCTM().inverse());
  }

  // Attach listeners to all dots belonging to this widget
  svgEl.querySelectorAll('.ag-dot[data-ag="{$uid}"]').forEach(function (dot) {
    dot.addEventListener('mouseenter', function (evt) {
      const app   = dot.getAttribute('data-app');
      const time  = dot.getAttribute('data-time');
      const value = dot.getAttribute('data-value');

      tipApp.textContent = app;
      tipVal.textContent = time + '  —  ' + value + ' requests';

      // Position tooltip near the cursor but keep it inside the viewBox
      const pos = toSvgCoords(evt);
      let tx = pos.x + MARGIN;
      let ty = pos.y - TIP_H - MARGIN;

      if (tx + TIP_W > VB_W - MARGIN) { tx = pos.x - TIP_W - MARGIN; }
      if (ty < MARGIN)                 { ty = pos.y + MARGIN; }
      if (ty + TIP_H > VB_H - MARGIN) { ty = VB_H - TIP_H - MARGIN; }

      tipG.setAttribute('transform', 'translate(' + tx + ',' + ty + ')');
      tipG.style.display = '';
    });

    dot.addEventListener('mouseleave', function () {
      tipG.style.display = 'none';
    });
  });
}());
</script>
JS;
    }

    /* =========================================================
     *  Coordinate helpers
     * ========================================================= */

    /**
     * Map a data-value to a Y pixel inside the plot area.
     * Y=0 sits at the bottom of the plot (value = 0 maps to plotY + plotH).
     *
     * @param array<string, mixed> $layout
     */
    private function yCoord(int $value, array $layout): float
    {
        $bottom = $layout['plotY'] + $layout['plotH'];
        return $bottom - $value * $layout['yScale'];
    }

    /**
     * Map a slot index to an X pixel inside the plot area.
     *
     * @param array<string, mixed> $layout
     */
    private function xCoord(int $index, array $layout): float
    {
        return $layout['plotX'] + $index * $layout['xStep'];
    }

    /* =========================================================
     *  Misc helpers
     * ========================================================= */

    /**
     * Build a SQL-safe comma-separated, quoted list from a PHP array.
     * Uses Yii2's own quoting so the driver escaping is always correct.
     *
     * @param  string[] $values
     */
    private function quoteList(array $values): string
    {
        return implode(', ', array_map(
            static fn(string $v): string => Yii::$app->db->quoteValue($v),
            $values
        ));
    }
}
