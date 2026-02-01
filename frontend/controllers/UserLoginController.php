<?php

namespace frontend\controllers;

use common\components\ManageAccessRights;
use common\models\UserLogin;
use frontend\components\AjaxRequest;
use Yii;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * UserLoginController implements the CRUD actions for UserLogin model.
 */
class UserLoginController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        /** @phpstan-ignore-next-line */
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
                                'actions' => ['index', 'ajax'],
                                'allow' => ManageAccessRights::isRouteAllowed($this),
                                'roles' => ['@'],
                            ],
                        ],
                    ],
                    'verbs' => [
                        'class' => VerbFilter::className(),
                        'actions' => [
                            'delete' => ['POST'],
                            'ajax' => ['POST', 'GET'],
                        ],
                    ],
                ]
        );
    }

    /**
     * Lists all UserLogin models.
     *
     * This action is limited to user with Admin role
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => UserLogin::find(),
            'sort' => [
                'defaultOrder' => [
                    'login_at' => SORT_DESC,
                    'application' => SORT_DESC,
                ]
            ],
        ]);

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
        ]);
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjax(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $param = [
            'modelName' => 'UserLogin',
            'sortOrder' => [
                'login_at' => SORT_DESC,
                'application' => SORT_DESC,
            ],
        ];
        $ajaxRequest = new AjaxRequest($param);

        if ($ajaxRequest->makeResponse(Yii::$app->request)) {
            return $ajaxRequest->response;
        }
        return ['error' => true, 'msg' => 'Error encountered'];
    }

    /**
     * Finds the UserLogin model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $userId Primary Key and Foreign Key to the [User] entity
     * @param string $application Application logged to
     * @param int $loginAt Login at
     * @return UserLogin the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(int $userId, string $application, int $loginAt): UserLogin
    {
        if (($model = UserLogin::findOne([
            'user_id' => $userId,
            'application' => $application,
            'login_at' => $loginAt
                ])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The login event you are looking for does not exist.');
    }
}
