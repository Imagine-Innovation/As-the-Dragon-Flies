class NotificationClient {
    constructor(url, playerId, avatar, questId, playerName, questName, chatInput) {
        Logger.log(1, 'constructor', `url=${url}, playerId=${playerId}, avatar=${avatar}, questId=${questId}, playerName=${playerName}, questName=${questName}, chatInput=${chatInput}`);
        // loading init parameters
        this.url = url;
        this.playerId = playerId;
        this.avatar = avatar;
        this.questId = questId;
        this.playerName = playerName;
        this.questName = questName;
        this.chatInput = chatInput;

        // init internal variables
        this.socket = null;
        this.connected = false;
        this.messageQueue = [];
        this.eventListeners = {};
        this.sessionId = NotificationClient.getSessionId();
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
        const chatInput = document.getElementById(this.chatInput);
        if (chatInput) {
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.sendChatMessage(chatInput);
                }
            });
        }

        // Set up send chat button
        const sendChatBtn = document.getElementById('sendChatMessageButton');
        if (sendChatBtn) {
            sendChatBtn.addEventListener('click', () => {
                if (chatInput) {
                    console.log('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
                    this.sendChatMessage(chatInput);
                }
            });
        }

        const leaveTavernBtn = document.getElementById('leaveTavernButton');
        if (leaveTavernBtn) {
            leaveTavernBtn.addEventListener('click', () => {
                console.log('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
                this.send({
                    type: 'player_leaving',
                    message: `${this.playerName} has decided not to take part in the quest`
                });
            });
        }

        // Set up leave quest button
        const leaveTavernBtnxx = document.getElementById('leaveTavernButton-xxxxxxxxxxxxx');
        if (leaveTavernBtnxx) {
            leaveTavernBtnxx.addEventListener('click', () => {
                console.log('!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!');
                /*
                 Logger.log(2, 'init', `Received leaveTavernBtn click event`);
                 const reason = `${this.playerName} has decided not to take part in the quest`;
                 
                 AjaxUtils.request({
                 url: 'quest/ajax-leave-quest',
                 data: {sessionId: this.sessionId, reason: reason},
                 successCallback: (response) => {
                 Logger.log(2, 'init', `leaveQuest ${JSON.stringify(response)}`);
                 if (response.success) {
                 // this.send({type: 'quest_can_start', payload: response});
                 this.sendWithPayload('player_leaving', reason);
                 }
                 }
                 });
                 */
                this.leaveTavern();
            });
        }

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
            this.displayNotification(data);
        });

        // Handle incoming notifications
        this.on('ack', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received aknowledgement:', data);
            this.displayNotification(data);
        });

        // Handle chat messages
        this.on('chat', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received chat message:', data);
            let config = {
                route: 'quest/ajax-get-messages',
                method: 'GET',
                placeholder: 'questChatContent',
                badge: false
            };
            this.executeRequest(config, data);
        });

        // Handle other player registration
        this.on('register', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received register message:', data);
            this.displayNotification(data);
        });

        this.on('quest_can_start', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received quest can start message:', data);
            this.displayNotification(data);
        });

        this.on('player_joined', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received new_player_joined event:', data);
            if (data.payload && data.payload.playerName && data.payload.questName) {
                // Construct the message from the payload
                const message = `Player ${data.payload.playerName} has joined quest "${data.payload.questName}".`;

                let config = {
                    route: 'quest/ajax-tavern',
                    method: 'GET',
                    placeholder: 'questTavernPlayersContainer',
                    badge: false
                };
                this.executeRequest(config, data);
            } else {
                console.warn('Received new_player_joined event with incomplete payload:', data);
            }
        });

        this.on('player_left', (data) => {
            Logger.log(2, 'setupDefaultHandlers', 'Received new_player_left event:', data);
            if (data.payload && data.payload.playerName && data.payload.questName) {
                // Construct the message from the payload
                const message = `Player ${data.payload.playerName} has left the quest`;

                let config = {
                    route: 'quest/ajax-tavern',
                    method: 'GET',
                    placeholder: 'questTavernPlayersContainer',
                    badge: false
                };
                this.executeRequest(config, data);
            } else {
                console.warn('Received new_player_left event with incomplete payload:', data);
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

            /*
             const joinedAt = new Date();
             const roundedTime = Math.floor(joinedAt / 60) * 60;
             this.send({
             type: 'player_joining',
             payload: {
             playerId: this.playerId,
             playerName: this.playerName,
             avatar: this.avatar,
             questId: this.questId,
             questName: this.questName,
             roundedTime: roundedTime,
             timestamp: joinedAt.toLocaleString(),
             joinedAt: joinedAt.toLocaleString()
             }
             });
             */
            this.sendWithPayload('player_joining', `${this.playerName} is joining the quest`);

            AjaxUtils.request({
                url: 'quest/ajax-can-start',
                data: {sessionId: this.sessionId},
                successCallback: (response) => {
                    Logger.log(2, 'connect', `Can start? ${JSON.stringify(response)}`);
                    if (response.canStart) {
                        this.send({type: 'quest_can_start', payload: response});
                    }
                }
            });

        };

        this.socket.onmessage = (event) => {
            Logger.log(2, 'connect', 'Received message:', event.data);
            try {
                const data = JSON.parse(event.data);
                this.triggerEvent('message', data);
                // Also trigger specific event type if applicable
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

    /**
     * Send a message to the server
     * 
     * @param {string} messageArray
     * @returns {void}
     */
    send(messageArray) {
        Logger.log(1, 'send', `send messageArray=${messageArray}, sessionId=${this.sessionId}, playerId=${this.playerId}, questId=${this.questId}`);
        // Ensure all messages include the session ID, player ID, and quest ID
        const completeMessage = {
            ...messageArray,
            sessionId: this.sessionId,
            playerId: this.playerId,
            questId: this.questId,
            playerName: this.playerName
        };

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

    sendWithPayload(type, textMessage) {
        const joinedAt = new Date();
        const roundedTime = Math.floor(joinedAt / 60) * 60;
        this.send({
            type: type,
            message: textMessage,
            payload: {
                playerId: this.playerId,
                playerName: this.playerName,
                avatar: this.avatar,
                questId: this.questId,
                questName: this.questName,
                roundedTime: roundedTime,
                timestamp: joinedAt.toLocaleString(),
                joinedAt: joinedAt.toLocaleString()
            }
        });
    }

    /**
     * Register an event handler
     * 
     * @param {string} eventType
     * @param {function} callback
     * @returns {undefined}
     */
    on(eventType, callback) {
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
        switch (status) {
            case 'Connected':
                severity = 'info';
                break;
            case 'Error':
                severity = 'error';
                break;
            default:
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

        this.send({
            type: 'chat',
            message: message
        });

        // Clear the input field
        if (chatInput) {
            chatInput.value = '';
        }
    }

    leaveTavern() {
        Logger.log(1, 'leaveTavern', `Received leaveTavernButton click event`);
        const reason = `${this.playerName} has decided not to take part in the quest`;
        AjaxUtils.request({
            url: 'quest/ajax-leave-quest',
            data: {sessionId: this.sessionId, reason: reason},
            successCallback: (response) => {
                Logger.log(2, 'leaveTavern', `leaveTavern ${JSON.stringify(response)}`);
                if (response.success) {
                    // this.send({type: 'quest_can_start', payload: response});
                    this.sendWithPayload('player_leaving', reason);
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

    // Static method to get session ID without needing an instance
    static getSessionId() {
        if (!sessionStorage.getItem("tabId")) {
            const uniqueId = `${Date.now()}-${Math.random().toString(16).slice(2)}`;
            sessionStorage.setItem("tabId", uniqueId);
        }
        const sessionId = sessionStorage.getItem("tabId");
        Logger.log(1, 'getSessionId', 'Using session ID:', sessionId);
        return sessionId;
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

        console.log('Before Ajax');
        AjaxUtils.request({
            url: config.route,
            method: config.method,
            data: fixedData,
            successCallback: (response) => {
                console.log('Callback', response);
                if (!response.error) {
                    this._updateTarget(target, response, config.badge);
                }
            }
        });
        console.log('After Ajax');
        return true;
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
