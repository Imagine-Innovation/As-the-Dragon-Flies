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
        if ($user->is_player && $user->current_player_id !== null) {
            $hasPlayer = true;
            $inQuest = Yii::$app->session->get('questId') ? true : false;
        }

        $authorizedIds = ManageAccessRights::getAuthorizedIds($user, $hasPlayer, $inQuest);

        $authorizedMenus = Menu::find()
                ->joinWith('accessRight')
                ->where(['access_right_id' => $authorizedIds])
                ->orderBy(['sort_order' => SORT_ASC]);

        return $authorizedMenus->all();
    }
}
