<?php

namespace frontend\controllers;

use common\components\AppStatus;
use common\components\ContextManager;
use common\components\gameplay\ActionManager;
use common\components\gameplay\QuestManager;
use common\components\gameplay\OutcomeManager;
use common\components\gameplay\TavernManager;
use common\components\AccessRightsManager;
use common\helpers\FindModelHelper;
use common\models\events\EventFactory;
use common\models\QuestPlayer;
use common\models\QuestTurn;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;

/**
 * GameController implements the CRUD actions for Quest model.
 * @extends \yii\web\Controller<\yii\base\Module>
 */
class GameController extends Controller
{

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    ['actions' => ['*'], 'allow' => false, 'roles' => ['?']],
                    [
                        'actions' => [
                            'view',
                            'ajax-actions',
                            'ajax-dialog',
                            'ajax-get-outcomes',
                            'ajax-mission',
                            'ajax-next-turn',
                            'ajax-player',
                            'ajax-quit',
                        ],
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            return AccessRightsManager::isRouteAllowed($action->controller);
                        },
                        'roles' => ['@'],
                    ],
                ],
            ],
        ]);
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
    public function actionView(int $id): string
    {
        $this->layout = 'game';

        $quest = FindModelHelper::findQuest(['id' => $id]);
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
     * @return array{error: bool, msg: string, content?: string} JSON response containing tavern state
     *   - error: boolean indicating request status
     *   - msg: string message for client
     *   - content: HTML content for tavern update
     */
    public function actionAjaxPlayer(?int $id): array
    {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $playerId = $id ?? Yii::$app->session->get('playerId');

        $player = FindModelHelper::findPlayer(['id' => $playerId]);
        $render = $this->renderPartial('ajax/player', ['player' => $player]);
        return ['error' => false, 'msg' => '', 'content' => $render];
    }

    /**
     * Ajax POST request to handle player's quitting the game
     *
     * @param string $reason
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxQuit(string $reason): array
    {
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
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxMission(int $missionId): array
    {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $mission = FindModelHelper::findMission(['id' => $missionId]);
        $render = $this->renderPartial('ajax/mission', ['mission' => $mission]);
        return ['error' => false, 'msg' => '', 'content' => $render, 'title' => $mission->name];
    }

    /**
     * Ajax GET request to retreive the eligible actions for a player
     *
     * @param int $questProgressId
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxActions(int $questProgressId): array
    {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $questProgress = FindModelHelper::findQuestProgress(['id' => $questProgressId]);

        if ($questProgress->current_player_id !== Yii::$app->session->get('playerId')) {
            return ['error' => true, 'msg' => 'Not your turn'];
        }

        $remainingActions = $questProgress->remainingActions;

        if ($remainingActions) {
            $render = $this->renderPartial('ajax/actions', ['questActions' => $remainingActions]);
            return ['error' => false, 'msg' => '', 'content' => $render];
        }

        // There are no more actions remaining, this mission is considered complete,
        // we move on to the next default mission.
        $questManager = new QuestManager(['questProgress' => $questProgress]);
        return $questManager->moveToNextDefaultMission();
    }

    /**
     * Ajax GET request to manage the dialog between a player and a NPC
     *
     * @param int $replyId
     * @param int $playerId
     * @param int $storyId
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxDialog(int $replyId, int $playerId, int $storyId): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $reply = FindModelHelper::findReply(['id' => $replyId]);
        $dialog = $reply->nextDialog;

        $player = FindModelHelper::findPlayer(['id' => $playerId]);

        $content = $this->renderPartial('ajax/dialog', [
            'storyId' => $storyId,
            'playerName' => $player->name,
            'reply' => $reply,
            'dialog' => $dialog,
        ]);

        return [
            'error' => false,
            'msg' => '',
            'content' => $content,
            'text' => $dialog->text,
            'audio' => $dialog->audio,
        ];
    }

    /**
     * Retrieves the POST parameters, evaluates the results of the completed action, and triggers a "game-action" event.
     *
     * @param Request $getRequest
     * @return array<string, mixed> An associative array that contains what should be displayed
     */
    protected function getActionResultLog(Request $getRequest): array
    {
        $param = [
            'quest_progress_id' => $getRequest->get('questProgressId'),
            'action_id' => $getRequest->get('actionId'),
        ];
        Yii::debug("*** debug *** - getActionResultLog - param=" . print_r($param, true));
        $questAction = FindModelHelper::findQuestAction($param);
        $outcomeManager = new OutcomeManager(['questAction' => $questAction]);
        /** @var array{payload: array<string, mixed>, log: array<string, mixed>} $actionOutcome */
        $actionOutcome = $outcomeManager->evaluateActionResult();
        $outcomeManager->logQuestAction($actionOutcome['log']);

        /** @var array{
         *   action: \common\models\Action,
         *   status: AppStatus,
         *   outcomes: array<\common\models\Outcome>,
         *   diceRoll: string,
         *   hpLoss: string,
         *   isFree: bool,
         *   canReplay: bool,
         *   questProgressId: int,
         *   missionId: int,
         *   nextMissionId: int|null,
         *   storyId: int,
         *   playerName: string,
         *   language: string
         * } $payload */
        $payload = $actionOutcome['payload'];

        // Update state flow
        $actionManager = new ActionManager(['questAction' => $questAction]);
        $actionManager->endCurrentAction($payload['status'], $payload['canReplay']);
        $actionManager->unlockNextActions($payload['status']);

        $this->createGameActionEvent($questAction->action->name, $payload);
        return $actionOutcome['log'];
    }

    /**
     * Ajax POST request that evaluates the action and get the outcomes of the completed action
     *
     * @return array{error: bool, msg: string, content?: string} Json encoded associative array with error status, internal message, and content to display
     */
    public function actionAjaxGetOutcomes(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $actionLog = $this->getActionResultLog(Yii::$app->request);
        Yii::debug($actionLog);
        $content = $this->renderPartial('ajax/outcomes', $actionLog);
        return [
            'error' => false,
            'msg' => '',
            'content' => $content,
            'questProgressId' => $actionLog['questProgressId'],
        ];
    }

    /**
     * Ajax POST request to handle moving to the next turn
     *
     * @return array{error: bool, msg: string, content?: string} Json encoded associative array with error status, internal message, and content to display
     */
    public function actionAjaxNextTurn(): array
    {
        Yii::$app->response->format = Response::FORMAT_JSON;

        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $request = Yii::$app->request;
        $questProgressId = $request->post('questProgressId');
        $questProgress = FindModelHelper::findQuestProgress(['id' => $questProgressId]);

        // Set the context of the QuestManager to the current QuestProgress
        $questManager = new QuestManager(['questProgress' => $questProgress]);
        $rawNextMissionId = $request->post('nextMissionId');
        $nextMissionId = ($rawNextMissionId !== null && $rawNextMissionId !== '') ? (int) $rawNextMissionId : null;
        $currentMissionId = (int) $request->post('missionId');

        $remainingActions = $questProgress->remainingActions;
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

    /**
     *
     * @param string $actionName
     * @param array<string, mixed> $actionResult
     * @return bool
     * @throws \Exception
     */
    protected function createGameActionEvent(string $actionName, array $actionResult = []): bool
    {
        $sessionId = Yii::$app->session->get('sessionId');
        $player = Yii::$app->session->get('currentPlayer');
        $quest = Yii::$app->session->get('currentQuest');
        try {
            $data['action'] = $actionName;
            $data['detail'] = [
                'diceRoll' => $actionResult['diceRoll'],
                'status' => $actionResult['status'],
                'outcomes' => $actionResult['outcomes'],
                'hpLoss' => $actionResult['hpLoss'],
            ];
            $event = EventFactory::createEvent('game-action', $sessionId, $player, $quest, $data);
            $event->process();
            return true;
        } catch (\Exception $e) {
            Yii::error("Failed to broadcast 'game-action' event: {$e->getMessage()}");
            $errorMessage = 'Error: ' . $e->getMessage() . '<br />Stack Trace:<br />' . nl2br($e->getTraceAsString());
            throw new \Exception($errorMessage);
        }
    }
}
