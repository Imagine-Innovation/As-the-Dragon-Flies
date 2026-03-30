<?php

namespace backend\controllers;

use common\components\AccessRightsManager;
use common\models\Race;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * RaceController implements the CRUD actions for Race model.
 * @extends \yii\web\Controller<\yii\base\Module>
 */
class RaceController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        /** @phpstan-ignore-next-line */
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
                        'actions' => ['index', 'view'],
                        'allow' => AccessRightsManager::isRouteAllowed($this),
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Lists all Race models.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Race::find(),
        ]);

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Race model.
     *
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string
    {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     * Finds the Race model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Race the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Race
    {
        if (($model = Race::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The race your are looking for does not exist.');
    }
}
