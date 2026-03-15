<?php

namespace backend\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property string $sql_text
 * @property int $avg_runtime_ms
 * @property int|null $calls_last_hour
 * @property int|null $last_seen
 */
final class DbMonitor extends ActiveRecord
{

    const MYSQL_MONITOR = "
        SELECT DIGEST_TEXT AS sql_text, ROUND(AVG_TIMER_WAIT / 1000000) AS avg_runtime_ms, COUNT_STAR AS calls_last_hour
        FROM performance_schema.events_statements_summary_by_digest
        WHERE SCHEMA_NAME=DATABASE() AND (DIGEST_TEXT LIKE 'SELECT%' OR DIGEST_TEXT LIKE 'INSERT%' OR DIGEST_TEXT LIKE 'UPDATE%' OR DIGEST_TEXT LIKE 'DELETE%')
        ORDER BY AVG_TIMER_WAIT DESC
        LIMIT 100
    ";

    public static function tableName(): string
    {
        return 'db_monitor';
    }

    /**
     * Get DB KPIs (MariaDB MVP).
     *
     * @return array{
     *   uptime:int,
     *   threadsConnected:int,
     *   slowQueries:int,
     *   queriesPerSecond:int
     * }
     */
    public function getKpis(): array
    {
        $db = Yii::$app->db;

        /** @var array{Value?:string}|false $kpi1 */
        $kpi1 = $db->createCommand('SHOW GLOBAL STATUS LIKE "Uptime"')->queryOne();
        $uptime = isset($kpi1['Value']) ? (int) $kpi1['Value'] : 0;

        /** @var array{Value?:string}|false $kpi2 */
        $kpi2 = $db->createCommand('SHOW STATUS LIKE "Threads_connected"')->queryOne();
        $threads = isset($kpi2['Value']) ? (int) $kpi2['Value'] : 0;

        /** @var array{Value?:string}|false $kpi3 */
        $kpi3 = $db->createCommand('SHOW GLOBAL STATUS LIKE "Slow_queries"')->queryOne();
        $slow = isset($kpi3['Value']) ? (int) $kpi3['Value'] : 0;

        /** @var array{Value?:string}|false $d */
        $d = $db->createCommand('SHOW GLOBAL STATUS LIKE "Queries"')->queryOne();
        $queries = isset($d['Value']) ? (int) $d['Value'] : 0;

        return [
            'uptime' => $uptime,
            'threadsConnected' => $threads,
            'slowQueries' => $slow,
            'queriesPerSecond' => $queries,
        ];
    }

    /**
     * @return array<int, self>
     */
    public function getTopSlowQueries(): array
    {
        /** @var array<int, self> $rows */
        $rows = self::find()
                ->orderBy(['avg_runtime_ms' => SORT_DESC])
                ->limit(10)
                ->all();

        return $rows;
    }

    public function getQueryText(int $id): string
    {
        $row = self::findOne($id);
        return $row ? $row->sql_text : '';
    }

    /**
     * Return decoded JSON explain plan as array.
     * MariaDB MVP; future drivers can be added (Postgres, Oracle, SQL Server).
     *
     * @return array<string,mixed>
     */
    public function getExplainPlan(int $id): array
    {
        $query = self::findOne($id);
        if ($query === null) {
            return [];
        }

        try {
            /** @var array{EXPLAIN?:string}|false $result */
            $result = Yii::$app->db
                    ->createCommand('EXPLAIN FORMAT=JSON ' . $query->sql_text)
                    ->queryOne();
        } catch (\Throwable $e) {
            return ['error' => $e->getMessage()];
        }

        if (!is_array($result) || !isset($result['EXPLAIN'])) {
            return [];
        }

        /** @var array<string,mixed> $decoded */
        $decoded = json_decode($result['EXPLAIN'], true);
        return $decoded;
    }

    /**
     * @return string[]
     */
    public function getQuerySuggestions(int $id): array
    {
        $row = self::findOne($id);
        if ($row === null) {
            return ['Query not found'];
        }

        $sql = strtolower($row->sql_text);
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

    private function getRefreshQuery(string $rdbms): string
    {
        return match ($rdbms) {
            'mysql' => self::MYSQL_MONITOR,
            default => self::MYSQL_MONITOR
        };
    }

    /**
     * Refresh monitor table from MariaDB slow query data.
     *
     * @return int Number of rows inserted/updated
     */
    public function refreshFromEngine(): int
    {
        $db = Yii::$app->db;

        $rdbms = $db->getDriverName();
        $monitorSqlQuery = $this->getRefreshQuery($rdbms);

        /** @var array<int, array<string,mixed>> $rows */
        $rows = $db->createCommand($monitorSqlQuery)->queryAll();

        $count = 0;

        foreach ($rows as $r) {
            $sql = is_string($r['sql_text']) ? (string) $r['sql_text'] : '';

            // Try to find existing
            /** @var DbMonitor|null $existing */
            $existing = self::findOne(['sql_text' => $sql]);

            if ($existing === null) {
                $existing = new self();
            }

            $existing->sql_text = $sql;
            $existing->avg_runtime_ms = is_numeric($r['avg_runtime_ms']) ? (int) $r['avg_runtime_ms']
                        : 0;
            $existing->calls_last_hour = is_numeric($r['calls_last_hour']) ? (int) $r['calls_last_hour']
                        : 0;
            $existing->last_seen = (int) time();
            $existing->save(false);

            $count++;
        }

        return $count;
    }
}
