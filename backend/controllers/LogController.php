<?php

namespace backend\controllers;

use Yii;
use yii\web\Controller;
use yii\filters\AccessControl;

class LogController extends Controller {

    /**
     * @inheritdoc
     */
    public function behaviors() {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Display websocket logs
     */
    public function actionWebsocket() {
        $logFile = Yii::getAlias('@runtime/logs/websocket.log');
        $logs = [];

        if (file_exists($logFile)) {
            $content = file_get_contents($logFile);
            $lines = explode("\n", $content);

            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }

                // Parse the log line
                if (preg_match('/^(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}) \[(.*?)\]\[(.*?)\]\[(.*?)\] (.*)$/m', $line, $matches)) {
                    $logs[] = [
                        'time' => $matches[1],
                        'ip' => $matches[2],
                        'user' => $matches[3],
                        'category' => $matches[4],
                        'message' => $matches[5],
                    ];
                } else {
                    $logs[] = [
                        'message' => $line,
                    ];
                }
            }

            // Reverse to show newest first
            $logs = array_reverse($logs);
        }

        return $this->render('websocket', [
                    'logs' => $logs,
        ]);
    }

    /**
     * Clear websocket logs
     */
    public function actionClearWebsocket() {
        $logFile = Yii::getAlias('@runtime/logs/websocket.log');

        if (file_exists($logFile)) {
            file_put_contents($logFile, '');
            Yii::$app->session->setFlash('success', 'WebSocket logs have been cleared.');
        } else {
            Yii::$app->session->setFlash('error', 'WebSocket log file not found.');
        }

        return $this->redirect(['websocket']);
    }
}
