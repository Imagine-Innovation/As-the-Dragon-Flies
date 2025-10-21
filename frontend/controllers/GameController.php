<?php

namespace frontend\controllers;

use common\components\AppStatus;
use common\components\ContextManager;
use common\components\ManageAccessRights;
use common\components\QuestComponent;
use common\models\Quest;
use common\models\QuestPlayer;
use common\models\QuestProgress;
use frontend\components\AjaxRequest;
use frontend\components\QuestOnboarding;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * QuestController implements the CRUD actions for Quest model.
 */
class GameController extends Controller
{

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
                                    'resume', 'get-messages', 'quit',
                                    'ajax-get-messages', 'ajax-send-message', 'ajax-quit', 'ajax-mission', 'ajax-actions'
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
        $this->layout = 'game';

        $quest = $this->findQuest($id);
        $nbPlayers = QuestPlayer::find()
                ->where(['quest_id' => $quest->id])
                ->andWhere(['<>', 'status', AppStatus::LEFT->value])
                ->count();

        return $this->render('view', [
                    'quest' => $quest,
                    'nbPlayers' => $nbPlayers,
        ]);
    }

    public function actionAjaxQuit() {
        // Configure response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
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
        // Process player offboarding
        $withdraw = QuestOnboarding::withdrawPlayerFromQuest($player, $quest, $reason);
        if ($withdraw['error']) {
            return ['success' => false, 'message' => $withdraw['message']];
        }

        ContextManager::updateQuestContext(null);

        return ['success' => true, 'message' => "Player {$player->name} successfully withdrown from quest {$quest->name}"];
    }

    public function actionAjaxMission() {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $questId = Yii::$app->request->get('questId');
        if (!$questId) {
            return ['error' => true, 'msg' => 'Missing Quest ID'];
        }

        // Prepare Ajax request parameters
        $param = [
            'modelName' => 'QuestProgress',
            'render' => 'mission',
            'filter' => ['quest_id' => $questId, 'status' => AppStatus::IN_PROGRESS->value],
        ];

        // Process request and return response
        $ajaxRequest = new AjaxRequest($param);
        if ($ajaxRequest->makeResponse(Yii::$app->request)) {
            return $ajaxRequest->response;
        }

        return ['error' => true, 'msg' => 'Error encountered'];
    }

    public function actionAjaxActios() {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $questProgressId = Yii::$app->request->get('questProgressId');
        if (!$questProgressId) {
            return ['error' => true, 'msg' => 'Missing Quest Progress ID'];
        }

        $questProgress = $this->findQuestProgress($questProgressId);
        $playerId = Yii::$app->session->get('playerId');

        if ($questProgress->current_player_id !== $playerId) {
            return ['error' => true, 'msg' => 'Not your turn'];
        }

        $questComponent = new QuestComponent(['questProgressId' => $questProgressId]);
        $actions = $questComponent->getEligibleActions($playerId);

        if ($actions) {
            $render = $this->render('ajax/actions', ['questActions' => $actions]);
            return ['error' => false, 'msg' => 'List of eligible actions', 'content' => $render];
        }

        return ['error' => true, 'msg' => 'Error encountered'];
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
    protected function findQuest($id) {
        if (($model = Quest::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The quest you are looking for does not exist.');
    }

    protected function findQuestProgress($id) {
        if (($model = QuestProgress::findOne(['id' => $id])) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The quest progress you are looking for does not exist.');
    }
}
