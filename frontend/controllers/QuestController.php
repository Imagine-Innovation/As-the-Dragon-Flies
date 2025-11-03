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
use common\components\ContextManager;
use common\components\ManageAccessRights;
use common\components\QuestComponent;
use common\components\QuestMessages;
use common\helpers\UserErrorMessage;
use common\models\Chapter;
use common\models\Player;
use common\models\Quest;
use common\models\Story;
use common\models\events\EventFactory;
use frontend\components\AjaxRequest;
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
class QuestController extends Controller
{

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
                                    'index', 'update', 'delete', 'create', 'view', 'quit', 'start',
                                    'tavern', 'join', 'resume', 'get-messages',
                                    'ajax-quest-members', 'ajax-welcome-messages',
                                    'ajax-get-messages', 'ajax-send-message', 'ajax-can-start',
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
                            'start' => ['POST'],
                            'quit' => ['POST'],
                            'join' => ['POST'],
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

    /**     * ***************************************** */
    /**     *            Basic POST requests            */
    /**     * ***************************************** */

    /**
     * Handles tavern entry and player onboarding for a specific story
     *
     * Validates story access, creates or joins quest, and manages player onboarding flow.
     * Core entry point for quest participation.
     *
     * @param int $storyId The ID of the story to join
     * @return string|Response Rendered tavern view or error redirect
     */
    public function actionJoin(int $storyId, ?int $playerId = null) {
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
        $player = $this->findPlayer($playerId);

        // Validate player eligibility
        $canJoin = QuestOnboarding::canPlayerJoinQuest($player, $tavern);
        if ($canJoin['denied']) {
            return UserErrorMessage::throw($this, 'error', $canJoin['reason'], self::DEFAULT_REDIRECT);
        }

        // Process player onboarding
        $onboarded = QuestOnboarding::addPlayerToQuest($player, $tavern);
        if ($onboarded['error']) {
            return UserErrorMessage::throw($this, 'error', $onboarded['message'], self::DEFAULT_REDIRECT);
        }

        $success = $this->createEvent('player-joining', $player, $tavern);
        if ($success) {
            return $this->redirect(['tavern', 'id' => $tavern->id]);
        } else {
            return UserErrorMessage::throw($this, 'error', "Could not trigger event", self::DEFAULT_REDIRECT);
        }
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
    public function actionAjaxQuestMembers() {
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

        $render = Yii::$app->request->get('render');

        // Prepare Ajax request parameters
        $param = [
            'modelName' => 'QuestPlayer',
            'render' => $render ?? 'tavern-members',
            'filter' => ['quest_id' => $questId],
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
    public function actionAjaxWelcomeMessages() {
        // Configure response as JSON format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type and method
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $questId = Yii::$app->request->get('questId');
        $quest = $this->findModel($questId);

        $playerCount = Player::find()
                ->where(['quest_id' => $quest->id])
                ->count();

        $welcomeMessage = QuestOnboarding::welcomeMessage($playerCount);
        $missingPlayers = QuestOnboarding::missingPlayers($quest, $playerCount);
        $missingClasses = QuestOnboarding::missingClasses($quest);

        // Return success response with welcome message
        return ['error' => false, 'msg' => '',
            'welcomeMessage' => $welcomeMessage,
            'missingPlayers' => $missingPlayers,
            'missingClasses' => $missingClasses
        ];
    }

    public function actionAjaxSendMessage() {
        Yii::debug("*** Debug *** actionAjaxSendMessage");
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request method and type
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $quest = $this->findModel(Yii::$app->request->post('questId'));
        if (!$quest) {
            return ['success' => false, 'message' => 'Quest not found'];
        }
        $player = $this->findPlayer(Yii::$app->request->post('playerId'));
        if (!$player) {
            return ['success' => false, 'message' => 'Player not found'];
        }

        $message = Yii::$app->request->post('message');
        Yii::debug("*** Debug *** actionAjaxSendMessage - Player: {$player->name}, Quest: {$quest->name}, Message: " . ($message ?? 'empty'));
        if (empty($message)) {
            return ['success' => false, 'message' => 'Message cannot be empty'];
        }

        $success = $this->createEvent('sending-message', $player, $quest, ['message' => $message]);
        return ['success' => $success, 'msg' => "Create 'sending-message' event {($success ? 'succeded' : 'failed')}"];
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

    public function actionStart(?int $id) {
        $quest = $this->findModel($id);

        $player = $quest->initiator;
        $chapter = $this->findChapterNumber($quest->story_id, 1);

        $quest->status = AppStatus::PLAYING->value;
        $quest->current_chapter_id = $chapter->id;
        $quest->started_at = time();

        if (!$quest->save()) {
            throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($quest->errors, 0, false)));
        }

        $questComponent = new QuestComponent(['quest' => $quest]);
        $questComponent->initQuestProgress();

        $success = $this->createEvent('quest-starting', $player, $quest);
        if ($success) {
            return $this->redirect(['game/view', 'id' => $id]);
        } else {
            return UserErrorMessage::throw($this, 'error', "Could not trigger event", self::DEFAULT_REDIRECT);
        }
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
        $content = $this->renderPartial('ajax/messages', ['messages' => $messages]);

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
        $playerId = Yii::$app->session->get('playerId');
        Yii::debug("*** debug *** - actionAjaxCanStart - questId={$quest->id}, initiatorId={$quest->initiator_id}, playerId={$playerId}");
        if ($playerId !== $quest->initiator_id) {
            return ['canStart' => false, 'msg' => "Your are the quest initiator"];
        }
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

    public function actionTavern($id) {
        $tavern = $this->findModel($id);

        ContextManager::updateQuestContext($tavern->id);

        $this->layout = 'game';
        return $this->render('tavern', [
                    'model' => $tavern
        ]);
    }

    public function actionQuit(?int $playerId, ?int $id) {

        $player = $this->findPlayer($playerId);
        $quest = $this->findModel($id);
        $reason = 'Player decided to quit the quest';
        // Process player offboarding
        $withdraw = QuestOnboarding::withdrawPlayerFromQuest($player, $quest, $reason);
        if ($withdraw['error']) {
            Yii::$app->session->setFlash('error', $withdraw['message']);
            return $this->redirect(['quest/view', 'id' => $quest->id]);
        }

        $success = $this->createEvent('player-quitting', $player, $quest, ['reason' => $reason]);
        if ($success) {
            return $this->redirect(['story/index']);
        } else {
            return UserErrorMessage::throw($this, 'error', "Could not trigger event", self::DEFAULT_REDIRECT);
        }
    }

    /**
     * Resumes an existing quest session
     *
     * Validates current player and quest state before allowing
     * player to rejoin their active quest.
     *
     * @return string|Response Rendered tavern view or error redirect
     */
    public function actionResume(?int $id = null) {
        $quest = $this->findModel($id);

        if ($quest->status == AppStatus::PLAYING->value) {
            return $this->redirect(['game/view', 'id' => $quest->id]);
        }

        return $this->redirect(['tavern', 'id' => $quest->id]);
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
    protected function findModel(?int $id = null): Quest {
        $model = Quest::findOne(['id' => ($id ?? Yii::$app->session->get('questId'))]);

        if ($model) {
            return $model;
        }

        throw new NotFoundHttpException("The quest (id={$id}) you are looking for does not exist.");
    }

    protected function findPlayer(?int $playerId = null): Player {
        $player = Player::findOne(['id' => ($playerId ?? Yii::$app->session->get('playerId'))]);

        if ($player) {
            return $player;
        }

        throw new NotFoundHttpException("The player (playerId={$playerId}) you are looking for does not exist.");
    }

    protected function findValidStory($storyId): ?Story {
        if ($storyId) {
            return Story::findOne(['id' => $storyId, 'status' => AppStatus::PUBLISHED->value]);
        }
        return null;
    }

    protected function findChapterNumber(int $storyId, ?int $chapterNumber = 1): ?Chapter {
        if ($storyId) {
            $chapter = Chapter::findOne(['story_id' => $storyId, 'chapter_number' => $chapterNumber]);
            if ($chapter) {
                return $chapter;
            }

            throw new NotFoundHttpException("The chapter #{$chapterNumber} in story #{$storyId} you are looking for does not exist.");
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
                'current_chapter_id' => QuestOnboarding::getChapterId($story->id, 1),
                'name' => $story->name,
                'description' => $story->description,
                'image' => $story->image,
                'status' => AppStatus::WAITING->value,
                'created_at' => time(),
                'local_time' => time(),
            ]);
            if (!$tavern->save()) {
                throw new \Exception(implode("<br />", \yii\helpers\ArrayHelper::getColumn($tavern->errors, 0, false)));
            }
        }

        return $tavern;
    }

    protected function createEvent(string $eventType, Player &$player, Quest &$quest, array $data = []): bool {
        $sessionId = Yii::$app->session->get('sessionId');
        try {
            $event = EventFactory::createEvent($eventType, $sessionId, $player, $quest, $data);
            $event->process();
            return true;
        } catch (\Exception $e) {
            Yii::error("Failed to broadcast '{$eventType}' event: " . $e->getMessage());
            return false;
        }
    }
}
