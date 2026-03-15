<?php

/**
 * @var string[] $suggest
 */
use backend\widgets\DbMonitorSuggestion;

echo DbMonitorSuggestion::widget([
    'suggestions' => $suggest,
]);
