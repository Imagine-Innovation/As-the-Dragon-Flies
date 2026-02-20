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
use common\components\gameplay\ChatManager;
use common\components\gameplay\QuestManager;
use common\components\gameplay\TavernManager;
use common\components\AccessRightsManager;
use common\helpers\FindModelHelper;
use common\helpers\SaveHelper;
use common\helpers\UserErrorMessage;
use common\models\events\EventFactory;
use common\models\Player;
use common\models\Quest;
use common\models\QuestPlayer;
use common\models\Story;
use frontend\components\AjaxRequest;
use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * QuestController implements the CRUD actions for Quest model.
 * @extends \yii\web\Controller<\yii\base\Module>
 */
class QuestController extends Controller
{

    const DEFAULT_REDIRECT = 'story/index';

    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        /** @phpstan-ignore-next-line */
        return array_merge(parent::behaviors(), [
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
                            'create',
                            'delete',
                            'index',
                            'join',
                            'quit',
                            'resume',
                            'start',
                            'tavern',
                            'update',
                            'view',
                            'ajax-can-start',
                            'ajax-get-messages',
                            'ajax-quest-members',
                            'ajax-send-message',
                            'ajax-welcome-messages',
                        ],
                        'allow' => AccessRightsManager::isRouteAllowed($this),
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
        ]);
    }

    /**
     * Lists all Quest models with access control
     *
     * Administrators see all quests while regular users only see
     * quests they are participating in. Uses ActiveDataProvider for pagination.
     *
     * @return string Rendered index view with quest listing
     */
    public function actionIndex(): string
    {
        $user = Yii::$app->user->identity;

        // Admin users get full quest access
        if ($user->is_admin) {
            Yii::debug('*** Debug *** quest/index is_admin', __METHOD__);
            $dataProvider = new ActiveDataProvider([
                'query' => Quest::find(),
            ]);
        }
        // Regular users only see their quests
        else {
            $subQuery = (new Query())
                    ->select('quest_id')
                    ->from('quest_player')
                    ->where(['player_id' => $user->current_player_id ?? 0]);
            $dataProvider = new ActiveDataProvider([
                'query' => Quest::find()->where(['id' => $subQuery]),
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
    public function actionView(int $id): string
    {
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
     * @param int $storyId
     * @param int|null $playerId
     * @return Response|null
     */
    public function actionJoin(int $storyId, ?int $playerId = null): ?Response
    {
        $story = $this->findValidStory($storyId);

        $tavernManager = new TavernManager(['story' => $story]);
        $tavern = $tavernManager->findTavern();
        $player = FindModelHelper::findPlayer(['id' => $playerId]);

        // Validate player eligibility
        $canJoin = $tavernManager->canPlayerJoinQuest($player);
        if ($canJoin['denied']) {
            UserErrorMessage::throw($this, 'error', $canJoin['reason'], self::DEFAULT_REDIRECT);
            return null;
        }

        // Process player onboarding
        $onboarded = $tavernManager->addPlayerToQuest($player);
        if ($onboarded['error']) {
            UserErrorMessage::throw($this, 'error', $onboarded['message'], self::DEFAULT_REDIRECT);
            return null;
        }

        /** @phpstan-ignore-next-line */
        $success = $this->createEvent('player-joining', $player, $tavern);
        if ($success) {
            return $this->redirect(['tavern', 'id' => $tavern->id]);
        }
        UserErrorMessage::throw($this, 'error', 'Could not trigger event', self::DEFAULT_REDIRECT);
        return null;
    }

    /**
     * Handles AJAX request for tavern updates
     *
     * Retrieves and returns the current tavern state for real-time updates.
     * Used for periodic polling from the frontend.
     *
     * @return array{error: bool, msg: string, content?: string} JSON response containing tavern state
     *   - error: boolean indicating request status
     *   - msg: string message for client
     *   - content: HTML content for tavern update
     */
    public function actionAjaxQuestMembers(?int $questId, ?string $render): array
    {
        // Configure JSON response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        if (!$questId) {
            return ['error' => true, 'msg' => 'Missing Quest ID'];
        }

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
     * @return array{error: bool, msg: string, content?: string} JSON response containing:
     *   - error: boolean indicating operation status
     *   - msg: status message (empty on success)
     *   - content: HTML content with welcome message
     */
    public function actionAjaxWelcomeMessages(?int $questId): array
    {
        // Configure response as JSON format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type and method
        if (!$this->request->isGet || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax GET request'];
        }

        $quest = $this->findModel($questId);

        /**
         * The (int) cast is on purpose.
         * In the Yii2 framework, the count() method of an ActiveQuery doesn't
         * strictly return an int. Under the hood, it queries the database and can return:
         * 1. A numeric string (because many database drivers return counts as
         *    strings to avoid integer overflow on 32-bit systems).
         * 2. null (in some rare edge cases or failed executions).
         * 3. An integer.
         *
         */
        $playerCount = (int) Player::find()->where(['quest_id' => $quest->id])->count();

        $tavernManager = new TavernManager(['quest' => $quest]);
        $welcomeMessage = $tavernManager->welcomeMessage($playerCount);
        $missingPlayers = $tavernManager->missingPlayers($playerCount);
        $missingClasses = $tavernManager->missingClasses();

        // Return success response with welcome message
        return [
            'error' => false,
            'msg' => '',
            'welcomeMessage' => $welcomeMessage,
            'missingPlayers' => $missingPlayers,
            'missingClasses' => $missingClasses,
        ];
    }

    /**
     *
     * @return array{error: bool, msg: string, content?: string}
     */
    public function actionAjaxSendMessage(): array
    {
        Yii::debug('*** Debug *** actionAjaxSendMessage');
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request method and type
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['error' => true, 'msg' => 'Not an Ajax POST request'];
        }

        $quest = $this->findModel(Yii::$app->request->post('questId'));
        $playerId = Yii::$app->request->post('playerId');
        $player = FindModelHelper::findPlayer(['id' => $playerId]);

        $message = Yii::$app->request->post('message');
        Yii::debug(
                "*** Debug *** actionAjaxSendMessage - Player: {$player->name}, Quest: {$quest->name}, Message: "
                . ($message ?? 'empty'),
        );
        if (empty($message)) {
            return ['error' => true, 'msg' => 'Message cannot be empty'];
        }

        $success = $this->createEvent('sending-message', $player, $quest, ['message' => $message]);
        return ['error' => !$success, 'msg' => "Create 'sending-message' event " . ($success
                ? 'succeded' : 'failed')];
    }

    /**
     *
     * @param int|null $id
     * @return Response|null
     */
    public function actionStart(?int $id): ?Response
    {
        $quest = $this->findModel($id);

        $player = $quest->initiator;

        $quest->status = AppStatus::PLAYING->value;
        $quest->current_chapter_id = (int) $quest->story->firstChapter?->id;
        $quest->started_at = time();
        SaveHelper::save($quest);

        $questManager = new QuestManager(['quest' => $quest]);
        $questManager->addFirstQuestProgress();

        $eventCreated = $this->createEvent('quest-starting', $player, $quest);
        if ($eventCreated) {
            return $this->redirect(['game/view', 'id' => $id]);
        }
        UserErrorMessage::throw($this, 'error', 'Could not trigger event', self::DEFAULT_REDIRECT);
        return null;
    }

    /**
     * Retrieves latest messages for real-time chat updates
     *
     * Handles periodic polling for new messages in the tavern chat.
     * Supports message grouping by timestamp.
     *
     * @return array{error: bool, msg: string, content?: string} JSON response with latest messages
     */
    public function actionAjaxGetMessages(): array
    {
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
        $chatManager = new ChatManager(['questId' => $questId, 'playerId' => $playerId]);
        $messages = $chatManager->getLastMessages($roundedTime);

        $content = $this->renderPartial('ajax/messages', ['messages' => $messages]);

        return ['error' => false, 'msg' => '', 'content' => $content];
    }

    /**
     * Check if the quest can start or not
     *
     * @return array{canStart: bool, msg: string}
     */
    public function actionAjaxCanStart(): array
    {
        // Configure response format
        Yii::$app->response->format = Response::FORMAT_JSON;

        // Validate request type
        if (!$this->request->isPost || !$this->request->isAjax) {
            return ['canStart' => false, 'msg' => 'Not an Ajax POST request'];
        }

        $quest = Yii::$app->session->get('currentQuest');
        $playerId = Yii::$app->session->get('playerId');

        $tavernManager = new TavernManager(['quest' => $quest]);
        return $tavernManager->questCanStart($playerId);
    }

    /**
     *
     * @param int $id
     * @return string
     */
    public function actionTavern(int $id): string
    {
        $tavern = $this->findModel($id);

        ContextManager::updateQuestContext($tavern->id);

        $this->layout = 'game';
        return $this->render('tavern', [
                    'model' => $tavern,
        ]);
    }

    /**
     *
     * @param int|null $playerId
     * @param int|null $id
     * @return Response|null
     */
    public function actionQuit(?int $playerId, ?int $id): ?Response
    {
        $player = FindModelHelper::findPlayer(['id' => $playerId]);
        $quest = $this->findModel($id);
        $reason = 'Player decided to quit the quest';
        // Process player offboarding
        $tavernManager = new TavernManager(['quest' => $quest]);
        $withdraw = $tavernManager->withdrawPlayerFromQuest($player, $reason);
        if ($withdraw['error']) {
            Yii::$app->session->setFlash('error', $withdraw['message']);
            return $this->redirect(['quest/view', 'id' => $quest->id]);
        }

        $success = $this->createEvent('player-quitting', $player, $quest, ['reason' => $reason]);
        if ($success) {
            return $this->redirect(['story/index']);
        }
        UserErrorMessage::throw($this, 'error', 'Could not trigger event', self::DEFAULT_REDIRECT);
        return null;
    }

    /**
     * Resumes an existing quest session
     *
     * Validates current player and quest state before allowing
     * player to rejoin their active quest.
     *
     * @param int|null $id
     * @return Response Redirect to tavern view or game view
     */
    public function actionResume(?int $id = null): Response
    {
        $quest = $this->findModel($id);

        if ($quest->status === AppStatus::PLAYING->value) {
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
    public function actionCreate(): string|Response
    {
        $model = new Quest();

        // Handle form submission
        if ($this->request->isPost) {
            $post = (array) $this->request->post();
            if ($model->load($post) && $model->save()) {
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
    public function actionUpdate(int $id): string|Response
    {
        $model = $this->findModel($id);

        // Process form submission
        $post = (array) $this->request->post();
        if ($this->request->isPost && $model->load($post) && $model->save()) {
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
    public function actionDelete(int $id): Response
    {
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
    protected function findModel(?int $id = null): Quest
    {
        $questId = $id ?? Yii::$app->session->get('questId');
        $user = Yii::$app->user->identity;

        if (!$user->is_admin) {
            // For non-administrator users, limit access to quests in which their player participates.
            $quesPlayer = QuestPlayer::findOne(['quest_id' => $questId, 'player_id' => $user->current_player_id ?? 0]);
            if ($quesPlayer === null) {
                throw new NotFoundHttpException("You are not a member of this quest (id={$questId}).");
            }
        }

        $model = Quest::findOne(['id' => $questId]);
        if ($model !== null) {
            return $model;
        }

        throw new NotFoundHttpException("The quest (id={$questId}) you are looking for does not exist.");
    }

    /**
     *
     * @param int $storyId
     * @return Story|null
     * @throws NotFoundHttpException
     */
    protected function findValidStory(int $storyId): ?Story
    {
        if ($storyId) {
            $story = Story::findOne(['id' => $storyId, 'status' => AppStatus::PUBLISHED->value]);
            if (!$story) {
                throw new NotFoundHttpException("The story #{$storyId} you are looking for does not exist.");
            }
            return $story;
        }
        throw new NotFoundHttpException('Missing story ID');
    }

    /**
     *
     * @param string $eventType
     * @param Player $player
     * @param Quest $quest
     * @param array<string, mixed> $data
     * @return bool
     * @throws \Exception
     */
    protected function createEvent(string $eventType, Player &$player, Quest &$quest, array $data = []): bool
    {
        $sessionId = Yii::$app->session->get('sessionId');
        try {
            $event = EventFactory::createEvent($eventType, $sessionId, $player, $quest, $data);
            $event->process();
            return true;
        } catch (\Exception $e) {
            Yii::error("Failed to broadcast '{$eventType}' event: " . $e->getMessage());
            $errorMessage = 'Error: ' . $e->getMessage() . '<br />Stack Trace:<br />' . nl2br($e->getTraceAsString());
            throw new \Exception($errorMessage);
        }
    }
}
