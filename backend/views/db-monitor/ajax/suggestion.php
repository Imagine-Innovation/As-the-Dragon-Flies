<?php

/**
 * @var string[] suggestions
 */
use backend\widgets\DbMonitorSuggestion;

echo DbMonitorSuggestion::widget([
    'suggestions' => $suggest,
]);
