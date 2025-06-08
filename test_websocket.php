<?php

// Include your Yii application bootstrap file or manually include the necessary classes
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/common/extensions/EventHandler/EventHandler.php';
require_once __DIR__ . '/common/extensions/EventHandler/WebSocketHandler.php';

use common\extensions\EventHandler\EventHandler;

// Create an instance of the EventHandler
$handler = EventHandler::getInstance();

// Manually register a test client for quest ID 31
echo "Registering test client for quest 31...\n";
$handler->manuallyRegisterClient('test_client_id', 40, 31);

// Try broadcasting a message to quest 31
echo "Broadcasting test message to quest 31...\n";
$result = $handler->broadcastToQuest(31, [
    'type' => 'test-message',
    'message' => 'This is a test message',
    'timestamp' => time()
        ]);

echo "Broadcast result: " . ($result ? "SUCCESS" : "FAILED") . "\n";
echo "Current quest map: " . print_r($handler->getQuestMap(), true) . "\n";
