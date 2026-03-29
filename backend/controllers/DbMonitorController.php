<?php

namespace backend\controllers;

use backend\components\DbMonitorManager;
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
                        'actions' => ['index', 'ajax-explain', 'ajax-suggestion', 'refresh'],
                        'allow' => AccessRightsManager::isRouteAllowed($this),
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
        $dbMonitor = new DbMonitorManager();
        $kpis = $dbMonitor->getKPIs();
        $limit = 10;

        $dbMonitor->refreshSlowQueries();
        /** @var array<int, DbMonitor> $rows */
        $slowQueries = DbMonitor::find()
                ->orderBy(['avg_runtime_ms' => SORT_DESC])
                ->limit($limit)
                ->all();

        return $this->render('index', [
                    'kpis' => $kpis,
                    'topQueries' => $slowQueries,
        ]);
    }

    /**
     *
     * @param int $id
     * @return string
     */
    public function actionAjaxExplain(int $id): string
    {
        $model = $this->findModel($id);

        $dbMonitor = new DbMonitorManager();

        $sql = $model->sql_text;
        $explainPlan = $dbMonitor->getExplainPlan($sql);
        Yii::debug($explainPlan);
        return $this->renderPartial('ajax/explain', [
                    'plan' => $explainPlan,
                    'sql' => $sql,
                    'queryId' => $id,
        ]);
    }

    /**
     *
     * @param int $id
     * @return string
     */
    public function actionAjaxSuggestion(int $id): string
    {
        $model = $this->findModel($id);
        $dbMonitor = new DbMonitorManager();
        $suggestions = $dbMonitor->getQuerySuggestions($model->sql_text);

        return $this->renderAjax('ajax/suggestion', [
                    'suggestions' => $suggestions,
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

    /**
     * Finds the DbMonitor model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id Primary Key
     * @return DbMonitor the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): DbMonitor
    {
        if (($model = DbMonitor::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The user your are looking for does not exist.');
    }
}
