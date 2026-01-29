<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
use common\helpers\FindModelHelper;
use common\models\Alignment;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * AlignmentController implements the CRUD actions for Alignment model.
 */
class AlignmentController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors() {
        return array_merge(
                parent::behaviors(),
                [
                    'access' => [
                        'class' => AccessControl::class,
                        'rules' => [
                            [
                                'actions' => ['*'],
                                'allow' => false,
                                'roles' => ['?'],
                            ],
                            [
                                'actions' => ['ajax-wizard', 'view'],
                                'allow' => ManageAccessRights::isRouteAllowed($this),
                                'roles' => ['@'],
                            ],
                        ],
                    ],
                ]
        );
    }

    /**
     * Displays a single Alignment model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView(int $id): string {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     * }
     */
    public function actionAjaxWizard(): array {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $id = $request->post('id');
        Yii::debug("*** Debug *** actionAjaxWizard - id={$id}");
        $model = $this->findModel($id);

        $content = $this->renderPartial('ajax/wizard', [
            'model' => $model,
        ]);

        return ['error' => false, 'msg' => '', 'content' => $content];
    }

    /**
     * Finds the Alignment model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return Alignment the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $id): Alignment {
        /** @var Alignment $model */
        $model = FindModelHelper::findModel(Alignment::class, ['id' => $id]);
        return $model;
    }
}
