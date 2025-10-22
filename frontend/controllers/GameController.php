<?php

namespace frontend\controllers;

use common\components\AppStatus;
use common\components\ContextManager;
use common\components\ManageAccessRights;
use common\components\QuestComponent;
use common\models\Quest;
use common\models\QuestPlayer;
use frontend\components\QuestOnboarding;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * QuestController implements the CRUD actions for Quest model.
 */
class GameController extends Controller
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
                            ['actions' => ['*'], 'allow' => false, 'roles' => ['?']],
                            [
                                'actions' => [
                                    'view',
                                    'ajax-actions', 'ajax-mission', 'ajax-next-dialog', 'ajax-quit',
                                ],
                                'allow' => ManageAccessRights::isRouteAllowed($this),
                                'roles' => ['@'],
                            ],
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

        $quest = $this->findQuest($questId);
        $questProgress = $quest->currentQuestProgress;

        if ($questProgress) {
            $render = $this->renderPartial('ajax/mission', ['questProgress' => $questProgress]);
            return ['error' => false, 'msg' => '', 'content' => $render];
        }

        return ['error' => true, 'msg' => 'Error encountered'];
    }

    public function actionAjaxActions() {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        if (!($questId = Yii::$app->request->get('questId'))) {
            return ['error' => true, 'msg' => 'Missing Quest ID'];
        }
        $quest = $this->findQuest($questId);
        $questProgress = $quest->currentQuestProgress;
        $playerId = Yii::$app->session->get('playerId');

        if ($questProgress->current_player_id !== $playerId) {
            return ['error' => true, 'msg' => 'Not your turn'];
        }

        $questComponent = new QuestComponent(['questProgress' => $questProgress]);
        $actions = $questComponent->getEligibleActions($playerId);

        if ($actions) {
            $render = $this->renderPartial('ajax/actions', ['questActions' => $actions]);
            return ['error' => false, 'msg' => 'List of eligible actions', 'content' => $render];
        }

        return ['error' => true, 'msg' => 'Error encountered'];
    }

    public function actionAjaxNextDialog() {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isGet || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $nextDialogId = Yii::$app->request->get('nextDialogId', 1);

        $dialog = \common\models\Dialog::findOne(['id' => $nextDialogId]);

        $content = $this->renderPartial('ajax/dialog', [
            'dialog' => $dialog,
        ]);

        return ['error' => false, 'msg' => '', 'content' => $content];
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
}
