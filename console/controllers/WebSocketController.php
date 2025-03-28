<?php
namespace console\controllers;

use yii\console\Controller;
use common\components\WebSocketServer;

class WebSocketController extends Controller
{
    public function actionStart()
    {
        $this->stdout("Starting WebSocket Server\n");
        $server = new WebSocketServer();
        $server->run();
    }
}
