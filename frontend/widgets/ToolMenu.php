<?php

namespace frontend\widgets;

use common\models\Menu;
use common\components\ManageAccessRights;
use Yii;
use yii\base\Widget;

class ToolMenu extends Widget {

    public $mode = 'nav';

    public function run() {
        $render = $this->mode == 'nav' ? 'tool-menu-nav' : 'tool-menu-lobby';

        return $this->render($render, [
                    'menus' => $this->getMenus(),
                    'debugMode' => false
        ]);
    }

    private function getMenus() {
        $user = Yii::$app->user->identity;

        $hasPlayer = false;
        $inQuest = false;
        if ($user->is_player) {
            if ($user->current_player_id) {
                $hasPlayer = true;
                $inQuest = Yii::$app->session->get('questId') ? true : false;
            }
        }

        $accesRightIds = ManageAccessRights::getEntitled($user, $hasPlayer, $inQuest);

        $selectMenus = Menu::find()
                ->joinWith('accessRight')
                ->where(['access_right_id' => $accesRightIds])
                ->orderBy(['sort_order' => SORT_ASC]);

        return $selectMenus->all();
    }
}
