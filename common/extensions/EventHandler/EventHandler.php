<?php

namespace common\extensions\EventHandler;

use React\EventLoop\Loop;
use React\Http\Server as HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use common\extensions\EventHandler\handlers\RegistrationHandler;
use common\extensions\EventHandler\handlers\SendingMessageHandler;
use common\extensions\EventHandler\handlers\GameActionHandler;
use common\extensions\EventHandler\handlers\PlayerJoiningHandler;
use common\extensions\EventHandler\handlers\PlayerQuittingHandler;
use common\extensions\EventHandler\handlers\QuestStartingHandler;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use Yii;
use yii\base\Component;

class EventHandler extends Component
{

    public string $host = '0.0.0.0';
    public int $port = 8082;
    public int $internalPort = 8083;
    public string $logFilePath = 'c:/temp/EventHandler.log';
    public bool $debug = true;
    private ?LoggerService $loggerService = null;
    private ?QuestSessionManager $questSessionManager = null;
    private ?NotificationService $notificationService = null;
    private ?WebSocketServerManager $webSocketServerManager = null;
    private ?BroadcastService $broadcastService = null;
    private ?MessageHandlerOrchestrator $messageHandlerOrchestrator = null;
    private $loop;

    public function init(): void {
        parent::init();
        $this->loop = Loop::get();
        $this->initializeServices();
        $this->initializeMessageHandlers();
    }

    protected function initializeServices(): void {
        $actualLogFilePath = $this->logFilePath;
        $this->loggerService = new LoggerService($actualLogFilePath, $this->debug);
        $this->questSessionManager = new QuestSessionManager($this->loggerService);
        $messageFactory = new BroadcastMessageFactory();
        $this->webSocketServerManager = new WebSocketServerManager($this->loggerService, $this->loop, $this->questSessionManager);
        $this->broadcastService = new BroadcastService(
                $this->loggerService,
                $this->webSocketServerManager,
                $this->questSessionManager
        );
        $this->notificationService = new NotificationService(
                $this->loggerService,
                $this->broadcastService,
                $messageFactory
        );
        $this->broadcastService->setNotificationService($this->notificationService);
    }

    protected function initializeMessageHandlers(): void {
        $specificHandlers = [
            'register' => new RegistrationHandler($this->loggerService, $this->questSessionManager, $this->broadcastService, new BroadcastMessageFactory()),
            'sending-message' => new SendingMessageHandler($this->loggerService, $this->notificationService, $this->broadcastService, new BroadcastMessageFactory()),
            'action' => new GameActionHandler($this->loggerService, $this->broadcastService, new BroadcastMessageFactory()),
            'player-joining' => new PlayerJoiningHandler($this->loggerService, $this->broadcastService, new BroadcastMessageFactory()),
            'player-quitting' => new PlayerQuittingHandler($this->loggerService, $this->broadcastService, new BroadcastMessageFactory()),
            'quest-starting' => new QuestStartingHandler($this->loggerService, $this->broadcastService, new BroadcastMessageFactory()),
        ];
        $this->messageHandlerOrchestrator = new MessageHandlerOrchestrator(
            $this->loggerService,
            $this->broadcastService,
            $this->questSessionManager,
            $this->notificationService,
            $specificHandlers
        );
    }

    public function run(): void {
        $this->suppressDeprecationWarnings();
        if (!$this->areServicesInitialized()) {
            return;
        }

        $this->setupWebSocketServer();
        $this->setupInternalHttpServer();
        $this->startEventLoop();
    }

    protected function suppressDeprecationWarnings(): void {
        error_reporting(error_reporting() & ~E_DEPRECATED);
    }

    protected function areServicesInitialized(): bool {
        if (!$this->loggerService || !$this->webSocketServerManager || !$this->messageHandlerOrchestrator) {
            $this->loggerService?->log("EventHandler: Essential services not initialized. Cannot run.", null, 'error');
            return false;
        }
        return true;
    }

