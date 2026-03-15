<?php

namespace backend\widgets;

use yii\base\Widget;

final class DbMonitorTopQueries extends Widget
{

    /**
     * @var array<int, \backend\models\DbMonitor>
     */
    public array $queries = [];

    public function run(): string
    {
        return $this->render('db-monitor-top-queries', [
                    'queries' => $this->queries,
        ]);
    }
}
