<?php

namespace backend\components;

use backend\models\DbMonitor;
use Yii;
use yii\base\Component;

class DbMonitorManager extends Component
{

    private $driver;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $db = Yii::$app->db;
        $rdbms = $db->getDriverName();

        $this->driver = match ($rdbms) {
            'mysql' => new drivers\MySQLDriver(),
            default => throw new \InvalidArgumentException("Unknown DB engine: $rdbms"),
        };
    }

    /**
     * Return decoded JSON explain plan as array.
     *
     * @param string $sqlStatement
     * @return array<string,mixed>
     */
    public function getExplainPlan(string $sqlStatement): array
    {
        return $this->driver->getExplainPlan($sqlStatement);
    }

    /**
     *
     * @return void
     */
    public function refreshSlowQueries(): void
    {
        $rows = $this->driver->refreshFromEngine();

        foreach ($rows as $row) {
            $hash = is_string($row['hash']) ? (string) $row['hash'] : '';

            $existing = DbMonitor::findOne(['hash' => $hash]);

            if ($existing === null) {
                $existing = new DbMonitor();
                $existing->hash = $hash;
            }

            $existing->sql_text = is_string($row['sql_text']) ? (string) $row['sql_text'] : '';
            $existing->avg_runtime_ms = is_numeric($row['avg_runtime_ms']) ? (int) $row['avg_runtime_ms'] : 0;
            $existing->calls_last_hour = is_numeric($row['calls_last_hour']) ? (int) $row['calls_last_hour'] : 0;
            $existing->last_seen = is_numeric($row['last_seen']) ? (int) $row['last_seen'] : (int) time() * 1000000000;
            $existing->save(false);
        }
    }

    /**
     *
     * @return array{uptime:int, threadsConnected:int, slowQueries:int, queriesPerSecond:int}
     */
    public function getKPIs(): array
    {
        return $this->driver->getKPIs();
    }

    /**
     * @param string $sqlStatement
     * @return string[]
     */
    public function getQuerySuggestions(string $sqlStatement): array
    {
        $sql = strtolower($sqlStatement);
        $s = [];

        if (str_contains($sql, 'select *')) {
            $s[] = 'Avoid SELECT *; specify columns.';
        }
        if (preg_match('/like\s+[\'"]%/i', $sql)) {
            $s[] = 'Leading wildcard in LIKE prevents index usage.';
        }
        if (str_contains($sql, 'lower(')) {
            $s[] = 'Avoid LOWER() in WHERE; use functional indexes or normalized data.';
        }
        if (strlen($sql) > 2000) {
            $s[] = 'Query is large; consider refactoring or breaking it down.';
        }

        return $s !== [] ? $s : ['No suggestions'];
    }
}
