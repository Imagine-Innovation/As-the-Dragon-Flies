<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;

class EventServerController extends Controller
{

    public function actionStart() {
        $this->stdout("Starting ATDF Event Server...\n");
        $this->stdout("\n");
        $this->stdout("******************************************\n");
        $this->stdout("*                                        *\n");
        $this->stdout("*      A      TTTTT     DDDD     FFFFF   *\n");
        $this->stdout("*     A A       T       D   D    F       *\n");
        $this->stdout("*    AAAAA      T       D    D   FFF     *\n");
        $this->stdout("*    A   A      T       D   D    F       *\n");
        $this->stdout("*    A   A      T       DDDD     F       *\n");
        $this->stdout("*                                        *\n");
        $this->stdout("******************************************\n");
        $this->stdout("\n");
        Yii::$app->eventHandler->run();
    }
}
