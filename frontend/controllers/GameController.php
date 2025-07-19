<?php

namespace frontend\controllers;

/**
 * Quest Management Controller
 *
 * Handles all quest-related operations including CRUD operations, tavern management,
 * and real-time chat functionality through AJAX endpoints.
 *
 * Key features:
 * - Quest listing and management
 * - Tavern system for quest joining
 * - Real-time chat system
 * - Access control for authenticated users
 *
 * @package frontend\controllers
 * @author François Gros
 * @version 1.0
 */
use common\components\AppStatus;
use common\models\Quest;
use common\models\Story;
use common\components\ManageAccessRights;
use common\helpers\UserErrorMessage;
use frontend\components\AjaxRequest;
use frontend\components\QuestMessages;
use frontend\components\QuestNotification;
use frontend\components\QuestOnboarding;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * QuestController implements the CRUD actions for Quest model.
 */
class GameController extends Controller {

    const DEFAULT_REDIRECT = 'quest/resume';

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
                                'actions' => [
                                    'index', 'update', 'delete', 'create', 'view',
                                    'resume', 'get-messages', 'leave-quest',
                                    'ajax-get-messages', 'ajax-send-message', 'ajax-leave-quest'
                                ],
                                'allow' => ManageAccessRights::isRouteAllowed($this),
                                'roles' => ['@'],
                            ],
                        ],
                    ],
                    'verbs' => [
                        'class' => VerbFilter::className(),
                        'actions' => [
                            'delete' => ['POST'],
                        ],
                    ],
                ]
        );
    }

    /**
     * Displays quest details
     *
     * Shows comprehensive information about a specific quest instance.
     *
     * @param int $id Quest ID to view
     * @return string Rendered view page
     * @throws NotFoundHttpException if quest not found
     */
    public function actionView($id) {
        return $this->render('view', [
                    'model' => $this->findModel($id),
        ]);
    }

    public function actionAjaxLeaveQuest() {
        // Configure response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['canStart' => false, 'msg' => 'Not an Ajax POST request'];
        }

        $player = Yii::$app->session->get('currentPlayer');
        if (!$player) {
            return ['success' => false, 'message' => 'Player not found'];
        }

        $quest = Yii::$app->session->get('currentQuest');
        if (!$quest) {
            return ['success' => false, 'message' => 'Quest not found'];
        }

        $reason = Yii::$app->request->post('reason');
        // Process player onboarding
        if (!QuestOnboarding::withdrawPlayerFromQuest($player, $quest, $reason)) {
            return ['success' => false, 'message' => "Unable to withdraw player {$player->name} to the quest"];
        }
        Yii::$app->session->set('currentQuest', null);
        Yii::$app->session->set('questId', null);
        Yii::$app->session->set('inQuest', false);
        return ['success' => true, 'message' => "Player {$player->name} successfully withdrown from quest {$quest->story->name}"];
    }

    /**
     * Utility method to find Quest model
     *
     * Central method for quest lookup to maintain consistency
     * and proper error handling.
     *
     * @param int $id Quest ID to find
     * @return Quest Found quest model
     * @throws NotFoundHttpException if quest not found
     */
    protected function findModel($id) {
        if (($model = Quest::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
