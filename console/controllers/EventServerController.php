<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;

class EventServerController extends Controller {

    public function actionStart() {
        $this->stdout("Starting ATDF Event Server...\n");
        Yii::$app->eventHandler->run();
    }
}
