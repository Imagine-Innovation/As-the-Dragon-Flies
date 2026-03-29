<?php

namespace backend\widgets;

use backend\helpers\DbMonitorHelper;
use Yii;
use yii\base\Widget;

final class DbMonitorExplainPlan extends Widget
{

    /** @var array<string, mixed> $plan */
    public array $plan = [];
    public string $sql = '';
    public int $queryId = 0;

    public function run(): string
    {
        Yii::debug(print_r($this->sql, true));
        Yii::debug(print_r($this->plan, true));
        $tree = DbMonitorHelper::renderNode($this->plan);
        Yii::debug(print_r($tree, true));
        return $this->render('db-monitor-explain-plan', [
                    'sql' => $this->sql,
                    'tree' => $tree,
                    'queryId' => $this->queryId,
        ]);
    }
}