    protected function setupWebSocketServer(): void {
        $this->webSocketServerManager->setup($this->messageHandlerOrchestrator, $this->host, $this->port);
        $this->loggerService->log("EventHandler: WebSocket server configured to run at {$this->host}:{$this->port}");
    }

    protected function setupInternalHttpServer(): void {
        $http = new HttpServer($this->loop, [$this, 'handleBroadcastRequest']);
        $socket = new SocketServer("{$this->host}:{$this->internalPort}", [], $this->loop);
        $http->listen($socket);
        $this->loggerService->log("EventHandler: Internal HTTP server listening on {$this->host}:{$this->internalPort}");
    }

    public function handleBroadcastRequest(ServerRequestInterface $request): ResponseInterface {
        $this->loggerService->logStart("EventHandler: handleBroadcastRequest - Received request: {$request->getMethod()} {$request->getUri()->getPath()}");

        if (!$this->isValidBroadcastRequest($request)) {
            $response = new Response(404, ['Content-Type' => 'text/plain'], 'Not found');
            $this->loggerService->logEnd("EventHandler: handleBroadcastRequest - Rejected request (not POST or not /broadcast).", null, 'warning');
            return $response;
        }

        $data = $this->parseRequestBody($request);
        if ($data === null) {
            $response = new Response(400, ['Content-Type' => 'text/plain'], 'Invalid JSON');
            $this->loggerService->logEnd("EventHandler: handleBroadcastRequest - Invalid JSON received.", null, 'warning');
            return $response;
        }
        try {
            $response = $this->processBroadcastRequest($data);
            $this->loggerService->logEnd("EventHandler: handleBroadcastRequest - Request processed successfully.");
        } catch (\Throwable $e) {
            $this->loggerService->log("EventHandler: Exception in processBroadcastRequest: " . $e->getMessage(), null, 'error');
            $response = new Response(500, ['Content-Type' => 'text/plain'], 'Internal Server Error');
            $this->loggerService->logEnd("EventHandler: handleBroadcastRequest - Broadcast request failed", null, 'error');
        }
        return $response;
    }

    protected function isValidBroadcastRequest(ServerRequestInterface $request): bool {
        return $request->getMethod() === 'POST' && $request->getUri()->getPath() === '/broadcast';
    }

    protected function parseRequestBody(ServerRequestInterface $request): ?array {
        $body = $request->getBody()->getContents();
        $data = json_decode($body, true);
        return json_last_error() === JSON_ERROR_NONE ? $data : null;
    }

    protected function processBroadcastRequest(array $data): ResponseInterface {
        $questId = $data['questId'] ?? null;
        $message = $data['message'] ?? null;
        $excludeSessionId = $data['excludeSessionId'] ?? null;

        if ($questId && $message) {
            $this->broadcastToQuest($questId, $message, $excludeSessionId);
            return new Response(200, ['Content-Type' => 'application/json'], json_encode(['status' => 'ok', 'message' => 'Broadcast initiated.']));
        }

        return new Response(400, ['Content-Type' => 'text/plain'], 'Missing questId or message');
    }

    protected function startEventLoop(): void {
        $this->loggerService->logStart("EventHandler: Starting event loop...");
        $this->loop->run();
        $this->loggerService->logEnd("EventHandler: Event loop stopped.");
    }

    public function broadcastToQuest(int $questId, array $message, ?string $excludeSessionId = null): void {
        $this->loggerService->logStart("EventHandler: broadcastToQuest questId={$questId}, excludeSessionId={$excludeSessionId}, message:", $message);
        if (!$this->notificationService) {
            $this->loggerService?->logEnd("EventHandler: broadcastToQuest => notificationService not initialized when calling broadcastToQuest.", null, 'error');
            return;
        }

        try {
            $this->notificationService->broadcast($questId, $message, $excludeSessionId);
            $this->loggerService?->logEnd("EventHandler: broadcastToQuest");
        } catch (\Throwable $e) {
            $this->loggerService->logEnd("EventHandler: broadcastToQuest - Exception in broadcastToQuest: " . $e->getMessage(), null, 'error');
        }
    }
}
