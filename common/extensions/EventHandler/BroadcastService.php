<?php

namespace common\extensions\EventHandler;

use Ratchet\ConnectionInterface;
use common\models\QuestSession;
use common\models\Notification; 
// Assuming LoggerService, WebSocketServerManager, QuestSessionManager, NotificationService are properly imported or aliased if not in this namespace.

class BroadcastService {

    private LoggerService $logger;
    private WebSocketServerManager $webSocketServerManager;
    private QuestSessionManager $questSessionManager;
    private NotificationService $notificationService;

    public function __construct(
        LoggerService $logger,
        WebSocketServerManager $webSocketServerManager,
        QuestSessionManager $questSessionManager,
        NotificationService $notificationService
    ) {
        $this->logger = $logger;
        $this->webSocketServerManager = $webSocketServerManager;
        $this->questSessionManager = $questSessionManager;
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

    public function sendToClient(string $clientId, string $jsonData, bool $updateTimestamp = true, ?string $sessionId = null): bool {
        $this->logger->logStart("BroadcastService: sendToClient clientId=[{$clientId}]", ['dataLength' => strlen($jsonData), 'updateTimestamp' => $updateTimestamp, 'sessionId' => $sessionId]);
        $clientConnection = $this->webSocketServerManager->getClient($clientId);

        if (!$clientConnection) {
            $this->logger->log("BroadcastService: Client [{$clientId}] not found for sendToClient.", null, 'warning');
            $this->logger->logEnd("BroadcastService: sendToClient clientId=[{$clientId}]");
            return false;
        }

        $this->sendToConnection($clientConnection, $jsonData);

        if ($updateTimestamp && $sessionId) {
            // Assuming QuestSessionManager has a method to update last_ts for a session ID
            $this->questSessionManager->updateLastTimestamp($sessionId, time());
        }
        $this->logger->logEnd("BroadcastService: sendToClient clientId=[{$clientId}]");
        return true;
    }
    
    public function sendToSession(QuestSession $session, string $jsonData, bool $updateTimestamp = true): bool {
        $this->logger->logStart("BroadcastService: sendToSession sessionId={$session->id}", ['dataLength' => strlen($jsonData), 'updateTimestamp' => $updateTimestamp]);
        
        $clientId = $session->client_id;
        if (!$clientId) {
            $this->logger->log("BroadcastService: QuestSession id=[{$session->id}] has no clientId. Cannot send.", null, 'warning');
            $this->logger->logEnd("BroadcastService: sendToSession sessionId={$session->id}");
            return false;
        }

        $sent = $this->sendToClient($clientId, $jsonData, $updateTimestamp, $session->id);
        // updateLastTimestamp is handled by sendToClient if sessionId is passed
        $this->logger->logEnd("BroadcastService: sendToSession sessionId={$session->id}");
        return $sent;
    }

    public function broadcast(array $data): void {
        $this->logger->logStart("BroadcastService: broadcast", $data);
        $jsonData = json_encode($data);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->log("BroadcastService: JSON encode error in broadcast", $data, 'error');
            return;
        }

        $allClients = $this->webSocketServerManager->getAllClients();
        foreach ($allClients as $client) {
            if ($client instanceof ConnectionInterface) {
                $this->sendToConnection($client, $jsonData);
            }
        }
        $this->logger->log("BroadcastService: Message broadcasted to " . count($allClients) . " clients");
        $this->logger->logEnd("BroadcastService: broadcast");
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

    public function broadcastToQuest(int $questId, array $messageData, ?string $excludeSessionId = null): void {
        $this->logger->logStart("BroadcastService: broadcastToQuest questId={$questId}, excluding=[{$excludeSessionId}]", $messageData);
        
        $jsonData = json_encode($messageData);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->logger->log("BroadcastService: JSON encode error in broadcastToQuest", $messageData, 'error');
            return;
        }

        $otherSessions = $this->findOtherSessions($questId, $excludeSessionId);

        if (empty($otherSessions)) {
            $this->logger->log("BroadcastService: Quest {$questId} has no other connected clients to broadcast to.", null, 'info');
            $this->logger->logEnd("BroadcastService: broadcastToQuest");
            return;
        }
        
        $sentCount = 0;
        foreach ($otherSessions as $session) {
            if ($this->sendToSession($session, $jsonData, true)) { // updateTimestamp can be true here
                $sentCount++;
            }
        }
        $this->logger->log("BroadcastService: Message broadcasted to {$sentCount} sessions in quest {$questId}");
        $this->logger->logEnd("BroadcastService: broadcastToQuest");
    }
    
    public function recoverMessageHistory(string $sessionId): void {
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
            $chatMessageJson = $this->notificationService->prepareChatMessage($notification, $sessionId);
            
            // Send directly to the specific client's connection
            $this->sendToConnection($clientConnection, $chatMessageJson);
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
