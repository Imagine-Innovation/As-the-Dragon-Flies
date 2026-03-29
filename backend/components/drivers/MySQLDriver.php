<?php

namespace backend\components\drivers;

use backend\components\drivers\DriverInterface;
use backend\models\DbMonitor;
use Yii;

/**
 *
 *
 * Before you sttart, vérify you have coorecly configured my.ini (Windows) or my.conf (Linux)
 *
 * Edit your MariaDB config file:
 *      Linux : /etc/my.cnf or /etc/mysql/my.cnf
 *      Windows : C:\Program Files\MariaDB XX\data\my.ini
 * Add:
 * [mysqld]
 * performance_schema=ON
 * performance_schema_events_statements_history_long_size=10000
 *
 * Save and restart MariaDB/mySQL
 *
 * Below is a fully ready-to-paste SQL script that correctly enables Performance Schema, statement instruments,
 * statement consumers, and history tables required to populate:
 *
 * events_statements_history
 * events_statements_history_long
 * events_statements_current
 * Digest tables
 *
 * This script reflects MariaDB/MySQL mechanics documented in Performance Schema configuration guides, which explain
 * that BOTH instruments and consumers must be enabled for the history tables to populate.
 * It also relies on the documented variable performance_schema_events_statements_history_long_size, which configures
 * the number of rows stored in history_long at startup.
 * -- ==========================================================
 * -- FULL PERFORMANCE SCHEMA PROFILING ACTIVATION
 *   -- Works for MySQL / MariaDB (including XAMPP installations)
 * -- ==========================================================
 *
 * -- ----------------------------------------------------------
 * -- 1. ENABLE ALL STATEMENT INSTRUMENTS
 * -- These capture the actual SQL events.
 * -- ----------------------------------------------------------
 * UPDATE performance_schema.setup_instruments
 * SET ENABLED = 'YES', TIMED = 'YES'
 * WHERE NAME LIKE 'statement%';
 *
 * -- ----------------------------------------------------------
 * -- 2. ENABLE STAGE INSTRUMENTS (optional but very useful)
 * -- Shows parse/optimize/execute phases.
 * -- ----------------------------------------------------------
 * UPDATE performance_schema.setup_instruments
 * SET ENABLED = 'YES', TIMED = 'YES'
 * WHERE NAME LIKE 'stage%';
 *
 * -- ----------------------------------------------------------
 * -- 3. ENABLE WAIT INSTRUMENTS (optional)
 * -- Helps understand lock waits, I/O waits, mutex waits.
 * -- ----------------------------------------------------------
 * UPDATE performance_schema.setup_instruments
 * SET ENABLED = 'YES', TIMED = 'YES'
 * WHERE NAME LIKE 'wait%';
 *
 * -- ----------------------------------------------------------
 * -- 4. ENABLE CONSUMERS FOR STATEMENT HISTORY
 * -- WITHOUT THESE, history tables remain EMPTY.
 * -- (This is the #1 reason your tables were empty.)
 * -- ----------------------------------------------------------
 * UPDATE performance_schema.setup_consumers
 * SET ENABLED = 'YES'
 * WHERE NAME IN (
 * 'events_statements_current',
 * 'events_statements_history',
 * 'events_statements_history_long',
 * 'statements_digest',
 * 'events_statements_summary_by_digest'
 * );
 *
 * -- ----------------------------------------------------------
 * -- 5. ENABLE OTHER USEFUL CONSUMERS (optional)
 * -- These enrich diagnostic detail.
 * -- ----------------------------------------------------------
 * UPDATE performance_schema.setup_consumers
 * SET ENABLED = 'YES'
 * WHERE NAME IN (
 * 'events_waits_current',
 * 'events_waits_history',
 * 'events_waits_history_long',
 * 'events_stages_current',
 * 'events_stages_history',
 * 'events_stages_history_long'
 * );
 *
 * -- ----------------------------------------------------------
 * -- 6. VERIFY EVERYTHING IS ENABLED
 * -- ----------------------------------------------------------
 * SELECT * FROM performance_schema.setup_consumers;
 * SELECT * FROM performance_schema.setup_instruments
 * WHERE ENABLED = 'YES'
 * ORDER BY NAME;
 *
 * -- ----------------------------------------------------------
 * -- 7. TEST: RUN A FEW STATEMENTS, THEN CHECK HISTORY
 * -- ----------------------------------------------------------
 * SELECT 1;
 * SELECT SLEEP(0.3);
 * SELECT * FROM performance_schema.events_statements_history_long
 * ORDER BY event_id DESC
 * LIMIT 5;
 *
 */
