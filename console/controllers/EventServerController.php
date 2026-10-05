<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;

/**
 * @extends \yii\console\Controller<\yii\base\Module>
 */
class EventServerController extends Controller
{

    /**
     *
     * @return void
     */
    public function actionStart(): void
    {
        $this->stdout("Starting ATDF Event Server...\n");
        $this->stdout("\n");
        $this->stdout("                              ####################\n");
        $this->stdout("                          ############################\n");
        $this->stdout("                       #########                #########\n");
        $this->stdout("                    ########                        ########\n");
        $this->stdout("    #######       #######                              #######       #######\n");
        $this->stdout(" ##############  #####                                    #####  ##############\n");
        $this->stdout("####################                                        ####################\n");
        $this->stdout("#######################               ####               #######################\n");
        $this->stdout(" #########################           ######           #########################\n");
        $this->stdout("   ##########################       ########       ##########################\n");
        $this->stdout("        ########################     ######     ########################\n");
        $this->stdout("            #######################  ######  #######################\n");
        $this->stdout("           ####    ##########################################    ####\n");
        $this->stdout("       ##################################################################\n");
        $this->stdout("      ####################################################################\n");
        $this->stdout("     ############################    ######    ############################\n");
        $this->stdout("     ######################          ######          ######################\n");
        $this->stdout("     ###############                 ######                 ###############\n");
        $this->stdout("            ####                     ######                     ####\n");
        $this->stdout("            #####                    ######                    #####\n");
        $this->stdout("             #####                    ####                    #####\n");
        $this->stdout("              #####                   ####                   #####\n");
        $this->stdout("               #####                  ####                  #####\n");
        $this->stdout("                 #####                 ##                 #####\n");
        $this->stdout("                  #######              ##              #######\n");
        $this->stdout("                    ########                        ########\n");
        $this->stdout("                       #########                #########\n");
        $this->stdout("                          ############################\n");
        $this->stdout("                              ####################\n");
        $this->stdout("\n");
        Yii::$app->eventHandler->run();
    }
}
