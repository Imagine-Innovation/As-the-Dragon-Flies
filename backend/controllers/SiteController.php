<?php

namespace backend\controllers;

use common\components\AccessRightsManager;
use common\components\AppStatus;
use common\helpers\Utilities;
use common\models\LoginForm;
use common\models\Player;
use common\models\Quest;
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
                        'actions' => ['login', 'error'],
                        'allow' => true,
                    ],
                    [
                        'actions' => [
                            'logout',
                            'index',
                            'colors',
                            'fonts',
                            'icons',
                            'ajax-toast',
                            'ajax-active-quests',
                        ],
                        'allow' => AccessRightsManager::isRouteAllowed($this),
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                    'layout' => 'blank',
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
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxActiveQuests(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!Yii::$app->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax request'];
        }

        return [
            'error' => false,
            'msg' => '',
            'content' => $this->renderPartial('ajax/active-quests', [
                'activeQuests' => Quest::getActiveQuests(),
            ]),
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index', [
                    'activeQuests' => Quest::getActiveQuests(),
                    'topPlayers' => PlayerController::getTop10Players(),
        ]);
    }

    /**
     * Login action.
     *
     * @return string|Response
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->identity;
            if ($user && ($user->is_admin || $user->is_designer)) {
                return $this->goHome();
            }

            Yii::$app->user->logout();
            Yii::$app->session->setFlash('error', 'You do not have permission to access the backend.');
        }
        $this->layout = 'blank';

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            $user = Yii::$app->user->identity;
            if ($user && ($user->is_admin || $user->is_designer)) {
                return $this->goBack();
            }

            Yii::$app->user->logout();
            Yii::$app->session->setFlash('error', 'You do not have permission to access the backend.');
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
        return $this->render('icons');
    }

    /**
     *
     * @return string
     */
    public function actionFonts(): string
    {
        return $this->render('fonts');
    }

    /**
     *
     * @return string
     */
    public function actionColors(): string
    {
        return $this->render('colors');
    }
}
