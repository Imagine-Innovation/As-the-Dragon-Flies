<?php

namespace frontend\controllers;

use common\components\ActionComponent;
use common\components\AppStatus;
use common\components\ContextManager;
use common\components\ManageAccessRights;
use common\components\QuestComponent;
use common\models\events\EventFactory;
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
                                    'ajax-actions', 'ajax-dialog', 'ajax-evaluate', 'ajax-mission', 'ajax-quit',
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
    public function actionView(int $id) {
        $this->layout = 'game';

        $quest = $this->findModel('Quest', ['id' => $id]);
        $nbPlayers = QuestPlayer::find()
                ->where(['quest_id' => $quest->id])
                ->andWhere(['<>', 'status', AppStatus::LEFT->value])
                ->count();

        return $this->render('view', [
                    'quest' => $quest,
                    'nbPlayers' => $nbPlayers,
        ]);
    }

    public function actionAjaxQuit(string $reason): array {
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

        // Process player offboarding
        $withdraw = QuestOnboarding::withdrawPlayerFromQuest($player, $quest, $reason);
        if ($withdraw['error']) {
            return ['success' => false, 'message' => $withdraw['message']];
        }

        ContextManager::updateQuestContext(null);

        return ['success' => true, 'message' => "Player {$player->name} successfully withdrown from quest {$quest->name}"];
    }

    public function actionAjaxMission(int $questProgressId) {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $questProgress = $this->findModel('QuestProgress', ['id' => $questProgressId]);

        if ($questProgress) {
            $render = $this->renderPartial('ajax/mission', ['questProgress' => $questProgress]);
            return ['error' => false, 'msg' => '', 'content' => $render];
        }

        return ['error' => true, 'msg' => 'Error encountered'];
    }

    public function actionAjaxActions(int $questProgressId, int $playerId): array {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $questProgress = $this->findModel('QuestProgress', ['id' => $questProgressId]);

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

    public function actionAjaxDialog(int $replyId, int $playerId, int $storyId): array {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isGet || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $reply = $this->findModel('Reply', ['id' => $replyId]);
        $dialog = $reply->nextDialog;

        $player = $this->findModel('Player', ['id' => $playerId]);

        $content = $this->renderPartial('ajax/dialog', [
            'storyId' => $storyId,
            'playerName' => $player->name,
            'reply' => $reply,
            'dialog' => $dialog,
        ]);

        //return ['error' => false, 'msg' => '', 'previousContent' => $previsouContent, 'nextContent' => $content];
        return ['error' => false, 'msg' => '', 'content' => $content, 'text' => $dialog->text];
    }

    public function actionAjaxEvaluate(): array {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $questAction = $this->findModel('QuestAction', ['quest_progress_id' => $request->post('questProgressId'), 'action_id' => $request->post('actionId')]);
        $actionComponent = new ActionComponent(['questAction' => $questAction]);
        $outcomes = $actionComponent->evaluateActionOutcome();

        $success = $this->createEvent('game-action', $request, $questAction->action->name, $outcomes);
        if (!$success) {
            return UserErrorMessage::throw($this, 'error', "Could not trigger event", self::DEFAULT_REDIRECT);
        }

        $content = $this->renderPartial('ajax/outcomes', [
            'diceRoll' => $outcomes['diceRoll'],
            'status' => $outcomes['status'],
            'outcomes' => $outcomes['outcomes'],
            'hpLoss' => $outcomes['hpLoss'],
        ]);

        $status = $outcomes['status'];
        return ['error' => false, 'msg' => "{$outcomes['diceRoll']}, action status={$status->getLabel()}", 'next' => "Move to next mission #{$outcomes['nextMissionId']}", 'content' => $content];
    }

    /**
     * Finds the model model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $modelName model type to load
     * @param array $param
     * @return common\models\modelName the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel(string $modelName, array $param) {
        Yii::debug("*** debug *** findModel modelName={$modelName}, param=" . print_r($param, true));
        $activeRecord = "\\common\\models\\{$modelName}";
        $pk = $param['id'] ?? null;
        $model = $activeRecord::findOne($pk ? ['id' => $pk] : $param);
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException("The requested {$modelName} does not exist.");
    }

    protected function createEvent(string $eventType, yii\web\Request $request, string $actionName, array $outcomes = []): bool {
        $sessionId = Yii::$app->session->get('sessionId');
        try {
            $player = $this->findModel('Player', ['id' => $request->post('playerId')]);
            $quest = $this->findModel('Quest', ['id' => $request->post('questId')]);
            $data['action'] = $actionName;
            $data['outcomes'] = $outcomes;
            $event = EventFactory::createEvent($eventType, $sessionId, $player, $quest, $data);
            $event->process();
            return true;
        } catch (\Exception $e) {
            Yii::error("Failed to broadcast '{$eventType}' event: " . $e->getMessage());
            return false;
        }
    }
}
