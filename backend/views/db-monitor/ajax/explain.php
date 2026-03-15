<?php

use backend\widgets\DbMonitorExplainPlan;

/**
 * @var array<string,mixed> $plan
 * @var string $sql
 * @var int $queryId
 */
echo DbMonitorExplainPlan::widget([
    'plan' => $plan,
    'sql' => $sql,
    'queryId' => $queryId,
]);
