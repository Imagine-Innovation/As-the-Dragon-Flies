<?php

namespace backend\controllers;

use common\components\AccessRightsManager;
use common\helpers\Utilities;
use common\models\LoginForm;
use Yii;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;

/**
 * Site controller
 * @extends \yii\web\Controller<\yii\base\Module>
 */
class SiteController extends Controller
{

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'actions' => ['login'],
                        // 'allow' => true,
                        'allow' => AccessRightsManager::isRouteAllowed($this),
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout', 'index', 'colors', 'fonts', 'icons', 'ajax-toast'],
                        'allow' => AccessRightsManager::isRouteAllowed($this),
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        //$this->layout = 'dashboard';
        return $this->render('index');
    }

    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
                    'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     *
     * @return array{error: bool, msg: string, UUID?: string, content?: string}
     */
    public function actionAjaxToast(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        // Retrieve the item ID from the POST data and find the item
        $messageHeader = Yii::$app->request->post('messageHeader');
        $message = Yii::$app->request->post('message');
        $severity = Yii::$app->request->post('severity');

        $UUID = Utilities::newUUID();
        return [
            'error' => false,
            'msg' => '',
            'UUID' => $UUID,
            'content' => $this->renderPartial('ajax/toast', [
                'UUID' => $UUID,
                'messageHeader' => $messageHeader,
                'message' => $message,
                'severity' => $severity,
            ]),
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIcons(): string
    {
        AccessRightsManager::isRouteAllowed($this);
        return $this->render('icons');
    }

    /**
     *
     * @return string
     */
    public function actionFonts(): string
    {
        AccessRightsManager::isRouteAllowed($this);
        return $this->render('fonts');
    }

    /**
     *
     * @return string
     */
    public function actionColors(): string
    {
        AccessRightsManager::isRouteAllowed($this);
        return $this->render('colors');
    }
}
