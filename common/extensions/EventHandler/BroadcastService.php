<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\contracts\BroadcastMessageInterface;
use common\extensions\EventHandler\contracts\BroadcastServiceInterface;
use Ratchet\ConnectionInterface;
use common\models\QuestSession;
use common\models\Notification;
// Assuming LoggerService, WebSocketServerManager, QuestSessionManager, NotificationService are properly imported or aliased if not in this namespace.

class BroadcastService implements BroadcastServiceInterface {

    private LoggerService $logger;
    private WebSocketServerManager $webSocketServerManager;
    private QuestSessionManager $questSessionManager;
    private ?NotificationService $notificationService = null; // Updated property

    public function __construct(
        LoggerService $logger,
        WebSocketServerManager $webSocketServerManager,
        QuestSessionManager $questSessionManager
        // NotificationService $notificationService // Removed from constructor
    ) {
        $this->logger = $logger;
        $this->webSocketServerManager = $webSocketServerManager;
        $this->questSessionManager = $questSessionManager;
        // $this->notificationService = $notificationService; // Removed assignment
    }

    public function setNotificationService(NotificationService $notificationService): void {
        $this->notificationService = $notificationService;
    }

    public function sendToConnection(ConnectionInterface $connection, string $jsonData): void {
        $this->logger->log("BroadcastService: Sending direct to connection", ['remoteAddress' => $connection->remoteAddress, 'dataLength' => strlen($jsonData)]);
        try {
            $connection->send($jsonData);
        } catch (\Exception $e) {
            $this->logger->log("BroadcastService: Exception during sendToConnection: " . $e->getMessage(), ['remoteAddress' => $connection->remoteAddress], 'error');
        }
    }

