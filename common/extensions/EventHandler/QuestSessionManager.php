<?php

namespace common\extensions\EventHandler;

use common\models\QuestSession; // Required for QuestSession model usage

// use common\extensions\EventHandler\LoggerService; // Will be injected

class QuestSessionManager
{

    private LoggerService $logger;

    public function __construct(LoggerService $logger) {
        $this->logger = $logger;
    }

    /**
     * Register a player for a quest.
     * Original logic from EventHandler::registerSessionForQuest
     * @param string $sessionId
     * @param array $data Should contain 'questId'
     * @return bool True if registration was successful, false otherwise
     */
    public function registerSessionForQuest(string $sessionId, array $data): bool {
        $this->logger->logStart("QuestSessionManager: registerSessionForQuest sessionId=[{$sessionId}]", $data);

        $questId = $data['questId'] ?? null;
        // Validate input parameters
        if (!$sessionId || !is_numeric($questId) || $questId <= 0) {
            $this->logger->log("QuestSessionManager: Invalid sessionId or quest ID", ['sessionId' => $sessionId, 'questId' => $questId], 'error');
            $this->logger->logEnd("QuestSessionManager: registerSessionForQuest");
            return false;
        }

        try {
            // The original $data array was passed which might contain more than just questId and playerId.
            // The registerSession method below has been simplified to accept specific parameters.
            // We need to ensure we extract all necessary info from $data if there's more than just playerId/questId.
            // For now, assuming $data might also be used by registerSession indirectly or for other logging.
            $this->registerSession($sessionId, $data['playerId'] ?? null, $questId, $data['clientId'] ?? null, $data);
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
     * @param string|null $sessionId
     * @param int|null $playerId
     * @param int|null $questId
     * @param string|null $clientId
     * @param mixed|null $contextData For logging purposes, original $data from message
     * @return bool
     */
    public function registerSession(?string $sessionId, ?int $playerId, ?int $questId, ?string $clientId, mixed $contextData = null): bool {
        $this->logger->logStart("QuestSessionManager: registerSession (sessionId=[{$sessionId}], playerId=[{$playerId}], questId=[{$questId}], clientId=[{$clientId}])", $contextData);

        if ($sessionId === null) {
            $this->logger->log("QuestSessionManager: Missing sessionId", null, 'warning');
            $this->logger->logEnd("QuestSessionManager: registerSession");
            return false;
        }

        // Note: In original EventHandler, $data was passed to newSession/updateSession.
        // Here, we pass individual parameters. If $data had other relevant fields for these methods,
        // this needs adjustment or those methods need to be updated to accept $contextData.
        // For now, assuming $playerId, $questId, $clientId are the key fields from $data for session management.

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
     * @param string $sessionId
     * @param int|null $questId
     * @param int|null $playerId
     * @param string|null $clientId
     * @return bool
     */
    private function newSession(string $sessionId, ?int $questId, ?int $playerId, ?string $clientId): bool {
        $this->logger->logStart("QuestSessionManager: newSession sessionId=[{$sessionId}], questId=[{$questId}], playerId=[{$playerId}], clientId=[{$clientId}]");

        $session = new QuestSession([
            'id' => $sessionId,
            'quest_id' => $questId,
            'player_id' => $playerId,
            'client_id' => $clientId,
            'last_ts' => time(), // Consider setting last_ts on creation
        ]);
        $this->logger->log("QuestSessionManager: Attempting to save new QuestSession", $session->getAttributes());

        try {
            $saved = $session->save();
            if ($saved) {
                $this->logger->log("QuestSessionManager: Successfully saved new QuestSession: id=[{$session->id}]");
            } else {
                $this->logger->log("QuestSessionManager: Failed to save new QuestSession: sessionId=[{$sessionId}]. Errors: " . print_r($session->getErrors(), true), null, 'error');
            }
        } catch (\Exception $e) {
            $this->logger->log("QuestSessionManager: Exception while saving new QuestSession: sessionId=[{$sessionId}]. Error: " . $e->getMessage(), $e->getTraceAsString(), 'error');
        }

        $this->logQuestSession("QuestSession status after newSession attempt for sessionId=[{$sessionId}]");
        $this->logger->logEnd("QuestSessionManager: newSession");
        return $saved;
    }

    /**
     * Update an existing session.
     * Original logic from EventHandler::updateSession
     * @param QuestSession $session
     * @param int|null $questId
     * @param int|null $playerId
     * @param string|null $clientId
     * @return bool
     */
    private function updateSession(QuestSession $session, ?int $questId, ?int $playerId, ?string $clientId): bool {
        $this->logger->logStart("QuestSessionManager: updateSession session=[{$session->id}], newQuestId=[{$questId}], newPlayerId=[{$playerId}], newClientId=[{$clientId}]");
        $needUpdate = false;

        if ($playerId !== null && $session->player_id != $playerId) {
            $this->logger->log("QuestSessionManager: Updating playerId from [{$session->player_id}] to [{$playerId}] for session [{$session->id}]");
            $session->player_id = $playerId;
            $session->last_ts = 0; // Reset timestamp on player change
            $needUpdate = true;
        }

        if ($questId !== null && $session->quest_id != $questId) {
            $this->logger->log("QuestSessionManager: Updating questId from [{$session->quest_id}] to [{$questId}] for session [{$session->id}]");
            $session->quest_id = $questId;
            $session->last_ts = 0; // Reset timestamp on quest change
            $needUpdate = true;
        }

        if ($clientId !== null && $session->client_id != $clientId) {
            $this->logger->log("QuestSessionManager: Updating clientId from [{$session->client_id}] to [{$clientId}] for session [{$session->id}]");
            $session->client_id = $clientId;
            $needUpdate = true;
            /*
              } elseif ($clientId === null && $session->client_id !== null) { // Explicitly clearing clientId
              $this->logger->log("QuestSessionManager: Clearing clientId from [{$session->client_id}] for session [{$session->id}]");
              $session->client_id = null;
              $needUpdate = true;
             *
             */
        }


        if (!$needUpdate) {
            $this->logger->log("QuestSessionManager: No updates needed for QuestSession: id=[{$session->id}]");
            return true; // Assume success if no update is needed
        }

        $this->logger->log("QuestSessionManager: Attempting to update QuestSession: id=[{$session->id}]", $session->getDirtyAttributes());
        try {
            $updated = $session->save();
            if ($updated) {
                $this->logger->log("QuestSessionManager: Successfully updated QuestSession: id=[{$session->id}]");
            } else {
                $this->logger->log("QuestSessionManager: Failed to update QuestSession: id=[{$session->id}]. Errors: " . print_r($session->getErrors(), true), null, 'error');
            }
        } catch (\Exception $e) {
            $this->logger->log("QuestSessionManager: Exception while updating QuestSession: id=[{$session->id}]. Error: " . $e->getMessage(), $e->getTraceAsString(), 'error');
            $updated = false;
        }

        $this->logQuestSession("QuestSession status after updateSession attempt for session=[{$session->id}]");
        $this->logger->logEnd("QuestSessionManager: updateSession: " . ($updated ? "success" : "failed"));
        return $updated;
    }

    /**
     * Logs the state of QuestSessions.
     * Moved from LoggerService, uses injected LoggerService.
     * @param string|null $message A contextual message.
     * @param array|null $sessions An array of QuestSession models. If null, fetches all.
     */
    public function logQuestSession(string|null $message = null, array|null $sessions = null): void {
        // This method now uses $this->logger
        if (!$this->logger->isDebugEnabled()) { // Assuming LoggerService has a method to check debug status
            return;
        }
        if (!$sessions) {
            $sessions = QuestSession::find()->all();
        }

        if ($message) {
            $this->logger->log($message);
        }
        foreach ($sessions as $session) {
            if (is_object($session) && method_exists($session, 'getAttributes')) {
                $attributes = $session->getAttributes(['id', 'quest_id', 'player_id', 'client_id', 'last_ts']);
                $log = "Session Details: id=[{$attributes['id']}], quest_id=[{$attributes['quest_id']}], player_id=[{$attributes['player_id']}], client_id=[{$attributes['client_id']}], last_ts=[{$attributes['last_ts']}]";
                $this->logger->log($log);
            } else {
                $this->logger->log("Invalid session object provided to logQuestSession.");
            }
        }
    }

    /**
     * Clears the client_id for any QuestSession associated with the given clientId.
     * This is new method based on logic from EventHandler::removeClient.
     * @param string $clientId The client ID to clear.
     */
    public function clearClientId(string $clientId): void {
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
    public function updateLastTimestamp(string $sessionId, int $timestamp): bool {
        $this->logger->logStart("QuestSessionManager: updateLastTimestamp for sessionId=[{$sessionId}] to timestamp=[{$timestamp}]");

        $session = QuestSession::findOne(['id' => $sessionId]);
        if (!$session) {
            $this->logger->log("QuestSessionManager: QuestSession not found for id=[{$sessionId}] during updateLastTimestamp.", null, 'warning');
            $this->logger->logEnd("QuestSessionManager: updateLastTimestamp");
            return false;
        }

        $session->last_ts = $timestamp;

        try {
            if ($session->save()) {
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
