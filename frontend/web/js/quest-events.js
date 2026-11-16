class NotificationClient {
    constructor(url, playerId, playerName, avatar, questId, questName, vtt = null) {
        // loading init parameters
        this.url = url;
        this.playerId = playerId;
        this.playerName = playerName;
        this.avatar = avatar;
        this.questId = questId;
        this.questName = questName;
        this.vtt = vtt;

        // init internal variables
        this.stopped = false;
        this.after = 0;
        this.eventListeners = {};
        this.ding = new Audio("music/ding.mp3");
    }

    /**
     * Initialize the notification client and set up event handlers
     */
    init() {
        // Connect to WebSocket server
        this.connect();
        // Set up default event handlers
        this.setupDefaultHandlers();
        // Set up chat input enter key handler
        const chatInput = document.getElementById('questChatInput');

        this.ding.preload = "auto";

        if (chatInput) {
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.sendChatMessage(chatInput);
                }
            });
        }

        // Event delegation for the chat send button
        document.addEventListener('click', (event) => {
            if (event.target) {
                Logger.log(2, 'init', `------> click, event.target.id=${event.target.id}`);
                if (event.target.id === 'sendChatMessageButton' && chatInput) {
                    Logger.log(3, 'init', `Delegated click event for sendChatMessageButton`);
                    this.sendChatMessage(chatInput);
                }
            }
        });

        // Clean up when the page is unloaded
        window.addEventListener('pagehide', () => {
            Logger.log(2, 'init', `------> pagehide event`);
            this.disconnect();
        });

        return this;
    }

    /**
     * Set up default event handlers
     */
    setupDefaultHandlers() {
        Logger.log(1, 'setupDefaultHandlers', '');
        // Handle incoming notifications
        this.on('notification', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received notification:', data);
            const message = data.message ?? null;
            if (this.vtt) {
                this.vtt.refresh(this.questId, message);
            }
        });

        // Handle incoming notifications
        this.on('ack', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received acknowledgement:', data);
        });

        // Handle chat messages
        this.on('new-message', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received new-message message:', data);
            this.updateChatMessages();
        });

        // Handle other player registration
        this.on('register', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received register message:', data);
            this.displayNotification(data);
        });

        this.on('quest-started', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received quest-started message:', data);
            window.location.href = AjaxUtils.buildUrl('game/view') + '&id=' + data.questId;
        });

        this.on('game-action', (data) => this.handleGameAction(data));

        this.on('next-turn', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received next-turn message:', data);
            Logger.log(10, 'setupDefaultHandlers', `Payload: ${JSON.stringify(data, null, 2)}`);

            const detail = this._getEventDetail(data);
            if (!detail || typeof detail !== 'object' || !detail.nextPlayerId || !detail.nextPlayerName) {
                Logger.log(1, 'setupDefaultHandlers', 'Ignoring next-turn message: invalid detail payload');
                return;
            }

            if (this.vtt && typeof this.vtt.refreshTurn === 'function') {
                this.vtt.refreshTurn(this.questId, this.playerId, detail);
            }
        });

        this.on('next-mission', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received next-mission message:', data);
            Logger.log(10, 'setupDefaultHandlers', `Payload: ${JSON.stringify(data, null, 2)}`);

            const detail = this._getEventDetail(data);
            if (!detail || typeof detail !== 'object' || !detail.nextMissionId || !detail.nextPlayerId || !detail.nextPlayerName) {
                Logger.log(1, 'setupDefaultHandlers', 'Ignoring next-mission message: invalid detail payload');
                return;
            }

            if (this.vtt && typeof this.vtt.refreshMission === 'function') {
                this.vtt.refreshMission(this.questId, this.playerId, detail);
            }
        });

        this.on('game-over', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received game-over message:', data);
            Logger.log(10, 'setupDefaultHandlers', `game-over Payload: ${JSON.stringify(data, null, 2)}`);
            const detail = this._getEventDetail(data);
            Logger.log(10, 'setupDefaultHandlers', `game-over event => detail=${JSON.stringify(detail, null, 2)}`);
            if (!detail || typeof detail !== 'object') {
                Logger.log(1, 'setupDefaultHandlers', 'Ignoring game-over message: invalid detail payload');
                return;
            }
            const toastMessage = (this.playerId === detail.playerId) ? detail.toastMessage?.current : detail.toastMessage?.other;

            ToastManager.show('Game Over', toastMessage, 'info');

            const redirectAction = () => {
                // Preserve current origin/base path/front controller and only change the route/query
                const url = new URL(window.location.href);
                url.search = `r=quest/summarize&id=${encodeURIComponent(this.questId)}`;
                window.location.href = url.toString();
            };

            AjaxUtils.request({
                url: 'quest/ajax-end-quest',
                method: 'POST',
                successCallback: (response) => {
                    Logger.log(1, 'game-over', 'Session ID cleared');
                    // Redirect all participants to the summary page after a short delay
                    setTimeout(redirectAction, 3000);
                },
                errorCallback: (error) => {
                    Logger.log(1, 'game-over', 'Failed to clear session ID, redirecting anyway');
                    setTimeout(redirectAction, 3000);
                }
            });
        });

        this.on('player-joined', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received player-joined event:', data);
            if (data.playerName && data.questName) {
                // Construct the message from the payload
                const message = `Player ${data.playerName} has joined quest "${data.questName}".`;
                if (this.vtt) {
                    this.vtt.refresh(this.questId, message);
                }
            } else {
                console.warn('Received player-joined event with incomplete payload:', data);
            }
        });

        this.on('player-quit', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received player-quit event:', data);
            if (data.playerName && data.questName) {
                // Construct the message from the payload
                const message = `Player ${data.playerName} has left the quest`;
                if (this.vtt) {
                    this.vtt.refresh(this.questId, message);
                }
            } else {
                console.warn('Received player-quit event with incomplete payload:', data);
            }
        });
    }

    /**
     * Checks if the player who performed the action is the current player.
     *
     * @param {object} data
     * @returns {boolean}
     */
    _isCurrentPlayer(data) {
        if (!data || !data.playerName) {
            return false;
        }
        return data.playerName === this.playerName || (Boolean(this.vtt) && data.playerName === this.vtt.context?.currentPlayerName);
    }

    /**
     * Checks if at least one outcome has a non-null item_id.
     *
     * @param {Array} outcomes
     * @returns {boolean}
     */
    _hasItemOutcome(outcomes) {
        if (!Array.isArray(outcomes)) {
            return false;
        }
        return outcomes.some(outcome =>
            outcome !== null &&
                    outcome !== undefined &&
                    outcome.item_id !== null &&
                    outcome.item_id !== undefined
        );
    }

    /**
     * Checks if a value is positive (number > 0 or non-empty/non-zero string).
     *
     * @param {number|string|null|undefined} val
     * @returns {boolean}
     */
    _hasPositiveValue(val) {
        if (val === null || val === undefined)
            return false;
        if (typeof val === 'number')
            return val > 0;
        if (typeof val === 'string') {
            const trimmed = val.trim();
            if (trimmed === '' || trimmed === '0')
                return false;
            const num = Number(trimmed);
            if (!isNaN(num))
                return num > 0;
            return true; // Handles dice strings like "1d6"
        }
        return false;
    }

    /**
     * Checks if at least one outcome has a positive gained_xp, gained_gp, or hp_loss_dice.
     *
     * @param {Array} outcomes
     * @returns {boolean}
     */
    _hasStatsOutcome(outcomes) {
        if (!Array.isArray(outcomes)) {
            return false;
        }
        return outcomes.some(outcome =>
            outcome !== null &&
                    outcome !== undefined && (
                            this._hasPositiveValue(outcome.gained_xp) ||
                            this._hasPositiveValue(outcome.gained_gp) ||
                            this._hasPositiveValue(outcome.hp_loss_dice)
                            )
        );
    }

    /**
     * Handles equipment and stats updates for the current player's action.
     *
     * @param {Array} outcomes
     * @returns {void}
     */
    _processCurrentPlayerAction(outcomes) {
        if (this._hasItemOutcome(outcomes)) {
            if (typeof equipmentHandler !== 'undefined' && equipmentHandler && typeof equipmentHandler.refreshEquipment === 'function') {
                equipmentHandler.refreshEquipment(this.playerId);
            }
        }

        if (this._hasStatsOutcome(outcomes)) {
            if (this.vtt && typeof this.vtt._updatePlayer === 'function') {
                this.vtt._updatePlayer(this.playerId);
            }
        }
    }

    /**
     * Handles game-action event and performs necessary UI updates.
     *
     * @param {object} data
     * @returns {void}
     */
    handleGameAction(data) {
        Logger.log(2, 'handleGameAction', 'Received game-action message:', data);
        Logger.log(10, 'handleGameAction', `Payload: ${JSON.stringify(data, null, 2)}`);

        const detail = this._getEventDetail(data);
        const outcomes = (detail && Array.isArray(detail.outcomes)) ? detail.outcomes : [];

        // Always update quest members display so party state is synchronized for all players
        if (this.vtt && typeof this.vtt._updateQuestMembers === 'function') {
            this.vtt._updateQuestMembers(this.questId);
        }

        if (this._isCurrentPlayer(data)) {
            this._processCurrentPlayerAction(outcomes);
        }
    }

    /**
     * Extract normalized event detail from a realtime message.
     *
     * @param {object|null} data
     * @returns {object|null}
     */
    _getEventDetail(data) {
        if (!data || typeof data !== 'object') {
            return null;
        }
        return data.detail && typeof data.detail === 'object' ? data.detail : null;
    }

    /**
     * Connect to the event stream (HTTP long polling)
     *
     * No WebSocket daemon anymore: each request blocks server-side in the
     * controller until a new event is available (or until the server
     * timeout expires), then the client immediately issues the next
     * request. There is no JavaScript polling timer.
     */
    connect() {
        Logger.log(1, 'connect', `----------------------------------------------`);
        Logger.log(1, 'connect', `Player ID: ${this.playerId}, Player name: ${this.playerName}, Avatar: ${this.avatar}, Quest ID: ${this.questId}, Quest name: ${this.questName}`);
        Logger.log(1, 'connect', `----------------------------------------------`);

        this.stopped = false;
        this.firstPoll = true;
        this.pollStep();
    }

    /**
     * Long polling step: issues one blocking request, then reschedules
     * itself until disconnected. The server holds each request until a
     * new event is available, so there is no JavaScript polling timer.
     *
     * @returns {Promise<void>}
     */
    async pollStep() {
        if (this.stopped) {
            return;
        }
        this.abortController = new AbortController();
        try {
            const data = await this.fetchEventPage();
            this.handlePollData(data);
        } catch (error) {
            if (this.stopped) {
                return;
            }
            console.warn('Event stream error:', error);
            // Attempt to reconnect after a delay
            await this.sleep(3000);
        }
        this.pollStep();
    }

    /**
     * Issues one blocking long polling request and returns the decoded
     * response
     *
     * @returns {Promise<object>}
     */
    async fetchEventPage() {
        const response = await fetch(this.buildUrl(), {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': CONFIG.CSRF_TOKEN
            },
            body: new URLSearchParams({
                questId: this.questId,
                playerId: this.playerId,
                after: this.after
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        return response.json();
    }

    /**
     * Handles one successful polling response
     *
     * @param {object} data
     * @returns {void}
     */
    handlePollData(data) {
        if (this.firstPoll) {
            this.firstPoll = false;
            if (this.vtt) {
                this.vtt.refresh(this.questId);
            }
        }

        if (Array.isArray(data.events)) {
            data.events.forEach((event) => {
                this.dispatch(event);
            });
        }
        if (data.after) {
            this.after = parseInt(data.after, 10) || this.after;
        }
    }

    /**
     * Dispatch a received event to the registered handlers, skipping
     * events initiated by this very player (the initiator already handles
     * its own action through the AJAX response)
     *
     * @param {Object} event
     * @returns {void}
     */
    dispatch(event) {
        if (!event || typeof event !== 'object') {
            return;
        }
        if (event.playerId && event.playerId === this.playerId) {
            return;
        }
        this.triggerEvent('message', event);
        if (event.type) {
            this.triggerEvent(event.type, event);
        }
    }

    /**
     * Build the long polling route URL (the event cursor travels in the
     * POST body, not in the URL)
     *
     * @returns {String}
     */
    buildUrl() {
        return `${CONFIG.ROOT_URL}/index.php?r=${this.url.toLowerCase()}`;
    }

    /**
     * Wait for the given delay
     *
     * @param {number} milliseconds
     * @returns {Promise<void>}
     */
    sleep(milliseconds) {
        return new Promise((resolve) => setTimeout(resolve, milliseconds));
    }

    updateChatMessages() {
        Logger.log(1, 'updateChatMessages', ``);

        let target = `#questChatContent`;
        if (!DOMUtils.exists(target))
            return;

        const contextData = {
            playerId: this.playerId,
            questId: this.questId
        };
        Logger.log(10, 'updateChatMessages', `contextData=${JSON.stringify(contextData, null, 2)}`);

        AjaxUtils.request({
            url: 'quest/ajax-get-messages',
            data: contextData,
            method: 'GET',
            successCallback: (response) => {
                if (!response.error) {
                    $(target).html(response.content);
                    // Check if audio can be played (some browsers require user gesture first)
                    this.ding.play().catch((error) => {
                        console.warn("Autoplay blocked:", error);
                        // You could show a visual notification instead
                    });
                }
            }
        });
    }

    /**
     * Register an event handler
     * 
     * @param {string} eventType
     * @param {function} callback
     * @returns {void}
     */
    on(eventType, callback)
    {
        if (!this.eventListeners[eventType]) {
            this.eventListeners[eventType] = [];
        }
        this.eventListeners[eventType].push(callback);
    }

    /**
     * Trigger an event
     * 
     * @param {string} eventType
     * @param {object} data
     * @returns {void}
     */
    triggerEvent(eventType, data) {
        if (this.eventListeners[eventType]) {
            for (const callback of this.eventListeners[eventType]) {
                callback(data);
            }
        }
    }

    /**
     * Disconnect from the server
     */
    disconnect() {
        this.stopped = true;
        if (this.abortController) {
            this.abortController.abort();
        }
    }

    /**
     * Display a notification in the UI
     * 
     * @param {object} notification
     * @returns {void}
     */
    displayNotification(notification) {
        const message = `${notification.type} at ${this.formatTimestamp(notification.timestamp)}<br>
                ${notification.message ? JSON.stringify(notification.message) : 'no message'}        `;

        ToastManager.show('Event Handler', message, 'info');
    }

    /**
     * Send a chat message
     * 
     * @param {HTMLInputElement} chatInput
     * @returns {void}
     */
    sendChatMessage(chatInput) {
        const message = chatInput.value;
        if (!message.trim())
            return;

        AjaxUtils.request({
            url: 'quest/ajax-send-message',
            data: {
                playerId: this.playerId,
                questId: this.questId,
                message: message
            },
            successCallback: (response) => {
                if (!response.error) {
                    chatInput.value = '';
                }
            }
        });
    }

    /**
     * Format a timestamp
     * 
     * @param {number} timestamp
     * @returns {String}
     */
    formatTimestamp(timestamp) {
        const date = new Date(timestamp * 1000);
        return date.toLocaleTimeString();
    }

    /**
     * Updates target element based on response
     * @param {string} target - Target selector
     * @param {Object} response - Server response
     * @param {boolean} isBadge - Whether target is a badge
     */
    _updateTarget(target, response, isBadge) {
        if (isBadge) {
            const $target = $(target);
            response.content > 0
                    ? $target.removeClass('d-none').text(response.content)
                    : $target.addClass('d-none').text('0');
        } else {
            $(target).html(response.content);
        }
    }
}
