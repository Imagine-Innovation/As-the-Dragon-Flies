<?php

namespace frontend\widgets;

use common\models\Menu;
use common\components\ManageAccessRights;
use Yii;
use yii\base\Widget;

class ToolMenu extends Widget
{

    public $mode = 'nav';
    public $isAdmin = false;

    public function run() {
        if ($this->isAdmin) {
            $render = $this->mode == 'nav' ? 'tool-menu-nav' : 'tool-menu-admin';
        } elseif ($this->mode === 'nav') {
            return '';
        } else {
            $render = 'tool-menu-lobby';
        }

        return $this->render($render, [
                    'menus' => $this->getMenus(),
                    'debugMode' => false
        ]);
    }

    private function getMenus() {
        $user = Yii::$app->user->identity;

        $hasPlayerSelected = false;
        $inQuest = false;
        if ($user->is_player && $user->current_player_id !== null) {
            $hasPlayerSelected = true;
            $inQuest = Yii::$app->session->get('questId') ? true : false;
        }

        $authorizedIds = ManageAccessRights::getAuthorizedIds($user, $hasPlayerSelected, $inQuest);

        $authorizedMenus = Menu::find()
                ->joinWith('accessRight')
                ->where(['access_right_id' => $authorizedIds])
                ->orderBy(['sort_order' => SORT_ASC]);

        return $authorizedMenus->all();
    }
}
