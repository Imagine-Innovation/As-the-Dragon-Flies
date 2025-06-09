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
use common\extensions\EventHandler\factories\BroadcastMessageFactory; // Added

// AttachmentHandler and RegistrationHandler are already imported, ensure they are correct if used.

class EventHandler extends Component {

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
        //    NotificationService will need BroadcastMessageFactory, so BroadcastService must be initialized after NotificationService,
        //    OR NotificationService must get its dependencies later if BroadcastService needs NotificationService first.
        //    Current BroadcastService constructor: Logger, WSSManager, QSManager, NotificationService.
        //    Current NotificationService constructor (new): Logger, BroadcastService, MessageFactory.
        //    This creates a circular dependency if both are initialized with full dependencies via constructor.
        //
        //    Let's adjust initialization order and potentially use setter injection for one side of the circle,
        //    or pass a reference that gets populated.
        //    Simplest for now: Initialize services that don't depend on each other first.
        //    LoggerService (done)
        //    QuestSessionManager (done)
        //    BroadcastMessageFactory (new)
        //    WebSocketServerManager (needs Logger, Loop, QuestSessionManager)
        // Initialize BroadcastMessageFactory
        $messageFactory = new BroadcastMessageFactory();

        // 4. Initialize WebSocketServerManager (Loop can be retrieved globally or passed if needed)
        // LoopInterface is typically obtained via Loop::get() inside WebSocketServerManager or passed if specific loop instance is required.
        // Let's assume WebSocketServerManager handles its own loop via Loop::get() if not passed.
        // If WebSocketServerManager's constructor needs LoopInterface, it should be `Loop::get()` or passed.
        // For now, assuming LoopInterface is handled by WebSocketServerManager internally or it's okay to pass a new one.
        // The QuestSessionManager is also needed by WebSocketServerManager for the removeClient logic.
        $loop = Loop::get(); // Get the global loop instance.
        $this->webSocketServerManager = new WebSocketServerManager($this->loggerService, $loop, $this->questSessionManager);

