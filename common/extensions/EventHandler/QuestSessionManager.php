<?php

namespace common\extensions\EventHandler;

use common\extensions\EventHandler\LoggerService; // Will be injected
use common\helpers\PayloadHelper;
use common\models\QuestSession; // Required for QuestSession model usage

class QuestSessionManager
{

    private LoggerService $logger;

    /**
     *
     * @param LoggerService $logger
     */
    public function __construct(LoggerService $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Register a player for a quest.
     * Original logic from EventHandler::registerSessionForQuest
     *
     * @param string $sessionId
     * @param array<string, mixed> $data Should contain 'questId'
     * @return bool True if registration was successful, false otherwise
     */
    public function registerSessionForQuest(string $sessionId, array $data): bool
    {
        $this->logger->logStart("QuestSessionManager: registerSessionForQuest sessionId=[{$sessionId}]", $data);

        $questId = PayloadHelper::extractIntFromPayload('questId', $data);
        // Validate input parameters
        if ($sessionId !== '' || !$questId) {
            $this->logger->log("QuestSessionManager: Invalid sessionId or quest ID", ['sessionId' => $sessionId, 'questId' => $questId], 'error');
            $this->logger->logEnd("QuestSessionManager: registerSessionForQuest");
            return false;
        }

        try {
            // Assuming $data might also be used by registerSession indirectly or for other logging.
            $playerId = PayloadHelper::extractIntFromPayload('playerId', $data);
            $clientId = PayloadHelper::extractStringFromPayload('clientId', $data);
            $this->registerSession($sessionId, $playerId, $questId, $clientId, $data);
            $this->logger->logEnd("QuestSessionManager: registerSessionForQuest");
            return true;
        } catch (\Exception $e) {
            $this->logger->log("QuestSessionManager: Error registering session [{$sessionId}] for quest {$questId}: " . $e->getMessage(), null, 'error');
            $this->logger->logEnd("QuestSessionManager: registerSessionForQuest");
            return false;
        }
    }

    /**
     * Register a player with a client ID, linking them to a session.
     * Original logic from EventHandler::registerSession
     *
     * @param string|null $sessionId
     * @param int|null $playerId
     * @param int|null $questId
     * @param string|null $clientId
     * @param array<string, mixed>|null $contextData For logging purposes, original $data from message
     * @return bool
     */
    public function registerSession(?string $sessionId, ?int $playerId, ?int $questId, ?string $clientId, array $contextData = null): bool
    {
        $this->logger->logStart("QuestSessionManager: registerSession (sessionId=[{$sessionId}], playerId=[{$playerId}], questId=[{$questId}], clientId=[{$clientId}])", $contextData);

        if ($sessionId === null) {
            $this->logger->log("QuestSessionManager: Missing sessionId", null, 'warning');
            $this->logger->logEnd("QuestSessionManager: registerSession");
            return false;
        }

        // Assuming $playerId, $questId, $clientId are the key fields from $data for session management.
        $session = QuestSession::findOne(['id' => $sessionId]);

        if ($session) {
            $registered = $this->updateSession($session, $questId, $playerId, $clientId);
        } else {
            $registered = $this->newSession($sessionId, $questId, $playerId, $clientId);
        }

        $this->logger->logEnd("QuestSessionManager: registerSession");
        return $registered;
    }

    /**
     * Create a new session.
     * Original logic from EventHandler::newSession
     *
     * @param string $sessionId
     * @param int|null $questId
     * @param int|null $playerId
     * @param string|null $clientId
     * @return bool
     */
    private function newSession(string $sessionId, ?int $questId, ?int $playerId, ?string $clientId): bool
    {
        $this->logger->logStart("QuestSessionManager: newSession sessionId=[{$sessionId}], questId=[{$questId}], playerId=[{$playerId}], clientId=[{$clientId}]");

        $session = new QuestSession([
            'id' => $sessionId,
            'quest_id' => $questId,
            'player_id' => $playerId,
            'client_id' => $clientId,
            'last_ts' => time(), // Consider setting last_ts on creation
        ]);
        $this->logger->log("QuestSessionManager: Attempting to save new QuestSession", $session->getAttributes());

        $successfullySaved = false;
        try {
            $successfullySaved = $session->save();
        } catch (\Exception $e) {
            $this->logger->log("QuestSessionManager: Exception while saving new QuestSession: sessionId=[{$sessionId}]. Error: " . $e->getMessage(), $e->getTraceAsString(), 'error');
        }

        if ($successfullySaved) {
            $this->logger->log("QuestSessionManager: Successfully saved new QuestSession: id=[{$session->id}]");
        } else {
            $this->logger->log("QuestSessionManager: Failed to save new QuestSession: sessionId=[{$sessionId}]. Errors: " . print_r($session->getErrors(), true), null, 'error');
        }
        $this->logQuestSession("QuestSession status after newSession attempt for sessionId=[{$sessionId}]");
        $this->logger->logEnd("QuestSessionManager: newSession");
        return $successfullySaved;
    }

    /**
     *
     * @param QuestSession $session
     * @param int|null $playerId
     * @return bool
     */
    private function updatedSessionPlayerId(QuestSession &$session, ?int $playerId): bool
    {
        if ($playerId !== null && $session->player_id !== $playerId) {
            $this->logger->log("QuestSessionManager: Updating playerId from [{$session->player_id}] to [{$playerId}] for session [{$session->id}]");
            $session->player_id = $playerId;
            $session->last_ts = 0; // Reset timestamp on player change
            return true;
        }
        return false;
    }

    /**
     *
     * @param QuestSession $session
     * @param int|null $questId
     * @return bool
     */
    private function updatedSessionQuestId(QuestSession &$session, ?int $questId): bool
    {
        if ($questId !== null && $session->quest_id !== $questId) {
            $this->logger->log("QuestSessionManager: Updating questId from [{$session->quest_id}] to [{$questId}] for session [{$session->id}]");
            $session->quest_id = $questId;
            $session->last_ts = 0; // Reset timestamp on quest change
            return true;
        }
        return false;
    }

    /**
     *
     * @param QuestSession $session
     * @param string|null $clientId
     * @return bool
     */
    private function updatedSessionClientId(QuestSession &$session, ?string $clientId): bool
    {
        if ($clientId !== null && $session->client_id !== $clientId) {
            $this->logger->log("QuestSessionManager: Updating clientId from [{$session->client_id}] to [{$clientId}] for session [{$session->id}]");
            $session->client_id = $clientId;
            return true;
        }
        return false;
    }

    /**
     * Update an existing session.
     * Original logic from EventHandler::updateSession
     *
     * @param QuestSession $session
     * @param int|null $questId
     * @param int|null $playerId
     * @param string|null $clientId
     * @return bool
     */
    private function updateSession(QuestSession $session, ?int $questId, ?int $playerId, ?string $clientId): bool
    {
        $this->logger->logStart("QuestSessionManager: updateSession session=[{$session->id}], newQuestId=[{$questId}], newPlayerId=[{$playerId}], newClientId=[{$clientId}]");
        $needUpdate = $this->updatedSessionPlayerId($session, $playerId) ||
                $this->updatedSessionQuestId($session, $questId) ||
                $this->updatedSessionClientId($session, $clientId);

        if ($needUpdate === false) {
            $this->logger->log("QuestSessionManager: No updates needed for QuestSession: id=[{$session->id}]");
            return true; // Assume success if no update is needed
        }

        $this->logger->log("QuestSessionManager: Attempting to update QuestSession: id=[{$session->id}]", $session->getDirtyAttributes());

        $successfullySaved = false;
        try {
            $successfullySaved = $session->save();
        } catch (\Exception $e) {
            $this->logger->log("QuestSessionManager: Exception while updating QuestSession: id=[{$session->id}]. Error: " . $e->getMessage(), $e->getTraceAsString(), 'error');
        }

        if ($successfullySaved) {
            $this->logger->log("QuestSessionManager: Successfully updated QuestSession: id=[{$session->id}]");
        } else {
            $this->logger->log("QuestSessionManager: Failed to update QuestSession: id=[{$session->id}]. Errors: " . print_r($session->getErrors(), true), null, 'error');
        }
        $this->logQuestSession("QuestSession status after updateSession attempt for session=[{$session->id}]");
        $this->logger->logEnd('QuestSessionManager: updateSession: ' . ($successfullySaved
                            ? "success" : "failed"));
        return $successfullySaved;
    }

    /**
     * Logs the state of QuestSessions.
     *
     * @param string|null $message A contextual message.
     * @param QuestSession[]|null $sessions An array of QuestSession models. If null, fetches all.
     * @return void
     */
    public function logQuestSession(?string $message = null, ?array $sessions = null): void
    {
        // This method now uses $this->logger
        if (!$this->logger->isDebugEnabled()) {
            return;
        }
        if ($sessions === null) {
            $sessions = QuestSession::find()->all();
        }

        if ($message) {
            $this->logger->log($message);
        }
        foreach ($sessions as $session) {
            /** @var array{id: int, quest_id: int, player_id: int, client_id: string, last_ts: int} */
            $attributes = $session->getAttributes(['id', 'quest_id', 'player_id', 'client_id', 'last_ts']);
            $log = "Session Details: id=[{$attributes['id']}], quest_id=[{$attributes['quest_id']}], player_id=[{$attributes['player_id']}], client_id=[{$attributes['client_id']}], last_ts=[{$attributes['last_ts']}]";
            $this->logger->log($log);
        }
    }

    /**
     * Clears the client_id for any QuestSession associated with the given clientId.
     * This is new method based on logic from EventHandler::removeClient.
     *
     * @param string $clientId The client ID to clear.
     * @return void
     */
    public function clearClientId(string $clientId): void
    {
        $this->logger->logStart("QuestSessionManager: clearClientId for clientId=[{$clientId}]");

        try {
            $rowsUpdated = QuestSession::updateAll(
                    ['client_id' => null],
                    ['client_id' => $clientId]
            );
            $this->logger->log("QuestSessionManager: QuestSession::updateAll result: {$rowsUpdated} row(s) updated to nullify client_id for clientId=[{$clientId}]");

            if ($rowsUpdated === 0) {
                $this->logger->log("QuestSessionManager: No QuestSessions found with clientId=[{$clientId}] to update.", null, 'info');
                // Optionally, try to find if any session still has this client_id (e.g., due to race condition or caching)
                // $staleSession = QuestSession::findOne(['client_id' => $clientId]);
                // if ($staleSession) {
                //     $this->logger->log("QuestSessionManager: Found stale QuestSession with client_id=[{$clientId}]: id=[{$staleSession->id}]", null, 'warning');
                // }
            }
        } catch (\Exception $e) {
            $this->logger->log("QuestSessionManager: Exception during clearClientId for clientId=[{$clientId}]: " . $e->getMessage(), $e->getTraceAsString(), 'error');
        }

        // Log current state of sessions for this client (should be none or cleared)
        // $this->logQuestSession("QuestSessions state after attempting to clear clientId=[{$clientId}]", QuestSession::findAll(['client_id' => $clientId]));
        // Or log all sessions if that's more useful for debugging context
        // $this->logQuestSession("Current QuestSessions after attempting to clear client [{$clientId}]");

        $this->logger->logEnd("QuestSessionManager: clearClientId for clientId=[{$clientId}]");
    }

    /**
     * Updates the last_ts of a QuestSession.
     * @param string $sessionId The ID of the session to update.
     * @param int $timestamp The new timestamp.
     * @return bool True on success, false otherwise.
     */
    public function updateLastTimestamp(string $sessionId, int $timestamp): bool
    {
        $this->logger->logStart("QuestSessionManager: updateLastTimestamp for sessionId=[{$sessionId}] to timestamp=[{$timestamp}]");

        $session = QuestSession::findOne(['id' => $sessionId]);
        if (!$session) {
            $this->logger->log("QuestSessionManager: QuestSession not found for id=[{$sessionId}] during updateLastTimestamp.", null, 'warning');
            $this->logger->logEnd("QuestSessionManager: updateLastTimestamp");
            return false;
        }

        $session->last_ts = $timestamp;

        try {
            $successfullySaved = $session->save();
            if ($successfullySaved) {
                $this->logger->log("QuestSessionManager: Successfully updated last_ts for session [{$sessionId}].");
                $this->logger->logEnd("QuestSessionManager: updateLastTimestamp");
                return true;
            } else {
                $this->logger->log("QuestSessionManager: Failed to save QuestSession after updating last_ts for id=[{$sessionId}]. Errors: " . print_r($session->getErrors(), true), null, 'error');
                $this->logger->logEnd("QuestSessionManager: updateLastTimestamp");
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->log("QuestSessionManager: Exception while saving QuestSession during updateLastTimestamp for id=[{$sessionId}]. Error: " . $e->getMessage(), $e->getTraceAsString(), 'error');
            $this->logger->logEnd("QuestSessionManager: updateLastTimestamp");
            return false;
        }
    }
}