    // sendBack is part of BroadcastServiceInterface, keep signature as is for now.
    public function sendBack(ConnectionInterface $to, string $type, mixed $message): void {
        $this->logger->logStart("BroadcastService: sendBack type={$type}", ['to' => $to->resourceId]);
        $jsonData = json_encode([
            'type' => $type,
            'message' => $message,
            'timestamp' => time()
        ]);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->log("BroadcastService: JSON encode error in sendBack", ['type' => $type], 'error');
            // Optionally send a generic error if encoding fails
            $this->sendToConnection($to, json_encode(['type' => 'error', 'message' => 'Server error preparing response.']));
            return;
        }
        $this->sendToConnection($to, $jsonData);
        $this->logger->logEnd("BroadcastService: sendBack type={$type}");
    }

    public function sendToClient(string $clientId, BroadcastMessageInterface $message, bool $updateTimestamp = true, ?string $sessionId = null): bool {
        $this->logger->logStart("BroadcastService: sendToClient clientId=[{$clientId}] type=" . $message->getType(), ['updateTimestamp' => $updateTimestamp, 'sessionId' => $sessionId]);
        $clientConnection = $this->webSocketServerManager->getClient($clientId);

        if (!$clientConnection) {
            $this->logger->log("BroadcastService: Client [{$clientId}] not found for sendToClient (type=" . $message->getType() . ").", null, 'warning');
            $this->logger->logEnd("BroadcastService: sendToClient clientId=[{$clientId}] type=" . $message->getType());
            return false;
        }

        $this->sendToConnection($clientConnection, $message->toJson());

        if ($updateTimestamp && $sessionId) {
            // Assuming QuestSessionManager has a method to update last_ts for a session ID
            $this->questSessionManager->updateLastTimestamp($sessionId, time());
        }
        $this->logger->logEnd("BroadcastService: sendToClient clientId=[{$clientId}] type=" . $message->getType());
        return true;
    }
    
    public function sendToSession(QuestSession $session, BroadcastMessageInterface $message, bool $updateTimestamp = true): bool {
        $this->logger->logStart("BroadcastService: sendToSession sessionId={$session->id} type=" . $message->getType(), ['updateTimestamp' => $updateTimestamp]);
        
        $clientId = $session->client_id;
        if (!$clientId) {
            $this->logger->log("BroadcastService: QuestSession id=[{$session->id}] has no clientId. Cannot send (type=" . $message->getType() . ").", null, 'warning');
            $this->logger->logEnd("BroadcastService: sendToSession sessionId={$session->id} type=" . $message->getType());
            return false;
        }

        // Note: sendToClient now expects a BroadcastMessageInterface object.
        $sent = $this->sendToClient($clientId, $message, $updateTimestamp, $session->id);
        // updateLastTimestamp is handled by sendToClient if sessionId is passed
        $this->logger->logEnd("BroadcastService: sendToSession sessionId={$session->id} type=" . $message->getType());
        return $sent;
    }

    public function broadcast(BroadcastMessageInterface $message): void {
        $this->logger->logStart("BroadcastService: broadcast type=" . $message->getType());
        $jsonData = $message->toJson();

        $allClients = $this->webSocketServerManager->getAllClients();
        foreach ($allClients as $client) {
            if ($client instanceof ConnectionInterface) {
                $this->sendToConnection($client, $jsonData);
            }
        }
        $this->logger->log("BroadcastService: Message (type=" . $message->getType() . ") broadcasted to " . count($allClients) . " clients");
        $this->logger->logEnd("BroadcastService: broadcast type=" . $message->getType());
    }
    
    private function findOtherSessions(int $questId, ?string $excludeSessionId = null): array {
        $this->logger->logStart("BroadcastService: findOtherSessions for questId={$questId}, excluding=[{$excludeSessionId}]");
        
        $query = QuestSession::find()
            ->where(['quest_id' => $questId])
            ->andWhere(['is not', 'client_id', null]); // Only sessions with an active client

        if ($excludeSessionId !== null) {
            $query->andWhere(['<>', 'id', $excludeSessionId]);
        }
        
        $sessions = $query->all();
        $this->logger->log("BroadcastService: Found " . count($sessions) . " other sessions for questId={$questId}.");
        // $this->questSessionManager->logQuestSession("Other sessions for quest {$questId}", $sessions); // If detailed logging is needed
        $this->logger->logEnd("BroadcastService: findOtherSessions");
        return $sessions;
    }

    public function broadcastToQuest(int $questId, BroadcastMessageInterface $message, ?string $excludeSessionId = null): void {
        $this->logger->logStart("BroadcastService: broadcastToQuest questId={$questId}, excluding=[{$excludeSessionId}] type=" . $message->getType());
        
        $jsonData = $message->toJson();
        $otherSessions = $this->findOtherSessions($questId, $excludeSessionId);

        if (empty($otherSessions)) {
            $this->logger->log("BroadcastService: Quest {$questId} has no other connected clients to broadcast (type=" . $message->getType() . ").", null, 'info');
            $this->logger->logEnd("BroadcastService: broadcastToQuest type=" . $message->getType());
            return;
        }
        
        $sentCount = 0;
        foreach ($otherSessions as $session) {
            // sendToSession now expects BroadcastMessageInterface
            if ($this->sendToSession($session, $message, true)) { // updateTimestamp can be true here
                $sentCount++;
            }
        }
        $this->logger->log("BroadcastService: Message (type=" . $message->getType() . ") broadcasted to {$sentCount} sessions in quest {$questId}");
        $this->logger->logEnd("BroadcastService: broadcastToQuest type=" . $message->getType());
    }
    
    // recoverMessageHistory is part of BroadcastServiceInterface, keep signature as is.
    // Internal logic uses NotificationService which prepares JSON, so direct DTO usage for sending might not fit cleanly without larger refactor of NotificationService.
    public function recoverMessageHistory(string $sessionId): void {
        if (!$this->notificationService) {
            $this->logger->log("BroadcastService: NotificationService not set. Cannot recover history for session {$sessionId}.", null, 'error');
            throw new \LogicException("NotificationService has not been set in BroadcastService. Cannot recover history.");
            // return; // Alternative: just log and return
        }
        $this->logger->logStart("BroadcastService: recoverMessageHistory for sessionId=[{$sessionId}]");

        $session = QuestSession::findOne(['id' => $sessionId]);
        if (!$session || !$session->client_id) {
            $this->logger->log("BroadcastService: QuestSession not found for sessionId=[{$sessionId}] or no client_id. Cannot recover history.", null, 'warning');
            $this->logger->logEnd("BroadcastService: recoverMessageHistory");
            return;
        }
        
        $clientConnection = $this->webSocketServerManager->getClient($session->client_id);
        if (!$clientConnection) {
            $this->logger->log("BroadcastService: Client connection not found for clientId=[{$session->client_id}] (sessionId=[{$sessionId}]). Cannot send history.", null, 'warning');
            $this->logger->logEnd("BroadcastService: recoverMessageHistory");
            return;
        }

        $this->logger->log("BroadcastService: Found QuestSession", $session->getAttributes());

        // Use NotificationService to get and prepare messages
        $chatNotifications = $this->notificationService->getNotifications($session->quest_id, 'chat', $session->last_ts);
        $notificationCount = count($chatNotifications);
        $this->logger->log("BroadcastService: Processing {$notificationCount} 'chat' notifications for history recovery.");

        $recoveredMessagesCount = 0;
        foreach ($chatNotifications as $notification) {
            $this->logger->log("BroadcastService: Preparing historical chat message: Notification.id=[{$notification->id}]");
            // Use the new DTO method from NotificationService
            $chatMessageDto = $this->notificationService->prepareChatMessageDto($notification, $session->id);
            
            // Send directly to the specific client's connection
            $this->sendToConnection($clientConnection, $chatMessageDto->toJson());
            // No need to call sendToClient or sendToSession here to avoid loops or redundant timestamp updates for history.
            // However, the main timestamp for the session should be updated *after* all history is sent.
            $recoveredMessagesCount++;
        }
        
        // After sending all historical messages, update the session's last_ts to the current time.
        if ($recoveredMessagesCount > 0 || $notificationCount == 0) { // Update even if no messages, to prevent re-fetch of nothing
             $this->questSessionManager->updateLastTimestamp($sessionId, time());
        }

        $this->logger->log("BroadcastService: Finished recovering history for session [{$sessionId}]. Sent {$recoveredMessagesCount} message(s).");
        $this->logger->logEnd("BroadcastService: recoverMessageHistory");
    }
}
