<?php

namespace common\extensions\EventHandler;

use Yii; // Assuming Yii is used for path aliasing
use yii\base\Component;
use React\EventLoop\Loop; // Required for Loop::get() if used directly, but likely encapsulated now

// Specific Handlers (assuming they are in the same namespace)
use common\extensions\EventHandler\AttachmentHandler;
use common\extensions\EventHandler\RegistrationHandler;
use common\extensions\EventHandler\ChatMessageHandler;
use common\extensions\EventHandler\GameActionHandler;
use common\extensions\EventHandler\AnnouncePlayerJoinHandler;

class EventHandler extends Component {

    public $host = '0.0.0.0';
    public $port = 8082;
    public $logFilePath = '@runtime/logs/eventhandler.log'; // Default log file path
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

        // 3. Initialize NotificationService
        $this->notificationService = new NotificationService($this->loggerService);
        
        // 4. Initialize WebSocketServerManager (Loop can be retrieved globally or passed if needed)
        // LoopInterface is typically obtained via Loop::get() inside WebSocketServerManager or passed if specific loop instance is required.
        // Let's assume WebSocketServerManager handles its own loop via Loop::get() if not passed.
        // If WebSocketServerManager's constructor needs LoopInterface, it should be `Loop::get()` or passed.
        // For now, assuming LoopInterface is handled by WebSocketServerManager internally or it's okay to pass a new one.
        // The QuestSessionManager is also needed by WebSocketServerManager for the removeClient logic.
        $loop = Loop::get(); // Get the global loop instance.
        $this->webSocketServerManager = new WebSocketServerManager($this->loggerService, $loop, $this->questSessionManager);

        // 5. Initialize BroadcastService
        $this->broadcastService = new BroadcastService(
            $this->loggerService,
            $this->webSocketServerManager,
            $this->questSessionManager,
            $this->notificationService
        );

        // 6. Initialize Specific Message Handlers
        $specificHandlers = [
            'attach' => new AttachmentHandler($this->loggerService, $this->questSessionManager, $this->broadcastService),
            'register' => new RegistrationHandler($this->loggerService, $this->questSessionManager, $this->broadcastService), // Pass BroadcastService
            'chat' => new ChatMessageHandler($this->loggerService, $this->notificationService, $this->broadcastService),
            'action' => new GameActionHandler($this->loggerService, $this->broadcastService),
            'announce_player_join' => new AnnouncePlayerJoinHandler($this->loggerService, $this->broadcastService), // Pass BroadcastService
            // Note: RegistrationHandler and AnnouncePlayerJoinHandler might need NotificationService for recoverMessageHistory,
            // but recoverMessageHistory is now in BroadcastService which has NotificationService.
        ];

        // 7. Initialize MessageHandlerOrchestrator
        $this->messageHandlerOrchestrator = new MessageHandlerOrchestrator(
            $this->loggerService,
            $specificHandlers, // Corrected order: specificHandlers first
            $this->broadcastService
        );
    }

    public function run() {
        if (!$this->loggerService || !$this->webSocketServerManager || !$this->messageHandlerOrchestrator) {
            // Handle error: services not initialized
            if ($this->loggerService) {
                $this->loggerService->log("EventHandler: Essential services not initialized. Cannot run.", null, 'error');
            } else {
                error_log("EventHandler: LoggerService not initialized. Cannot run.");
            }
            return;
        }
        
        // Set error reporting to ignore deprecation warnings, if desired
        // error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);

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
        }
    }

    /**
     * Public entry point to register a session for a quest.
     * Uses the initialized QuestSessionManager.
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

    // The shutdown method, if needed at this level, would typically call:
    // $this->webSocketServerManager->shutdown();
    // However, Ratchet's loop->stop() is usually called from WebSocketServerManager's shutdown.
    // If an explicit overall shutdown is needed for EventHandler, it can be added.
}
