<?php

namespace backend\controllers;

use backend\models\DbMonitor;
use common\components\AccessRightsManager;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * DbMonitorController implements underlying database monitoring features.
 * @extends \yii\web\Controller<\yii\base\Module>
 */
final class DbMonitorController extends Controller
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
                        'actions' => ['index', 'explain', 'suggestion', 'refresh'],
                        //'allow' => AccessRightsManager::isRouteAllowed($this),
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $model = new DbMonitor();
        $count = $model->refreshFromEngine();

        return $this->render('index', [
                    'kpis' => $model->getKpis(),
                    'topQueries' => $model->getTopSlowQueries(),
                    'updatedRows' => $count,
        ]);
    }

    /**
     *
     * @param int $id
     * @return string
     */
    public function actionExplain(int $id): string
    {
        $model = new DbMonitor();

        return $this->renderAjax('ajax/explain', [
                    'plan' => $model->getExplainPlan($id),
                    'sql' => $model->getQueryText($id),
                    'queryId' => $id,
        ]);
    }

    /**
     *
     * @param int $id
     * @return string
     */
    public function actionSuggestion(int $id): string
    {
        $model = new DbMonitor();

        return $this->renderAjax('ajax/suggestion', [
                    'suggest' => $model->getQuerySuggestions($id),
        ]);
    }

    /**
     *
     * @return array{error: bool, msg: string, rowsUpdated?: int}
     */
    public function actionRefresh(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        $model = new DbMonitor();

        try {
            $count = $model->refreshFromEngine();
            return [
                'error' => false,
                'msg' => 'DB Monitor refreshed successfully.',
                'rowsUpdated' => $count,
            ];
        } catch (\Throwable $e) {
            return [
                'error' => true,
                'msg' => $e->getMessage(),
                'rowsUpdated' => 0,
            ];
        }
    }
}
