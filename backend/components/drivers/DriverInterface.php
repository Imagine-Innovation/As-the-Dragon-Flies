<?php

namespace backend\components\drivers;

interface DriverInterface
{

    /**
     * Return decoded JSON explain plan as array.
     *
     * @param string $sqlStatement
     * @return array<string,mixed>
     */
    public function getExplainPlan(string $sqlStatement): array;

    /**
     *
     * @return array<int, array<string,mixed>>
     */
    public function refreshFromEngine(): array;

    /**
     *
     * @return array{uptime:int, threadsConnected:int, slowQueries:int, queriesPerSecond:int}
     */
    public function getKPIs(): array;
}