class MySQLDriver implements DriverInterface
{

    const SECONDS_TO_PICO = 1000 * 1000 * 1000 * 1000;
    const MILLI_SECONDS_TO_PICO = 1000 * 1000 * 1000;

    /**
     * Returns the last known real SQL query for a given digest.
     *
     * @param string $hash Hexadecimal digest string from Performance Schema
     * @return string|null
     */
    private function getLastQueryByDigest(string $hash): ?string
    {
        $sql = "SELECT sql_text "
                . "FROM performance_schema.events_statements_history_long "
                . "WHERE digest = :hash "
                . "ORDER BY event_id DESC "
                . "LIMIT 1";

        $row = Yii::$app->db->createCommand($sql, [
                    ':hash' => $hash,
                ])->queryOne();

        $sql = $row['sql_text'];
        return is_string($sql) ? (string) $sql : null;
    }

    /**
     * Return decoded JSON explain plan as array.
     *
     * @param string $sqlStatement
     * @return array<string,mixed>
     */
    public function getExplainPlan(string $sqlStatement): array
    {
        try {
            /** @var array{EXPLAIN?:string}|false $result */
            $result = Yii::$app->db
                    ->createCommand('EXPLAIN FORMAT=JSON ' . $sqlStatement)
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
     *
     * @return int
     */
    private function getStartTimeInPicoSeconds(): int
    {
        $db = Yii::$app->db;
        $sqlStatement = "SELECT UNIX_TIMESTAMP(NOW()) - variable_value AS start_ts "
                . "FROM information_schema.global_status "
                . "WHERE variable_name = 'Uptime'";

        $dbServer = $db->createCommand($sqlStatement)->queryOne();

        $startTimeInSeconds = isset($dbServer['start_ts']) ? (int) $dbServer['start_ts'] : (time() - 3600);
        return (int) floor($startTimeInSeconds * self::SECONDS_TO_PICO);
    }

    /**
     *
     * @param int $dbStartTime Databae engine start time in pico seconds
     * @param int $observationWindow Observation window in seconds. Default = 1 hour
     * @return array<int, array<string,mixed>>
     */
    private function getStatementHistory(int $dbStartTime, int $observationWindow = 3600): array
    {
        $db = Yii::$app->db;
        $schema = $db->getSchema();
        Yii::debug(print_r($schema, true));
        $schemaName = $db->getSchema()->defaultSchema ?? 'dnd';
        $oneHourAgo = (time() - $observationWindow) * self::MILLI_SECONDS_TO_PICO; // 1 hour from now in pico seconds
        // Ensures that the timer's start value is not negative if the database engine was started less than an hour ago
        $timerStartFrom = max($oneHourAgo - $dbStartTime, 0);

        Yii::debug("getStatementHistory - oneHourAgo=$oneHourAgo, dbStartTime=$dbStartTime, schemaName=$schemaName, timerStartFrom=$timerStartFrom");

        $sqlStatement = "SELECT "
                . "AVG(TIMER_WAIT)/" . self::MILLI_SECONDS_TO_PICO . "  AS avg_runtime_ms, "
                . "MAX(TIMER_START) AS last_start, "
                . "COUNT(*) AS count, "
                . "DIGEST AS hash
                FROM performance_schema.events_statements_history_long
                WHERE "
                . "CURRENT_SCHEMA='{$schemaName}' "
                . "AND EVENT_NAME in ('statement/sql/select', 'statement/sql/update', 'statement/sql/insert', 'statement/sql/delete') "
                . "AND TIMER_START > {$timerStartFrom}
                GROUP BY DIGEST";

        return $db->createCommand($sqlStatement)->queryAll();
    }

    /**
     *
     * @return array<int, array<string,mixed>>
     */
    public function refreshFromEngine(): array
    {
        if (!$this->isPerformanceSchemaConfigured()) {
            $this->initPerformanceSchema();
        }

        $dbStartTime = $this->getStartTimeInPicoSeconds();
        $statementHistory = $this->getStatementHistory($dbStartTime, 3600);

        $dbMonitor = [];
        foreach ($statementHistory as $statement) {
            $hash = is_string($statement['hash']) ? (string) $statement['hash'] : '';
            $sql = $this->getLastQueryByDigest($hash);
            $lastStart = $statement['last_start'];
            if ($sql) {
                $dbMonitor[] = [
                    'sql_text' => $sql,
                    'hash' => $hash,
                    'avg_runtime_ms' => $statement['avg_runtime_ms'],
                    'calls_last_hour' => $statement['count'],
                    'last_seen' => (is_numeric($lastStart) ? (int) $lastStart : 0) + $dbStartTime,
                ];
            }
        }
        return $dbMonitor;
    }

    /**
     *
     * @param array<string, mixed> $kpis
     * @param string $kpiName
     * @return int
     */
    private function intValue(array $kpis, string $kpiName): int
    {
        if (isset($kpis[$kpiName]) && is_numeric($kpis[$kpiName])) {
            return (int) $kpis[$kpiName];
        }
        return 0;
    }

    /**
     *
     * @return array{uptime:int, threadsConnected:int, slowQueries:int, queriesPerSecond:int}
     */
    public function getKPIs(): array
    {
        /** @var array<int, array{Variable_name: string, Value: string|int}> $globalVars */
        $globalVars = Yii::$app->db->createCommand("SHOW GLOBAL STATUS WHERE Variable_name "
                        . "IN ('Uptime', 'Threads_connected', 'Slow_queries', 'Questions')")->queryAll();

        $kpis = [];
        foreach ($globalVars as $var) {
            $kpis[$var['Variable_name']] = $var['Value'];
        }

        return [
            'uptime' => $this->intValue($kpis, 'Uptime'),
            'threadsConnected' => $this->intValue($kpis, 'Threads_connected'),
            'slowQueries' => $this->intValue($kpis, 'Slow_queries'),
            'queriesPerSecond' => $this->intValue($kpis, 'Questions'),
        ];
    }

    /**
     *
     * @param string $name
     * @return int
     */
    private function enableInstruments(string $name): int
    {
        $db = Yii::$app->db;
        $updateStatement = "UPDATE performance_schema.setup_instruments "
                . "SET ENABLED = 'YES', TIMED = 'YES' "
                . "WHERE NAME LIKE :name";
        $rowsAffected = (int) $db->createCommand($updateStatement, [':name' => $name])->execute();

        return $rowsAffected;
    }

    /**
     *
     * @return void
     */
    private function initPerformanceSchema(): void
    {
        // 1. ENABLE ALL STATEMENT INSTRUMENTS: These capture the actual SQL events.
        $this->enableInstruments('statement%');

        // 2. ENABLE STAGE INSTRUMENTS (optional but very useful): Shows parse/optimize/execute phases.
        $this->enableInstruments('stage%');

        // 3. ENABLE WAIT INSTRUMENTS (optional): Helps understand lock waits, I/O waits, mutex waits.
        $this->enableInstruments('wait%');

        // 4. ENABLE CONSUMERS FOR STATEMENT HISTORY WITHOUT THESE, history tables remain EMPTY.
        $enableConsumersForStatement = "UPDATE performance_schema.setup_consumers
            SET ENABLED = 'YES'
            WHERE NAME IN (
                'events_statements_current', 'events_statements_history',
                'events_statements_history_long', 'statements_digest',
                'events_statements_summary_by_digest'
                'events_waits_current', 'events_waits_history', 'events_waits_history_long',
                'events_stages_current', 'events_stages_history', 'events_stages_history_long'
            )";
        Yii::$app->db->createCommand($enableConsumersForStatement)->execute();
    }

    /**
     *
     * @return bool
     */
    private function isPerformanceSchemaConfigured(): bool
    {
        $db = Yii::$app->db;

        /** @var array{Value?:string}|false $consumer */
        $consumer = $db->createCommand("select enabled from performance_schema.setup_consumers"
                        . " where name='events_statements_history_long'")
                ->queryOne();

        return (isset($consumer['enabled']) && $consumer['enabled'] === 'YES');
    }
}