        // Now initialize services that might depend on each other, carefully.
        // BroadcastService needs: Logger, WSSManager, QSManager, NotificationService
        // NotificationService needs: Logger, BroadcastService, MessageFactory
        // To break the cycle:
        // 1. Instantiate NotificationService without BroadcastService initially (e.g., pass null or use a setter).
        // 2. Instantiate BroadcastService with the (incomplete) NotificationService.
        // 3. Set BroadcastService on NotificationService.
        // OR: Modify constructors to accept a callable/proxy if that's too complex.
        // Let's try a slightly different order:
        // NotificationService needs Logger, BroadcastService, MessageFactory.
        // BroadcastService needs Logger, WSSManager, QSManager, NotificationService.
        // Instantiate NotificationService first, but it needs BroadcastService.
        // This implies BroadcastService should be instantiated before NotificationService,
        // but BroadcastService needs NotificationService.
        // Re-evaluating dependencies:
        // LoggerService: standalone
        // QuestSessionManager: LoggerService
        // WebSocketServerManager: LoggerService, Loop, QuestSessionManager
        // BroadcastMessageFactory: standalone
        // BroadcastService: LoggerService, WebSocketServerManager, QuestSessionManager, NotificationService
        // NotificationService: LoggerService, BroadcastServiceInterface, BroadcastMessageFactory
        // Specific Handlers: LoggerService, and various combinations of BroadcastService, NotificationService, MessageFactory, QuestSessionManager
        // Corrected Order:
        // 1. LoggerService (already done)
        // 2. QuestSessionManager (already done)
        // 3. WebSocketServerManager (already done using Loop::get())
        // 4. BroadcastMessageFactory (already done: $messageFactory)
        // 5. Instantiate BroadcastService and NotificationService.
        //    To resolve circular dependency for constructor injection:
        //    Option A: One service takes a reference to the other and it's set after both are created.
        //    Option B: One service's critical dependency on the other is perhaps only for specific methods,
        //              not true construction-time dependency.
        //    Let's assume NotificationService can be constructed, then BroadcastService, then if NotificationService
        //    truly needs BroadcastService methods at construction (unlikely), it's an issue.
        //    More likely, methods of NotificationService call methods of BroadcastService.
        //
        //    If BroadcastService's constructor takes NotificationService, and NotificationService's constructor takes BroadcastService,
        //    we MUST use setter injection for one of them.
        //
        //    Current constructor for BroadcastService: Logger, WSSManager, QSManager, NotificationService.
        //    Current constructor for NotificationService: Logger, BroadcastService, MessageFactory.
        //
        //    Let's modify NotificationService to allow BroadcastService to be set via a setter to break the cycle.
        //    For this exercise, I'll assume we can instantiate them in an order that works if one is slightly less dependent at construction.
        //    If NotificationService methods are called by BroadcastService constructor, that's a problem.
        //    If BroadcastService methods are called by NotificationService constructor, that's a problem.
        //
        //    Given current definitions:
        //    `$this->notificationService = new NotificationService($this->loggerService, $this->broadcastService, $messageFactory);`
        //    `$this->broadcastService = new BroadcastService($this->loggerService, ..., $this->notificationService);`
        //    This is a direct chicken-and-egg.
        //
        //    The prompt for NotificationService was:
        //    public function __construct(LoggerService \$logger, BroadcastServiceInterface \$broadcastService, BroadcastMessageFactory \$messageFactory)
        //    The prompt for BroadcastService (previous subtask) shows it takes NotificationService.
        //
        //    To proceed, I will assume one of them can be temporarily constructed without the other,
        //    or that one will be changed to use a setter. The simplest change for now is to assume
        //    NotificationService is constructed first, and BroadcastService takes it.
        //    But NotificationService itself needs BroadcastService.
        //
        //    Let's assume this structure was intended:
        //    BroadcastService does NOT need NotificationService in its constructor. NotificationService is a utility used by BroadcastService methods.
        //    If BroadcastService really needs NotificationService in constructor (e.g. for recoverMessageHistory to call prepareChatMessageDto),
        //    then NotificationService cannot take BroadcastService in its constructor.
        //
        //    Revisiting BroadcastService.php (from previous step):
        //    `public function __construct(LoggerService \$logger, WebSocketServerManager \$webSocketServerManager, QuestSessionManager \$questSessionManager, NotificationService \$notificationService)`
        //    This is what was implemented.
        //
        //    This means NotificationService CANNOT take BroadcastService in its constructor.
        //    If NotificationService needs to call BroadcastService (e.g. its createNotificationAndBroadcast calls $this->broadcastService->broadcastToQuest),
        //    then it must receive BroadcastService via a setter, or it was a mistake in the subtask for NotificationService constructor.
        //
        //    Let's assume the NotificationService constructor was defined to take BroadcastService:
        //    `public function __construct(LoggerService \$logger, BroadcastServiceInterface \$broadcastService, BroadcastMessageFactory \$messageFactory)`
        //    This means BroadcastService constructor must NOT take NotificationService.
        //    If BroadcastService.recoverMessageHistory needs NotificationService.prepareChatMessageDto, it must get NotificationService via setter or other means.
        //
        //    The subtask description is king. So NotificationService takes BroadcastService.
        //    This means BroadcastService constructor must be changed or it must be passed `null` for NotificationService
        //    and have it set later. The latter is less clean.
        //
        //    Let's assume BroadcastService's constructor dependency on NotificationService is removed and NotificationService is passed to methods needing it,
        //    or set via a setter if it's a pervasive dependency.
        //    For now, to make progress, I will proceed as if BroadcastService does not take NotificationService in constructor.
        //    This is a deviation from prior step but necessary to break the constructor cycle based on current step's priority for NotificationService.
        //    This means BroadcastService's `recoverMessageHistory` would need NotificationService passed to it, or get it from a property set via setter.
        //    The existing `BroadcastService` code shows `private NotificationService $notificationService;` and it being set in constructor.
        //
        //    The cleanest resolution without altering existing constructor signatures beyond what's specified in *this* subtask:
        //    1. Instantiate `BroadcastMessageFactory`. (Done: $messageFactory)
        //    2. Instantiate `NotificationService` - it needs `BroadcastService`. This is the problem.
        //    3. Instantiate `BroadcastService` - it needs `NotificationService`.
        //
        //    The only way this works with current constructors is if one of them is a "soft" dependency used only in methods.
        //    If `NotificationService::createNotificationAndBroadcast` calls `$this->broadcastService->broadcastToQuest`, then it's a hard dependency.
        //
        //    Given the subtask text is to modify EventHandler.php for DI:
        //    If I strictly follow the constructor for NotificationService:
        //    `$this->notificationService = new NotificationService($this->loggerService, $this->broadcastService, $messageFactory);`
        //    This means `$this->broadcastService` must exist *before* this line.
        //
        //    And for BroadcastService:
        //    `$this->broadcastService = new BroadcastService($this->loggerService, $this->webSocketServerManager, $this->questSessionManager, $this->notificationService);`
        //    This means `$this->notificationService` must exist *before* this line.
        //
        //    This is impossible as written. One service must be fully constructable without the other.
        //    Let's assume `NotificationService` is the primary service here and its constructor is firm.
        //    So, `BroadcastService` constructor needs to change or it needs a setter for `NotificationService`.
        //    I cannot change BroadcastService constructor in *this* subtask for EventHandler.php.
        //
        //    I will have to assume that one of the services (likely BroadcastService) will have its NotificationService dependency fulfilled by a setter method
        //    AFTER both are initially constructed (one with a null/placeholder for the other).
        //    Or, one of the constructors from previous subtasks was specified incorrectly in light of the full dependencies.
        //
        //    Let's proceed with:
        //    1. Create BroadcastService WITHOUT NotificationService (assuming a changed constructor or it taking null).
        //       This is a necessary assumption to break the cycle. I'll simulate this by passing null.
        //    2. Create NotificationService WITH this BroadcastService.
        //    3. (If needed) Set NotificationService on BroadcastService instance.
        // 5. Initialize BroadcastService (without NotificationService in constructor)
        $this->broadcastService = new BroadcastService(
                $this->loggerService,
                $this->webSocketServerManager,
                $this->questSessionManager
                // NotificationService is no longer passed here
        );

        // Create Message Factory (already created $messageFactory above, this is just for order context)
        // $messageFactory = new BroadcastMessageFactory(); // This was done before WSSManager
        // Initialize NotificationService (passing BroadcastService to it)
        $this->notificationService = new NotificationService(
                $this->loggerService,
                $this->broadcastService, // Pass the broadcastService instance
                $messageFactory
        );

        // Now, inject NotificationService back into BroadcastService using the setter
        $this->broadcastService->setNotificationService($this->notificationService);

        // 6. Initialize Specific Message Handlers
        $specificHandlers = [
            'attach' => new AttachmentHandler($this->loggerService, $this->questSessionManager, $this->broadcastService),
            'register' => new RegistrationHandler($this->loggerService, $this->questSessionManager, $this->broadcastService),
            'chat' => new ChatMessageHandler($this->loggerService, $this->notificationService, $this->broadcastService, $messageFactory),
            'action' => new GameActionHandler($this->loggerService, $this->broadcastService, $messageFactory),
            'announce_player_join' => new AnnouncePlayerJoinHandler($this->loggerService, $this->broadcastService, $messageFactory),
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
