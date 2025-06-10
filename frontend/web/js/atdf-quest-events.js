class NotificationClient {
    constructor(url, playerId, questId, playerName, questName) {
        this.url = url;
        this.playerId = playerId;
        this.questId = questId;
        this.playerName = playerName;
        this.questName = questName;
        this.socket = null;
        this.connected = false;
        this.messageQueue = [];
        this.eventListeners = {};
        // Get or create a session ID using sessionStorage
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
        const chatInput = document.getElementById('message-input');
        if (chatInput) {
            chatInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    this.sendChatMessage(chatInput.value);
                }
            });
        }
        // Set up mark all read button
        const markAllReadBtn = document.getElementById('mark-all-read-btn');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => {
                this.markAllNotificationsAsRead();
            });
        }
        // Set up send chat button
        const sendChatBtn = document.getElementById('send-message');
        if (sendChatBtn) {
            sendChatBtn.addEventListener('click', () => {
                const chatInput = document.getElementById('message-input');
                if (chatInput) {
                    this.sendChatMessage(chatInput.value);
                }
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
        // Handle connection established
        this.on('open', () => {
            console.log('Connection to notification server established');
            this.updateConnectionStatus('Connected');
        });

        // Handle connection closed
        this.on('close', () => {
            console.log('Connection to notification server closed');
            this.updateConnectionStatus('Disconnected - Reconnecting...');
        });

        // Handle errors
        this.on('error', (error) => {
            console.error('Notification error:', error);
            this.updateConnectionStatus('Connection Error');
        });

        // Handle incoming notifications
        this.on('notification', (data) => {
            console.log('Received notification:', data);
            this.displayNotification(data);
        });

        // Handle chat messages
        this.on('chat', (data) => {
            console.log('Received chat message:', data);
            this.displayChatMessage(data);
        });

        // Handle other player registration
        this.on('register', (data) => {
            console.log('Received register message:', data);
            this.displayChatMessage(data);
        });

        this.on('player_joined', (data) => {
            console.log('Received new_player_joined event:', data);
            if (data.payload && data.payload.playerName && data.payload.questName) {
                // Construct the message from the payload
                const message = `Player ${data.payload.playerName} has joined quest "${data.payload.questName}".`;

                // Use the existing displayChatMessage method to show it in the UI
                this.displayChatMessage({
                    sender: 'System', // Or any other appropriate sender name
                    message: message,
                    timestamp: data.timestamp || Math.floor(Date.now() / 1000) // Use server timestamp or current time
                });
            } else {
                console.warn('Received new_player_joined event with incomplete payload:', data);
            }
        });
    }

    /**
     * Connect to the WebSocket server
     */
    connect() {
        console.log(`----------------------------------------------`);
        console.log(`Connecting to WebSocket server at ${this.url}`);
        console.log(`Player ID: ${this.playerId}, Player name: ${this.playerName}, Quest ID: ${this.questId}, Quest name: ${this.questName}, Session ID: ${this.sessionId}`);

        this.socket = new WebSocket(this.url);

        this.socket.onopen = () => {
            console.log('WebSocket connection established');
            this.connected = true;
            this.send({type: 'register'});

            // Process any queued messages
            console.log(`Processing ${this.messageQueue.length} queued messages`);
            while (this.messageQueue.length > 0) {
                const message = this.messageQueue.shift();
                console.log('Sending queued message:', message);
                this.socket.send(JSON.stringify(message));
            }
            
            this.triggerEvent('open');

            const joinedAt = new Date();
            this.send({
                type: 'announce_player_join',
                payload: {
                    playerId: this.playerId,
                    playerName: this.playerName,
                    questId: this.questId,
                    questName: this.questName,
                    joinedAt: joinedAt.toLocaleString()
                }
            });
            
            AjaxUtils.request({
                url: 'quest/ajax-can-start',
                data: {sessionId: this.sessionId},
                successCallback: (response) => {
                    if (response && response.error === false && response.data) {
                        console.log('AJAX success for new player event, sending announce_player_join WebSocket message:', response.data);
                        this.send({
                            type: 'announce_player_join',
                            payload: response.data
                        });
                    } else {
                        console.error('Failed to get valid data from ajax-trigger-new-player-event:', response);
                        // Optionally, send the old chat message here as a fallback or error indicator,
                        // or handle the error more robustly. For now, just log it.
                        // this.send({
                        //     type: 'chat',
                        //     message: JSON.stringify("Error processing new player announcement: " + (response ? response.msg : "Unknown error"))
                        // });
                    }
                }
            });

        };

        this.socket.onmessage = (event) => {
            console.log('Received message:', event.data);
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
            console.log('WebSocket connection closed. Code:', event.code, 'Reason:', event.reason);
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
     * @param {string} message
     * @returns {void}
     */
    send(message) {
        // Ensure all messages include the session ID, player ID, and quest ID
        const completeMessage = {
            ...message,
            sessionId: this.sessionId,
            playerId: this.playerId,
            questId: this.questId
        };

        if (this.connected) {
            console.log('Sending message:', completeMessage);
            this.socket.send(JSON.stringify(completeMessage));
            //this.socket.send(completeMessage);
        } else {
            console.log('Connection not ready, queueing message:', completeMessage);
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
        const statusElement = document.getElementById('chat-messages');
        if (statusElement) {
            statusElement.textContent = status;
            statusElement.className = 'status-' + status.toLowerCase().replace(/\s+/g, '-');
        }
    }

    /**
     * Display a notification in the UI
     * 
     * @param {object} notification
     * @returns {void}
     */
    displayNotification(notification) {
        const container = document.getElementById('notifications-container');
        if (!container)
            return;

        const notificationElement = document.createElement('div');
        notificationElement.className = 'notification';
        notificationElement.dataset.id = notification.id;

        // Add 'read' class if the notification is already read
        if (notification.is_read) {
            notificationElement.classList.add('read');
        }

        notificationElement.innerHTML = `
            <div class="notification-header">
                <p>Notification</p>
                <span class="notification-type">Type: ${notification.type}</span>
                <span class="notification-time">Timestamp: ${notification.timestamp}</span>
            </div>
            <div class="notification-content">Content: ${notification.message}</div>
            <div class="notification-actions">
                <button class="mark-read-btn" ${notification.is_read ? 'disabled' : ''}>
                    ${notification.is_read ? 'Read' : 'Mark as Read'}
                </button>
            </div>
        `;

        // Add click handler for the mark as read button
        const markReadBtn = notificationElement.querySelector('.mark-read-btn');
        if (markReadBtn && !notification.is_read) {
            markReadBtn.addEventListener('click', () => {
                this.markNotificationAsRead(notification.id);
            });
        }

        // Add to the container
        container.prepend(notificationElement);
    }

    /**
     * Display notification history
     * 
     * @param {object} notifications
     * @returns {void}
     */
    displayNotificationHistory(notifications) {
        const container = document.getElementById('notifications-container');
        if (!container)
            return;

        // Clear existing notifications
        container.innerHTML = '';

        // Display each notification
        notifications.forEach(notification => {
            this.displayNotification(notification);
        });
    }

    /**
     * Display a chat message
     * 
     * @param {object} chatData
     * @returns {void}
     */
    displayChatMessage(chatData) {
        const container = document.getElementById('chat-messages');
        if (!container)
            return;

        const chatElement = document.createElement('div');
        chatElement.className = 'chat-message';
        chatElement.innerHTML = `
            <div class="chat-header">
                <p>Message</p>
                <span class="chat-sender">Sender: ${chatData.sender}</span>
                <span class="chat-time">Timestamp: ${chatData.timestamp}</span>
            </div>
            <div class="chat-content">Message: ${chatData.message}</div>
        `;

        // Add to the container
        container.appendChild(chatElement);
        // Scroll to bottom
        container.scrollTop = container.scrollHeight;
    }

    /**
     * Mark a notification as read
     * 
     * @param {int} notificationId
     * @returns {void}
     */
    markNotificationAsRead(notificationId) {
        this.send({
            type: 'notification_update',
            action: 'read',
            notificationId: notificationId
        });

        // Update UI
        const notificationElement = document.querySelector(`.notification[data-id="${notificationId}"]`);
        if (notificationElement) {
            notificationElement.classList.add('read');
            const markReadBtn = notificationElement.querySelector('.mark-read-btn');
            if (markReadBtn) {
                markReadBtn.textContent = 'Read';
                markReadBtn.disabled = true;
            }
        }
    }

    /**
     * Mark all notifications as read
     */
    markAllNotificationsAsRead() {
        this.send({
            type: 'mark_all_read'
        });

        // Update UI
        const notifications = document.querySelectorAll('.notification:not(.read)');
        notifications.forEach(notification => {
            notification.classList.add('read');
            const markReadBtn = notification.querySelector('.mark-read-btn');
            if (markReadBtn) {
                markReadBtn.textContent = 'Read';
                markReadBtn.disabled = true;
            }
        });
    }

    /**
     * Send a chat message
     * 
     * @param {string} message
     * @returns {void}
     */
    sendChatMessage(message) {
        if (!message.trim())
            return;

        this.send({
            type: 'chat',
            message: message
        });

        // Clear the input field
        const chatInput = document.getElementById('message-input');
        if (chatInput) {
            chatInput.value = '';
        }
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
        console.log('Using session ID:', sessionId);
        return sessionId;
    }
}
