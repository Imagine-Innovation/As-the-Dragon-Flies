<?php

namespace backend\controllers;

use backend\helpers\KpiHelper;
use common\components\AppStatus;
use common\components\AccessRightsManager;
use common\models\Quest;
use common\models\Player;
use common\models\Story;
use common\models\User;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * KPIController implements the CRUD actions for Player model.
 * @extends \yii\web\Controller<\yii\base\Module>
 */
class KpiController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['*'],
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['update'],
                        'allow' => AccessRightsManager::isRouteAllowed($this),
                        // 'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     *
     * @return int
     */
    private function getActiveUsers(): int
    {
        $count = User::find()
                ->where(['status' => AppStatus::ACTIVE->value])
                ->count();

        return (int) $count;
    }

    /**
     *
     * @return int
     */
    private function getActivePlayers(): int
    {
        $count = Player::find()
                ->where(['status' => AppStatus::ACTIVE->value])
                ->count();

        return (int) $count;
    }

    /**
     *
     * @return int
     */
    private function getActiveStories(): int
    {
        $count = Story::find()
                ->where(['status' => AppStatus::PUBLISHED->value])
                ->count();

        return (int) $count;
    }

    /**
     *
     * @return int
     */
    private function getActiveQuests(): int
    {
        $count = Quest::find()
                ->where(['status' => [AppStatus::WAITING->value, AppStatus::PLAYING->value, AppStatus::PAUSED->value]])
                ->count();

        return (int) $count;
    }

    /**
     *
     * @param string $name
     * @return int
     */
    private function getKpiValue(string $name): int
    {
        return match ($name) {
            'users' => $this->getActiveUsers(),
            'players' => $this->getActivePlayers(),
            'stories' => $this->getActiveStories(),
            'quests' => $this->getActiveQuests(),
            default => 0
        };
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: array<string, int>}
     */
    public function actionUpdate(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax request'];
        }

        $KPIs = [];
        foreach (KpiHelper::KPI as $name => $config) {
            $KPI = [$config['containerName'] => $this->getKpiValue($name)];
            $KPIs = empty($KPIs) ? $KPI : [...$KPIs, ...$KPI];
        }

        return ['error' => true, 'msg' => 'Not an Ajax request', 'content' => $KPIs];
    }
}
