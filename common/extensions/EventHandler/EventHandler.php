<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\handlers\RegistrationHandler;
use common\extensions\EventHandler\handlers\SendingMessageHandler;
use common\extensions\EventHandler\handlers\GameActionHandler;
use common\extensions\EventHandler\handlers\PlayerJoiningHandler;
use common\extensions\EventHandler\handlers\PlayerQuittingHandler;
use common\extensions\EventHandler\handlers\QuestStartingHandler;
use common\extensions\EventHandler\handlers\NextTurnHandler;
use common\extensions\EventHandler\handlers\NextMissionHandler;
use common\extensions\EventHandler\handlers\GameOverHandler;
use common\extensions\EventHandler\factories\BroadcastMessageFactory;
use common\helpers\JsonHelper;
use common\helpers\PayloadHelper;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Http\Server as HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Yii;
use yii\base\Component;

class EventHandler extends Component
{

    public string $host = '0.0.0.0';
    public int $port = 8082;
    public int $internalPort = 8083;
    public string $logFilePath = 'c:/temp/EventHandler.log';
    public bool $debug = true;
    private LoggerService $loggerService;
    private QuestSessionManager $questSessionManager;
    private NotificationService $notificationService;
    private WebSocketServerManager $webSocketServerManager;
    private BroadcastService $broadcastService;
    private MessageHandlerOrchestrator $messageHandlerOrchestrator;
    private LoopInterface $loop;

    /**
     *
     * @return void
     */
    public function init(): void {
        parent::init();

        echo "Init starting\n";
        echo "             host: {$this->host}\n";
        echo "             port: {$this->port}\n";
        echo "    internal port: {$this->internalPort}\n";
        echo "    log file path: {$this->logFilePath}\n";
        echo "       debug mode: " . ($this->debug ? 'true' : 'false') . "\n";
        echo "\n";

        $this->loop = Loop::get();
        $this->initializeServices();
        $this->initializeMessageHandlers();
    }

    /**
     *
     * @return void
     */
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

    /**
     *
     * @return void
     */
    protected function initializeMessageHandlers(): void {
        $specificHandlers = [
            'register' => new RegistrationHandler($this->loggerService, $this->questSessionManager, $this->broadcastService, new BroadcastMessageFactory()),
            'sending-message' => new SendingMessageHandler($this->loggerService, $this->broadcastService, new BroadcastMessageFactory()),
            'game-action' => new GameActionHandler($this->loggerService, $this->broadcastService, new BroadcastMessageFactory()),
            'player-joining' => new PlayerJoiningHandler($this->loggerService, $this->broadcastService, new BroadcastMessageFactory()),
            'player-quitting' => new PlayerQuittingHandler($this->loggerService, $this->broadcastService, new BroadcastMessageFactory()),
            'next-turn' => new NextTurnHandler($this->loggerService, $this->broadcastService, new BroadcastMessageFactory()),
            'next-mission' => new NextMissionHandler($this->loggerService, $this->broadcastService, new BroadcastMessageFactory()),
            'game-over' => new GameOverHandler($this->loggerService, $this->broadcastService, new BroadcastMessageFactory()),
            'quest-starting' => new QuestStartingHandler($this->loggerService, $this->broadcastService, new BroadcastMessageFactory()),
        ];
        $this->messageHandlerOrchestrator = new MessageHandlerOrchestrator(
                $this->loggerService,
                $this->broadcastService,
                $this->notificationService,
                $specificHandlers
        );
    }

    /**
     *
     * @return void
     */
    public function run(): void {
        $this->suppressDeprecationWarnings();
        $this->setupWebSocketServer();
        $this->setupInternalHttpServer();
        $this->startEventLoop();
    }

    /**
     *
     * @return void
     */
    protected function suppressDeprecationWarnings(): void {
        error_reporting(error_reporting() & ~E_DEPRECATED);
    }

    /**
     *
     * @return void
     */
    protected function setupWebSocketServer(): void {
        $this->webSocketServerManager->setup($this->messageHandlerOrchestrator, $this->host, $this->port);
        $this->loggerService->log("EventHandler: WebSocket server configured to run at {$this->host}:{$this->port}");
    }

    /**
     *
     * @return void
     */
    protected function setupInternalHttpServer(): void {
        $http = new HttpServer($this->loop, [$this, 'handleBroadcastRequest']);
        $socket = new SocketServer("{$this->host}:{$this->internalPort}", [], $this->loop);
        $http->listen($socket);
        $this->loggerService->log("EventHandler: Internal HTTP server listening on {$this->host}:{$this->internalPort}");
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    private function handleBroadcastRequest(ServerRequestInterface $request): ResponseInterface {
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

    /**
     *
     * @param ServerRequestInterface $request
     * @return bool
     */
    protected function isValidBroadcastRequest(ServerRequestInterface $request): bool {
        return $request->getMethod() === 'POST' && $request->getUri()->getPath() === '/broadcast';
    }

    /**
     *
     * @param ServerRequestInterface $request
     * @return array<string, mixed>|null
     */
    protected function parseRequestBody(ServerRequestInterface $request): ?array {
        $body = $request->getBody()->getContents();

        $data = JsonHelper::decode($body);

        return json_last_error() === JSON_ERROR_NONE ? $data : null;
    }

    /**
     *
     * @param array<string, mixed> $data
     * @return ResponseInterface
     */
    protected function processBroadcastRequest(array $data): ResponseInterface {
        $questId = PayloadHelper::extractIntFromPayload('questId', $data);
        $message = PayloadHelper::extractArrayFromPayload('message', $data);
        $excludeSessionId = PayloadHelper::extractStringFromPayload('excludeSessionId', $data);

        if ($questId && $message) {
            $this->broadcastToQuest($questId, $message, $excludeSessionId);
            $jsonData = json_encode(['status' => 'ok', 'message' => 'Broadcast initiated.']);
            if ($jsonData) {
                return new Response(200, ['Content-Type' => 'application/json'], $jsonData);
            } else {
                return new Response(400, ['Content-Type' => 'text/plain'], 'Encoding error');
            }
        }

        return new Response(400, ['Content-Type' => 'text/plain'], 'Missing questId or message');
    }

    /**
     *
     * @return void
     */
    protected function startEventLoop(): void {
        $this->loggerService->logStart("EventHandler: Starting event loop...");
        $this->loop->run();
        $this->loggerService->logEnd("EventHandler: Event loop stopped.");
    }

    /**
     *
     * @param int $questId
     * @param array<string, mixed> $message
     * @param string|null $excludeSessionId
     * @return void
     */
    public function broadcastToQuest(int $questId, array $message, ?string $excludeSessionId = null): void {
        $this->loggerService->logStart("EventHandler: broadcastToQuest questId={$questId}, excludeSessionId={$excludeSessionId}, message:", $message);

        try {
            $this->notificationService->broadcast($questId, $message, $excludeSessionId);
            $this->loggerService->logEnd("EventHandler: broadcastToQuest");
        } catch (\Throwable $e) {
            $this->loggerService->logEnd("EventHandler: broadcastToQuest - Exception in broadcastToQuest: " . $e->getMessage(), null, 'error');
        }
    }
}
