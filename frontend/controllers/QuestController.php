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
use common\models\events\EventFactory;
use common\models\Quest;
use common\models\QuestPlayer;
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
class QuestController extends Controller {

    const DEFAULT_REDIRECT = 'story/index';

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
                                    'join-quest', 'resume', 'get-messages', 'leave-quest',
                                    'ajax-tavern', 'ajax-tavern-counter',
                                    'ajax-new-message', 'ajax-get-messages', 'ajax-send-message',
                                    'ajax-start', 'ajax-can-start',
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
     * Lists all Quest models with access control
     *
     * Administrators see all quests while regular users only see
     * quests they are participating in. Uses ActiveDataProvider for pagination.
     *
     * @return string Rendered index view with quest listing
     */
    public function actionIndex() {
        $user = Yii::$app->user->identity;

        // Admin users get full quest access
        if ($user->is_admin) {
            Yii::debug("*** Debug *** quest/index is_admin", __METHOD__);
            $dataProvider = new ActiveDataProvider([
                'query' => Quest::find()
            ]);
        }
        // Regular users only see their quests
        else {
            $subQuery = (new Query())
                    ->select('quest_id')
                    ->from('quest_player')
                    ->where(['player_id' => $user->current_player_id ?? 0]);
            $dataProvider = new ActiveDataProvider([
                'query' => Quest::find()
                        ->where(['id' => $subQuery])
            ]);
        }

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
        ]);
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

    /**
     * Handles AJAX request for tavern updates
     *
     * Retrieves and returns the current tavern state for real-time updates.
     * Used for periodic polling from the frontend.
     *
     * @return array JSON response containing tavern state
     *   - error: boolean indicating request status
     *   - msg: string message for client
     *   - content: HTML content for tavern update
     */
    public function actionAjaxTavern() {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        // Get current user context
        $user = Yii::$app->user->identity;
        $player = $user->currentPlayer;

        // Prepare Ajax request parameters
        $param = [
            'modelName' => 'Quest',
            'render' => 'ajax-tavern',
            'filter' => ['id' => $player->quest_id],
        ];

        // Process request and return response
        $ajaxRequest = new AjaxRequest($param);
        if ($ajaxRequest->makeResponse(Yii::$app->request)) {
            return $ajaxRequest->response;
        }

        return ['error' => true, 'msg' => 'Error encountered'];
    }

    /**
     * Handles AJAX counter updates for tavern welcome messages
     *
     * Provides real-time welcome message updates for the tavern interface.
     * Used for dynamic content refresh and player engagement tracking.
     *
     * @return array JSON response containing:
     *   - error: boolean indicating operation status
     *   - msg: status message (empty on success)
     *   - content: HTML content with welcome message
     */
    public function actionAjaxTavernCounter() {
        // Configure response as JSON format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type and method
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        // Get current user context and quest information
        $user = Yii::$app->user->identity;
        $player = $user->currentPlayer;
        $questId = $player->quest_id;

        // Generate welcome message for current quest
        $wellcomeMessage = QuestOnboarding::wellcomeMessage($questId);

        // Return success response with welcome message
        return ['error' => false, 'msg' => '', 'content' => $wellcomeMessage];
    }

    /**
     * Processes new chat messages in the tavern
     *
     * Handles message creation, persistence, and notification dispatch.
     * Supports real-time chat functionality.
     *
     * @return array JSON response containing:
     *   - error: boolean indicating operation success
     *   - msg: status message
     *   - content: rendered chat messages HTML
     */
    public function actionAjaxNewMessage() {
        // Set JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request method and type
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        // Extract message data from request
        $request = Yii::$app->request;
        //$ts = $request->post('ts');
        $playerId = $request->post('playerId');
        $questId = $request->post('questId');

        // Dispatch notification
        QuestNotification::push('new-message', $questId, $playerId, 'Sent a message');

        //return ['error' => false, 'msg' => 'New message is saved', 'content' => $content];
        return ['error' => false, 'msg' => 'New message is saved', 'content' => ''];
        //}
        //return ['error' => true, 'msg' => 'Could not create a new message'];
    }

    public function actionAjaxSendMessage() {
        Yii::debug("*** Debug *** actionAjaxSendMessage");
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request method and type
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

        //$sessionId = Yii::$app->request->post('sessionId');
        $message = Yii::$app->request->post('message');
        Yii::debug("*** Debug *** actionAjaxSendMessage - Player/ {$player->name}, Quest: {$quest->story->name}, Message: " . ($message ?? 'empty'));
        if (empty($message)) {
            return ['success' => false, 'message' => 'Message cannot be empty'];
        }

        // Create and process new message event
        // $event = EventFactory::createEvent('new-message', $sessionId, $player, $quest, ['message' => $message]);
        // $event->process();
        // Yii::debug("*** Debug *** actionAjaxSendMessage - Event processing removed. Chat now handled via WebSocket direct message.");

        return ['success' => true, 'msg' => 'Message send attempt acknowledged. Actual processing via WebSocket.'];
    }

    /**
     * Get chat messages for a quest
     */
    public function actionGetMessages($questId = null) {
        Yii::debug("*** Debug *** actionGetMessages");
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request method and type
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

        if ($questId !== $quest->id) {
            Yii::debug("*** Debug *** actionGetMessages - Invalid QuestId");
            return ['success' => false, 'message' => 'Invalid QuestId'];
        }

        $messages = QuestMessages::getRecentChatMessages($questId);

        return [
            'success' => true,
            'messages' => $messages
        ];
    }

    public function actionAjaxStart() {
        // Set JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request method and type
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        // Extract message data from request
        $request = Yii::$app->request;
        $questId = $request->post('questId');
        $quest = $this->findModel($questId);

        if ($quest) {
            $updated = Quest::updateAll(
                    ['status' => AppStatus::PLAYING->value, 'started_at' => time()],
                    ['id' => $questId]
            );
            if ($updated > 0) {
                $render = $this->render('view', ['model' => $quest]);
                return ['error' => false, 'msg' => 'Quest is started', 'content' => $render];
            }
            return ['error' => true, 'msg' => 'Could not start quest'];
        }
        return ['error' => true, 'msg' => 'Quest not found'];
    }

    /**
     * Retrieves latest messages for real-time chat updates
     *
     * Handles periodic polling for new messages in the tavern chat.
     * Supports message grouping by timestamp.
     *
     * @return array JSON response with latest messages
     */
    public function actionAjaxGetMessages() {
        // Configure response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        // Extract request parameters
        $request = Yii::$app->request;
        $playerId = $request->get('playerId');
        $questId = $request->get('questId');
        $roundedTime = $request->get('roundedTime');

        // Fetch and render messages
        $messages = QuestMessages::getLastMessages($questId, $playerId, $roundedTime);
        $content = $this->renderPartial('ajax-messages', ['messages' => $messages]);

        return ['error' => false, 'msg' => '', 'content' => $content];
    }

    /**
     * Check if the quest can start or not
     *
     * @return array JSON response with latest messages
     */
    public function actionAjaxCanStart() {
        // Configure response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['canStart' => false, 'msg' => 'Not an Ajax POST request'];
        }

        // Extract request parameters
        $quest = Yii::$app->session->get('currentQuest');
        $story = $quest->story;

        if ($quest->status !== AppStatus::WAITING->value) {
            return ['canStart' => false, 'msg' => "Quest {$story->name} is not in wating state."];
        }

        $playersCount = $quest->getCurrentPlayers()->count();

        if ($playersCount < $story->min_players) {
            return ['canStart' => false, 'msg' => "Quest can start once {$story->min_players} joined. Current count is {$playersCount}"];
        }

        if (!QuestOnboarding::areRequiredClassesPresent($quest)) {
            return ['canStart' => false, 'msg' => "Missing required player classes"];
        }

        return ['canStart' => true, 'msg' => "Quest can start", 'questName' => $story->name, 'questId' => $quest->id];
    }

    /**
     * Handles tavern entry and player onboarding for a specific story
     *
     * Validates story access, creates or joins quest, and manages player onboarding flow.
     * Core entry point for quest participation.
     *
     * @param int $storyId The ID of the story to join
     * @return string|Response Rendered tavern view or error redirect
     */
    public function actionJoinQuest($storyId) {
        // Validate story existence and accessibility
        $story = $this->findValidStory($storyId);
        if (!$story) {
            return UserErrorMessage::throw($this, 'fatal', 'Invalid story ID (' . ($storyId ?? 'NULL') . ')');
        }

        // Find or create tavern quest instance
        $tavern = $this->findTavern($story);
        if (!$tavern) {
            return UserErrorMessage::throw($this, 'error', 'Unable to find or create a new quest', self::DEFAULT_REDIRECT);
        }

        // Get current player context
        $player = Yii::$app->session->get('currentPlayer');

        // Validate player eligibility
        $canJoin = QuestOnboarding::canPlayerJoinQuest($player, $tavern);
        if ($canJoin['denied']) {
            return UserErrorMessage::throw($this, 'error', $canJoin['reason'], self::DEFAULT_REDIRECT);
        }

        // Process player onboarding
        if (!QuestOnboarding::addPlayerToQuest($player, $tavern)) {
            return UserErrorMessage::throw($this, 'error', 'Unable to onboard player ' . $player->name . ' to the quest', self::DEFAULT_REDIRECT);
        }

        // Set session variables for quest context
        Yii::$app->session->set('currentQuest', $tavern);
        Yii::$app->session->set('questId', $tavern->id);
        Yii::$app->session->set('inQuest', true);

        return $this->render('tavern', ['model' => $tavern]);
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
        if (!QuestOnboarding::withdrawPlayer($player, $quest, $reason)) {
            return ['success' => false, 'message' => "Unable to withdraw player {$player->name} to the quest"];
        }
        Yii::$app->session->set('currentQuest', null);
        Yii::$app->session->set('questId', null);
        Yii::$app->session->set('inQuest', false);
        return ['success' => true, 'message' => "Player {$player->name} successfully withdrown from quest {$quest->story->name}"];
    }

    /**
     * Resumes an existing quest session
     *
     * Validates current player and quest state before allowing
     * player to rejoin their active quest.
     *
     * @return string|Response Rendered tavern view or error redirect
     */
    public function actionResume() {
        $quest = Yii::$app->session->get('currentQuest');

        if ($quest->status == AppStatus::PLAYING->value) {
            return $this->redirect(['quest/view', 'id' => $quest->id]);
        }

        return $this->render('tavern', [
                    'model' => $quest,
        ]);
    }

    /**
     * Creates a new Quest instance
     *
     * Handles form submission and model creation with validation.
     * Redirects to view page on successful creation.
     *
     * @return string|Response Rendered create form or redirect to view
     */
    public function actionCreate() {
        $model = new Quest();

        // Handle form submission
        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
                    'model' => $model,
        ]);
    }

    /**
     * Updates an existing Quest model
     *
     * Handles form submission for quest updates with validation.
     * Maintains data integrity through model validation rules.
     *
     * @param int $id Quest ID to update
     * @return string|Response Rendered update form or redirect to view
     * @throws NotFoundHttpException if quest not found
     */
    public function actionUpdate($id) {
        $model = $this->findModel($id);

        // Process form submission
        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
                    'model' => $model,
        ]);
    }

    /**
     * Deletes a Quest instance
     *
     * Removes quest and related data with proper cleanup.
     * Requires POST request for security.
     *
     * @param int $id Quest ID to delete
     * @return Response Redirect to index page
     * @throws NotFoundHttpException if quest not found
     */
    public function actionDelete($id) {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
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

    protected function findValidStory($storyId) {
        if ($storyId) {
            return Story::findOne(['id' => $storyId, 'status' => AppStatus::PUBLISHED->value]);
        }
        return null;
    }

    protected function findTavern($story) {

        $tavern = $story->tavern ?? null;
        $questId = Yii::$app->session->get('questId');

        if ($questId && (!$tavern || $tavern->id !== $questId)) {
            Yii::debug("*** Debug *** findTavern  ===>  Tavern is not the current quest");
            return null;
        }

        if (!$tavern) {
            Yii::debug("*** Debug *** findTavern  ===>  Create a new Tavern");
            $tavern = new Quest([
                'story_id' => $story->id,
                'initiator_id' => Yii::$app->session->get('playerId'),
                'status' => AppStatus::WAITING->value,
                'created_at' => time(),
                'local_time' => time(),
            ]);
            if (!$tavern->save()) {
                Yii::debug("*** Debug *** findTavern  ===>  Could not save new Quest");
                throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($tavern->errors, 0, false)));
                //return null;
            }
            Yii::debug("*** Debug *** findTavern  ===>  new Quest is saved");
        }
        Yii::debug("*** Debug *** findTavern  ===>  Tavern was found");

        return $tavern;
    }
}
