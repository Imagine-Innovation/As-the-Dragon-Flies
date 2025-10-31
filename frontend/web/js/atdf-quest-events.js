class NotificationClient {
    constructor(url, sessionId, playerId, playerName, avatar, questId, questName) {
        // loading init parameters
        this.url = url;
        this.sessionId = sessionId;
        this.playerId = playerId;
        this.playerName = playerName;
        this.avatar = avatar;
        this.questId = questId;
        this.questName = questName;

        // init internal variables
        this.socket = null;
        this.connected = false;
        this.messageQueue = [];
        this.eventListeners = {};
        this.heartbeatInterval = 30000; // 30s
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

        // Event delegation for the "Leave Tavern" button
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
        window.addEventListener('unload', (e) => {
            e.preventDefault();
            this.disconnect();
        });

        return this;
    }

    /**
     * Set up default event handlers
     */
    setupDefaultHandlers() {
        Logger.log(1, 'setupDefaultHandlers', '');
        // Handle connection established
        this.on('open', () => {
            Logger.log(2, 'setupDefaultHandlers', 'Connection to notification server established');
            this.updateConnectionStatus('Connected');
        });

        // Handle connection closed
        this.on('close', () => {
            Logger.log(2, 'setupDefaultHandlers', 'Connection to notification server closed');
            this.updateConnectionStatus('Disconnected - Reconnecting...');
        });

        // Handle errors
        this.on('error', (error) => {
            console.error('Notification error:', error);
            this.updateConnectionStatus('Connection Error');
        });

        // Handle incoming notifications
        this.on('notification', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received notification:', data);
            // this.displayNotification(data);
            const message = data.message ?? null;
            VirtualTableTop.refresh(this.questId, this.sessionId, message);
        });

        // Handle incoming notifications
        this.on('ack', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received aknowledgement:', data);
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
            if (data.payload && data.payload.redirectUrl) {
                window.location.href = data.payload.redirectUrl;
            }
        });

        this.on('game-action', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received game-action message:', data);
            Logger.log(10, 'setupDefaultHandlers', `Payload: ${JSON.stringify(data.payload)}`);
            //const message = data.message ?? null;
            //VirtualTableTop.refresh(this.questId, this.sessionId, message);
            VirtualTableTop.refresh(this.questId, this.sessionId);
            this.updateChatMessages();
        });

        this.on('player-joined', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received player-joined event:', data);
            if (data.payload && data.payload.playerName && data.payload.questName) {
                // Construct the message from the payload
                const message = `Player ${data.payload.playerName} has joined quest "${data.payload.questName}".`;
                //this.refreshTavern(message);
                VirtualTableTop.refresh(this.questId, this.sessionId, message);
            } else {
                console.warn('Received player-joined event with incomplete payload:', data);
            }
        });

        this.on('player-quit', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received player-quit event:', data);
            if (data.payload && data.payload.playerName && data.payload.questName) {
                // Construct the message from the payload
                const message = `Player ${data.payload.playerName} has left the quest`;
                // this.refreshTavern(message);
                VirtualTableTop.refresh(this.questId, this.sessionId, message);
            } else {
                console.warn('Received player-quit event with incomplete payload:', data);
            }
        });
    }

    /**
     * Connect to the WebSocket server
     */
    connect() {
        Logger.log(1, 'connect', `----------------------------------------------`);
        Logger.log(1, 'connect', `Connecting to WebSocket server at ${this.url}`);
        Logger.log(1, 'connect', `Player ID: ${this.playerId}, Player name: ${this.playerName}, Avatar: ${this.avatar}, Quest ID: ${this.questId}, Quest name: ${this.questName}, Session ID: ${this.sessionId}`);
        Logger.log(1, 'connect', `----------------------------------------------`);

        this.socket = new WebSocket(this.url);

        this.socket.onopen = () => {
            Logger.log(2, 'connect', 'WebSocket connection established');
            this.connected = true;
            this.send({type: 'register'});

            // Process any queued messages
            Logger.log(2, 'connect', `Processing ${this.messageQueue.length} queued messages`);
            while (this.messageQueue.length > 0) {
                const message = this.messageQueue.shift();
                Logger.log(2, 'connect', 'Sending queued message:', message);
                this.socket.send(JSON.stringify(message));
            }

            this.triggerEvent('open');
            //this.refreshTavern();
            VirtualTableTop.refresh(this.questId, this.sessionId);
        };

        this.socket.onmessage = (event) => {
            Logger.log(2, 'connect', 'Received message:', event.data);
            try {
                const data = JSON.parse(event.data);
                this.triggerEvent('message', data);
                if (data.type) {
                    this.triggerEvent(data.type, data);
                }
            } catch (error) {
                console.error('Error parsing message:', error);
                console.error('Raw message:', event.data);
            }
        };

        this.socket.onclose = (event) => {
            Logger.log(2, 'connect', `'WebSocket connection closed. Code:${event.code}, Reason: ${event.reason}`);
            this.connected = false;
            this.triggerEvent('close');
            // Attempt to reconnect after a delay
            setTimeout(() => this.connect(), 3000);
        };

        this.socket.onerror = (error) => {
            console.error('WebSocket error:', error);
            this.triggerEvent('error', error);
        };
    }

    refreshTavern(message = null) {
        Logger.log(1, 'refreshTavern', `message=${message}`);

        if (message)
            ToastManager.show('Event Handler', message, 'info');

        this.updateTavernMembers();
        this.updateWelcomeMessages();
    }

    updateTavernMembers() {
        Logger.log(2, 'updateTavernMembers', ``);

        const target = `#tavernPlayersContainer`;
        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: 'quest/ajax-tavern',
            method: 'GET',
            successCallback: (response) => {
                if (!response.error) {
                    $(target).html(response.content);
                    this.checkIfQuestCanStart();
                }
            }
        });

    }

    updateWelcomeMessages() {
        Logger.log(1, 'updateWelcomeMessages', ``);
        let target = `#tavernWelcomeMessage`;
        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: 'quest/ajax-welcome-messages',
            method: 'GET',
            successCallback: (response) => {
                if (!response.error) {
                    target = `#tavernWelcomeMessage`;
                    if (DOMUtils.exists(target))
                        $(target).html(response.welcomeMessage);

                    target = `#tavernMissingPlayers`;
                    if (DOMUtils.exists(target))
                        $(target).html(response.missingPlayers);

                    target = `#tavernMissingClasses`;
                    if (DOMUtils.exists(target))
                        $(target).html(response.missingClasses);
                }
            }
        });

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
        Logger.log(10, 'updateChatMessages', `contextData=${JSON.stringify(contextData)}`);

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

    checkIfQuestCanStart() {
        Logger.log(2, 'checkIfQuestCanStart', ``);

        const button = `#startQuestButton`;
        if (!DOMUtils.exists(button))
            return;

        AjaxUtils.request({
            url: 'quest/ajax-can-start',
            data: {sessionId: this.sessionId},
            successCallback: (response) => {
                Logger.log(3, 'checkIfQuestCanStart', `Can start? ${JSON.stringify(response)}`);
                if (response.canStart) {
                    $(button).removeClass('d-none');
                } else {
                    $(button).addClass('d-none');
                }
            }
        });
    }

    /**
     * Send a message to the server
     * 
     * @param {string} messageArray
     * @returns {void}
     */
    send(messageArray) {
        Logger.log(1, 'send', `send sessionId=${this.sessionId}, playerId=${this.playerId}, questId=${this.questId}`);
        // Ensure all messages include the session ID, player ID, and quest ID
        const completeMessage = {
            ...messageArray,
            sessionId: this.sessionId,
            playerId: this.playerId,
            questId: this.questId,
            playerName: this.playerName
        };
        Logger.log(2, 'send', `completeMessage=${JSON.stringify(completeMessage)}`);

        if (this.connected) {
            Logger.log(2, 'send', 'Sending message:', completeMessage);
            this.socket.send(JSON.stringify(completeMessage));
            //this.socket.send(completeMessage);
        } else {
            Logger.log(2, 'send', 'Connection not ready, queueing message:', completeMessage);
            // Queue the message to be sent when connection is established
            this.messageQueue.push(completeMessage);
        }
    }

    /**
     * Register an event handler
     * 
     * @param {string} eventType
     * @param {function} callback
     * @returns {undefined}
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
     * @returns {undefined}
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
        // Clear heartbeat interval
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
        }
        if (this.socket) {
            this.socket.close();
        }
    }

    /**
     * Update the connection status indicator
     * 
     * @param {string} status
     * @returns {void}
     */
    updateConnectionStatus(status) {
        let severity = '';
        //const statusIcon = $(#eventHandlerStatus);
        const statusIcon = document.getElementById('eventHandlerStatus');
        switch (status) {
            case 'Connected':
                statusIcon.classList.remove('blink');
                statusIcon.style.color = 'var(--success)';

                severity = 'info';
                break;
            case 'Error':
                statusIcon.classList.add('blink');
                statusIcon.style.color = 'var(--error)';

                severity = 'error';
                break;
            default:
                statusIcon.classList.add('blink');
                statusIcon.style.color = 'var(--warning)';

                severity = 'warning';
        }
        ToastManager.show('Connection status', status, severity);
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
     * @param {control} chatInput
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
     * @param {timestamp} timestamp
     * @returns {String}
     */
    formatTimestamp(timestamp) {
        const date = new Date(timestamp * 1000);
        return date.toLocaleTimeString();
    }

    /**
     * Executes AJAX request based on configuration
     * @param {Object} config - Request configuration
     * @param {Object} data - Request data
     */
    executeRequest(config, data) {
        Logger.log(1, 'executeRequest', `config=${JSON.stringify(config)}, data=${JSON.stringify(data)}`);

        const target = `#${config.placeholder}`;
        if (!DOMUtils.exists(target))
            return;

        const fixedData = {
            playerId: this.playerId,
            questId: this.questId
        };

        AjaxUtils.request({
            url: config.route,
            method: config.method,
            data: fixedData,
            successCallback: (response) => {
                if (!response.error) {
                    this._updateTarget(target, response, config.badge);
                }
            }
        });
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
