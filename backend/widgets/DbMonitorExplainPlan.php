<?php

namespace backend\widgets;

use backend\helpers\DbMonitorHelper;
use yii\base\Widget;

final class DbMonitorExplainPlan extends Widget
{

    /** @var array<string, mixed> $plan */
    public array $plan = [];
    public string $sql = '';
    public int $queryId = 0;

    public function run(): string
    {
        $tree = DbMonitorHelper::renderNode($this->plan);

        return $this->render('db-monitor-explain-plan', [
                    'sql' => $this->sql,
                    'tree' => $tree,
                    'queryId' => $this->queryId,
        ]);
    }
}
