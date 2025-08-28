<?php

namespace common\extensions\EventHandler;

use React\EventLoop\Loop; // Required for Loop::get()
use common\extensions\EventHandler\handlers\RegistrationHandler;
use common\extensions\EventHandler\handlers\ChatMessageHandler;
use common\extensions\EventHandler\handlers\GameActionHandler;
use common\extensions\EventHandler\handlers\PlayerJoiningHandler;
use common\extensions\EventHandler\handlers\PlayerLeavingHandler;
use common\extensions\EventHandler\handlers\QuestCanStartHandler;
use common\extensions\EventHandler\handlers\QuestStartingHandler;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\models\Notification;
use Yii;
use yii\base\Component;

class EventHandler extends Component
{

    public $host = '0.0.0.0';
    public $port = 8082;
    //public $logFilePath = '@runtime/logs/eventhandler.log'; // Default log file path
    public $logFilePath = 'c:/temp/EventHandler.log';
    public $debug = true;
    private ?LoggerService $loggerService = null;
    private ?QuestSessionManager $questSessionManager = null;
    private ?NotificationService $notificationService = null;
    private ?WebSocketServerManager $webSocketServerManager = null;
    private ?BroadcastService $broadcastService = null;
    private ?MessageHandlerOrchestrator $messageHandlerOrchestrator = null;

    public function init() {
        parent::init();

        $actualLogFilePath = Yii::getAlias($this->logFilePath);

        // 1. Initialize LoggerService
        $this->loggerService = new LoggerService($actualLogFilePath, $this->debug);

        // 2. Initialize QuestSessionManager
        $this->questSessionManager = new QuestSessionManager($this->loggerService);

        // 3. Initialize BroadcastService (depends on WebSocketServerManager, QuestSessionManager, NotificationService)
        $messageFactory = new BroadcastMessageFactory();

        // 4. Initialize WebSocketServerManager (Loop can be retrieved globally or passed if needed)
        $loop = Loop::get(); // Get the global loop instance.
        $this->webSocketServerManager = new WebSocketServerManager($this->loggerService, $loop, $this->questSessionManager);

        // 5. Initialize BroadcastService (without NotificationService in constructor)
        $this->broadcastService = new BroadcastService(
                $this->loggerService,
                $this->webSocketServerManager,
                $this->questSessionManager
        );

        // Create Message Factory (already created $messageFactory above, this is just for order context)
        $this->notificationService = new NotificationService(
                $this->loggerService,
                $this->broadcastService,
                $messageFactory
        );

        // Now, inject NotificationService back into BroadcastService using the setter
        $this->broadcastService->setNotificationService($this->notificationService);

        // 6. Initialize Specific Message Handlers
        $specificHandlers = [
            'register' => new RegistrationHandler($this->loggerService, $this->questSessionManager, $this->broadcastService),
            'chat' => new ChatMessageHandler($this->loggerService, $this->notificationService, $this->broadcastService, $messageFactory),
            'action' => new GameActionHandler($this->loggerService, $this->broadcastService, $messageFactory),
            'player_joining' => new PlayerJoiningHandler($this->loggerService, $this->broadcastService, $messageFactory),
            'player_leaving' => new PlayerLeavingHandler($this->loggerService, $this->broadcastService, $messageFactory),
            'quest_can_start' => new QuestCanStartHandler($this->loggerService, $this->broadcastService, $messageFactory),
            'quest_starting' => new QuestStartingHandler($this->loggerService, $this->broadcastService, $messageFactory),
        ];

        // 7. Initialize MessageHandlerOrchestrator
        $this->messageHandlerOrchestrator = new MessageHandlerOrchestrator(
                $this->loggerService,
                $this->broadcastService,
                $specificHandlers // Corrected order: specificHandlers first
        );
    }

    public function run() {
        $originalErrorReportingLevel = error_reporting();
        error_reporting($originalErrorReportingLevel & ~E_DEPRECATED);

        // Existing guard clause for service initialization
        if (!$this->loggerService || !$this->webSocketServerManager || !$this->messageHandlerOrchestrator) {
            if ($this->loggerService) {
                $this->loggerService->log("EventHandler: Essential services not initialized. Cannot run.", null, 'error');
            } else {
                error_log("EventHandler: LoggerService not initialized. Cannot run.");
            }
            error_reporting($originalErrorReportingLevel); // Restore error reporting before returning
            return;
        }

        $this->loggerService->logStart("EventHandler: WebSocket server starting...");
        try {
            $this->loggerService->log("EventHandler: WebSocket server configured to run at {$this->host}:{$this->port}");

            // Pass the orchestrator to the server manager's run method
            $this->webSocketServerManager->run($this->messageHandlerOrchestrator, $this->host, $this->port);

            $this->loggerService->log("EventHandler: WebSocket server has stopped.");
        } catch (\Exception $e) {
            $this->loggerService->log("EventHandler: Exception during server run: " . $e->getMessage(), $e->getTraceAsString(), 'error');
        } finally {
            $this->loggerService->logEnd("EventHandler: WebSocket server run method finished.");
            error_reporting($originalErrorReportingLevel); // Restore original error reporting level
        }
    }

    /**
     * Public entry point to register a session for a quest.
     * Uses the initialized QuestSessionManager.
     *
     * @param string $sessionId
     * @param array $data
     * @return bool
     */
    public function registerSessionForQuest(string $sessionId, array $data): bool {
        if (!$this->questSessionManager) {
            // Fallback or error if init() hasn't run - though for a Yii component, init() should run automatically.
            // This might indicate a usage problem if called before the component is fully initialized.
            $this->loggerService?->log("EventHandler: QuestSessionManager not initialized when calling registerSessionForQuest.", null, 'error');
            // For robustness, could initialize it here if absolutely necessary, but it's better if init() handles it.
            // $this->init(); // Avoid this if possible, could lead to multiple initializations.
            return false;
        }
        // The QuestSessionManager already has its own logger, so it will log its own start/end.
        return $this->questSessionManager->registerSessionForQuest($sessionId, $data);
    }

    public function broadcastToQuest(int $questId, array $message, ?string $excludeSessionId = null): void {
        if (!$this->notificationService) {
            $this->loggerService?->log("EventHandler: notificationService not initialized when calling broadcastToQuest.", null, 'error');
            return;
        }
        // The QuestSessionManager already has its own logger, so it will log its own start/end.
        $this->notificationService->createNotificationAndBroadcast($questId, $message, $excludeSessionId);
    }
}
