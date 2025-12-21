<?php

namespace frontend\controllers;

use common\components\AppStatus;
use common\components\ContextManager;
use common\components\gameplay\ActionManager;
use common\components\gameplay\QuestManager;
use common\components\gameplay\TavernManager;
use common\components\ManageAccessRights;
use common\helpers\FindModelHelper;
use common\models\events\EventFactory;
use common\models\QuestPlayer;
use common\models\QuestTurn;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Request;
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
                                    'ajax-actions', 'ajax-dialog', 'ajax-evaluate', 'ajax-mission', 'ajax-next-turn',
                                    'ajax-player', 'ajax-quit', 'ajax-turn',
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
     * Shows comprehensive information about a specific quest instance
     * in a virtual tabletop (VTT) layout
     *
     * @param int $id Quest ID to view
     * @return string Rendered view page
     * @throws NotFoundHttpException if quest not found
     */
    public function actionView(int $id) {
        $this->layout = 'game';

        $quest = FindModelHelper::findQuest($id);
        $nbPlayers = QuestPlayer::find()
                ->where(['quest_id' => $quest->id])
                ->andWhere(['<>', 'status', AppStatus::LEFT->value])
                ->count();

        return $this->render('view', [
                    'quest' => $quest,
                    'nbPlayers' => $nbPlayers,
        ]);
    }

    /**
     * Handles AJAX request to retreive the player's current health
     *
     * @return array JSON response containing tavern state
     *   - error: boolean indicating request status
     *   - msg: string message for client
     *   - content: HTML content for tavern update
     */
    public function actionAjaxPlayer(?int $id) {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $playerId = $id ?? Yii::$app->session->get('playerId');

        $player = FindModelHelper::findPlayer($playerId);
        if ($player) {
            $render = $this->renderPartial('ajax/player', ['player' => $player]);
            return ['error' => false, 'msg' => '', 'content' => $render];
        }

        return ['error' => true, 'msg' => 'Error encountered'];
    }

    /**
     * Ajax POST request to handle player's quitting the game
     *
     * @param string $reason
     * @return array
     */
    public function actionAjaxQuit(string $reason): array {
        // Configure response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $player = Yii::$app->session->get('currentPlayer');
        if (!$player) {
            return ['error' => true, 'msg' => 'Player not found'];
        }

        $quest = Yii::$app->session->get('currentQuest');
        if (!$quest) {
            return ['error' => true, 'msg' => 'Quest not found'];
        }

        // Process player offboarding
        $tavernManager = new TavernManager(['quest' => $quest]);
        $withdraw = $tavernManager->withdrawPlayerFromQuest($player, $reason);
        if ($withdraw['error']) {
            return ['error' => true, 'msg' => $withdraw['message']];
        }

        ContextManager::updateQuestContext(null);

        return ['error' => false, 'msg' => "Player {$player->name} successfully withdrown from quest {$quest->name}"];
    }

    /**
     * Ajax GET request to get the mission description layout
     *
     * @return array
     */
    public function actionAjaxMission(int $missionId): array {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $mission = FindModelHelper::findMission($missionId);
        $render = $this->renderPartial('ajax/mission', ['mission' => $mission]);
        return ['error' => false, 'msg' => '', 'content' => $render, 'title' => $mission->name];
    }

    /**
     * Ajax GET request to get the turn info
     *
     * @return array
     */
    public function actionAjaxTurn() {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $questTurn = QuestTurn::find()
                ->where(['status' => AppStatus::IN_PROGRESS->value, 'quest_progress_id' => Yii::$app->request->get('questProgressId')])
                ->one();

        if ($questTurn) {
            $content = [
                'playerId' => $questTurn->player_id,
                'sequence' => $questTurn->sequence,
            ];
            return ['error' => false, 'msg' => '', 'content' => $content];
        }

        return ['error' => true, 'msg' => 'Error encountered'];
    }

    /**
     * Ajax GET request to retreive the eligible actions for a player
     *
     * @return array
     */
    public function actionAjaxActions(int $questProgressId): array {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $questProgress = FindModelHelper::findQuestProgress($questProgressId);

        if ($questProgress->current_player_id !== Yii::$app->session->get('playerId')) {
            return ['error' => true, 'msg' => 'Not your turn'];
        }

        $remainingActions = $questProgress->remainingActions;

        if ($remainingActions) {
            $render = $this->renderPartial('ajax/actions', ['questActions' => $remainingActions]);
            return ['error' => false, 'msg' => '', 'content' => $render];
        }
        // There are no more actions remaining, this mission is considered complete,
        // we move on to the next mission.
        $questManager = new QuestManager(['questProgress' => $questProgress]);
        return $questManager->moveToNextMission();
    }

    /**
     * Ajax GET request to manage the dialog between a player and a NPC
     *
     * @param int $replyId
     * @param int $playerId
     * @param int $storyId
     * @return array
     */
    public function actionAjaxDialog(int $replyId, int $playerId, int $storyId): array {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isGet || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $reply = FindModelHelper::findReply($replyId);
        $dialog = $reply->nextDialog;

        $player = FindModelHelper::findPlayer($playerId);

        $content = $this->renderPartial('ajax/dialog', [
            'storyId' => $storyId,
            'playerName' => $player->name,
            'reply' => $reply,
            'dialog' => $dialog,
        ]);

        return [
            'error' => false, 'msg' => '', 'content' => $content,
            'text' => $dialog->text, 'audio' => $dialog->audio
        ];
    }

    /**
     * Retrieves the POST parameters, evaluates the results of the completed action, and triggers a "game-action" event.
     *
     * @param Request $postRequest
     * @return array An associative array that contains what should be displayed
     */
    protected function getOutcome(Request $postRequest): array {
        $param = [
            'quest_progress_id' => $postRequest->post('questProgressId'),
            'action_id' => $postRequest->post('actionId')
        ];
        $questAction = FindModelHelper::findQuestAction($param);
        $actionManager = new ActionManager(['questAction' => $questAction]);
        $outcome = $actionManager->evaluateActionOutcome();

        $this->createEvent('game-action', $postRequest, $questAction->action->name, $outcome);

        return $outcome;
    }

    /**
     * Ajax POST request that evaluates the outcome of the completed action
     *
     * @return array Json encoded associative array with error status, internal message, and content to display
     */
    public function actionAjaxEvaluate(): array {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $outcome = $this->getOutcome(Yii::$app->request);

        $content = $this->renderPartial('ajax/outcomes', $outcome);
        return ['error' => false, 'msg' => '', 'content' => $content];
    }

    /**
     * Ajax POST request to handle moving to the next turn
     *
     * @return array Json encoded associative array with error status, internal message, and content to display
     */
    public function actionAjaxNextTurn(): array {
        // Set the response format to JSON
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Check if the request is a POST request and if it is an AJAX request
        if (!$this->request->isPost || !$this->request->isAjax) {
            // If not, return an error response
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $questProgress = FindModelHelper::findQuestProgress($request->post('questProgressId'));

        // Set the context of the QuestManager to the current QuestProgress
        $questManager = new QuestManager(['questProgress' => $questProgress]);
        $nextMissionId = $request->post('nextMissionId');
        $currentMissionId = $request->post('missionId');
        $remainingActions = $questProgress->remainingActions;

        Yii::debug("*** debug *** actionAjaxNextTurn - currentMissionId={$currentMissionId}, nextMissionId={$nextMissionId}, remainingAction=" . count($remainingActions));

        if ($remainingActions) {
            if ($nextMissionId && $nextMissionId !== $currentMissionId) {
                // One of the results of the previous action indicates
                // that you should move on to another mission.
                // This takes over the processing of the remaining actions.
                return $questManager->moveToNextMission($nextMissionId);
            }
            return $questManager->nextPlayer();
        }
        // Move to the default mission
        return $questManager->moveToNextMission($nextMissionId);
    }

    protected function createEvent(string $eventType, Request $postRequest, string $actionName, array $outcome = []): bool {
        $sessionId = Yii::$app->session->get('sessionId');
        try {
            $player = FindModelHelper::findPlayer($postRequest->post('playerId'));
            $quest = FindModelHelper::findQuest($postRequest->post('questId'));
            $data['action'] = $actionName;
            $data['detail'] = [
                'diceRoll' => $outcome['diceRoll'],
                'status' => $outcome['status'],
                'outcomes' => $outcome['outcomes'],
                'hpLoss' => $outcome['hpLoss'],
            ];
            $event = EventFactory::createEvent($eventType, $sessionId, $player, $quest, $data);
            $event->process();
            return true;
        } catch (\Exception $e) {
            Yii::error("Failed to broadcast '{$eventType}' event: " . $e->getMessage());
            throw new \Exception(implode("<br />", ArrayHelper::getColumn($e, 0, false)));
        }
    }
}
