<?php

namespace frontend\widgets;

use common\components\AccessRightsManager;
use common\models\Menu;
use Yii;
use yii\base\Widget;

class ToolMenu extends Widget
{
    public string $mode = 'nav';
    public bool $isAdmin = false;

    /**
     *
     * @return string
     */
    public function run(): string
    {
        if ($this->isAdmin) {
            $render = $this->mode === 'nav' ? 'tool-menu-nav' : 'tool-menu-admin';
        } elseif ($this->mode === 'nav') {
            return '';
        } else {
            $render = 'tool-menu-lobby';
        }

        return $this->render($render, [
            'menus' => $this->getMenus(),
            'debugMode' => false,
        ]);
    }

    /**
     *
     * @return Menu[]
     */
    private function getMenus(): array
    {
        $user = Yii::$app->user->identity;

        $hasPlayerSelected = false;
        $inQuest = false;
        if ($user->is_player && $user->current_player_id !== null) {
            $hasPlayerSelected = true;
            $inQuest = Yii::$app->session->get('questId') ? true : false;
        }

        $authorizedIds = AccessRightsManager::getAuthorizedIds($user, $hasPlayerSelected, $inQuest);

        $authorizedMenus = Menu::find()
            ->joinWith('accessRight')
            ->where(['access_right_id' => $authorizedIds])
            ->orderBy(['sort_order' => SORT_ASC]);

        return $authorizedMenus->all();
    }
}
