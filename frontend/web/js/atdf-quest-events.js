/**
 * Quest Events JavaScript Module
 */
var QuestEvents = (function () {
    var socket;
    var isConnected;
    var reconnectAttempts;
    const maxReconnectAttempts = 5;
    const reconnectDelay = 3000; // 3 seconds
    var config = {
        playerId: null,
        questId: null,
        serverUrl: '',
        wsUrl: '',
        wsAltUrl: ''
    };

    /**
     * Initialize the module
     * @param {Object} options Configuration options
     */
    function init(options) {
        isConnected = false;
        reconnectAttempts = 0;
        config = $.extend(config, options);

        if (!config.playerId || !config.questId) {
            console.error('Player ID and Quest ID are required');
            return;
        }

        initWebSocket();
        initEventHandlers();
        loadInitialData();
    }

    /**
     * Initialize WebSocket connection
     */
    function initWebSocket() {
        Logger.log(1, `initWebSocket`, `config=${JSON.stringify(config)}`);

        // Close existing connection if any
        if (socket) {
            Logger.log(2, `initWebSocket`, `Previous socket is closed`);
            socket.close();
        }
        socket = new WebSocket(config.wsUrl);

        socket.onopen = function () {
            Logger.log(2, `initWebSocket`, `onopen - WebSocket connection established`);
            isConnected = true;
            reconnectAttempts = 0;

            // Send player registration
            sendRegistration();
        };

        socket.onmessage = function (event) {
            Logger.log(2, `initWebSocket`, `onmessage - Message from server: ${JSON.stringify(event.data)}`);
            try {
                var data = JSON.parse(event.data);
                handleEvent(data);
            } catch (e) {
                console.error('Error parsing message:', e);
            }
        };

        socket.onclose = function () {
            Logger.log(2, `initWebSocket`, `onclose - WebSocket connection closed: ${JSON.stringify(event)}`);
            isConnected = false;

            // Attempt to reconnect
            if (reconnectAttempts < maxReconnectAttempts) {
                reconnectAttempts++;
                Logger.log(2, `initWebSocket`, `onclose - Attempting to reconnect (${reconnectAttempts}/${maxReconnectAttempts})...`);
                setTimeout(initWebSocket, reconnectDelay);
            } else {
                console.error('Max reconnect attempts reached. Please refresh the page.');
            }
        };

        socket.onerror = function (error) {
            Logger.log(2, `initWebSocket`, `onerror - WebSocket error: ${JSON.stringify(error)}`);
            console.error('WebSocket error:', error);

            // Try alternative connection if this is the first error
            if (reconnectAttempts === 0) {
                console.log('Trying alternative connection...');
                setTimeout(() => {
                    socket = new WebSocket(config.wsAltUrl);

                    socket.onopen = function () {
                        Logger.log(2, `initWebSocket`, `onerror - Connected via alternative URL ${config.wsAltUrl}!`);
                        console.log('Connected via alternative URL!');
                        isConnected = true;
                        reconnectAttempts = 0;
                        sendRegistration();
                    };

                    socket.onerror = function (altError) {
                        console.error('Alternative connection also failed:', altError);
                    };
                }, 1000);
            }
        };
    }

    /**
     * Initialize UI event handlers
     */
    function initEventHandlers() {
        Logger.log(1, `initEventHandlers`, ``);
        // Send message button click
        $('#send-message').on('click', function () {
            Logger.log(2, `initEventHandlers`, `send-message click`);
            sendMessage();
        });

        // Enter key in message input
        $('#message-input').on('keypress', function (e) {
            if (e.which === 13) {
                Logger.log(2, `initEventHandlers`, `message-input keypress CR`);
                sendMessage();
                return false;
            }
        });

        // Start quest button click
        $('#start-quest-btn').on('click', function (e) {
            const url = $(this).attr('href');
            Logger.log(2, `initEventHandlers`, `start-quest-btn on click, url=${url}`);
            e.preventDefault();

            $.ajax({
                url: url,
                type: 'POST',
                data: {
                    action: 'start-quest',
                    actionData: {}
                },
                dataType: 'json',
                success: function (response) {
                    if (!response.success) {
                        alert(response.message || 'Failed to start quest');
                    }
                },
                error: function () {
                    alert('An error occurred while trying to start the quest');
                }
            });
        });
    }

    /**
     * Load initial chat messages and player list
     */
    function loadInitialData() {
        // Load chat messages
        AjaxUtils.request({
            url: 'quest/get-messages',
            data: {questId: config.questId},
            successCallback: (response) => {
                if (response.success) {
                    response.messages.forEach(function (message) {
                        addChatMessage(message);
                    });

                    // Scroll to bottom of chat
                    scrollChatToBottom();
                }
            }
        });
        /*
        $.ajax({
            url: 'quest/get-messages',
            type: 'GET',
            data: {questId: config.questId},
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    response.messages.forEach(function (message) {
                        addChatMessage(message);
                    });

                    // Scroll to bottom of chat
                    scrollChatToBottom();
                }
            }
        });
        */
    }

    /**
     * Send player registration to the server
     */
    function sendRegistration() {
        Logger.log(1, `sendRegistration`, ``);
        if (!isConnected) {
            console.error('Cannot send registration: WebSocket not connected');
            return;
        }

        const playerId = config.playerId;
        const questId = config.questId;

        if (!playerId) {
            console.error('Cannot send registration: Player ID not found');
            return;
        }

        const registrationData = {
            playerId: playerId
        };

        if (questId) {
            registrationData.questId = questId;
        }

        socket.send(JSON.stringify(registrationData));
        Logger.log(1, `sendRegistration`, `Sent registration:${JSON.stringify(registrationData)}`);
    }

    /**
     * Send a chat message
     */
    function sendMessage() {
        var messageInput = $('#message-input');
        var message = messageInput.val().trim();

        if (message) {
            AjaxUtils.request({
                url: 'quest/send-message',
                data: {message: message},
                successCallback: (response) => {
                    if (!response.success) {
                        alert(response.message || 'Failed to send message');
                    }

                    // Clear input regardless of success
                    messageInput.val('');
                },
                errorCallback: (response) => {
                    alert('An error occurred while sending your message');
                }
            });
            /*
             $.ajax({
             url: 'quest/send-message',
             type: 'POST',
             data: {message: message},
             dataType: 'json',
             success: function (response) {
             if (!response.success) {
             alert(response.message || 'Failed to send message');
             }
             
             // Clear input regardless of success
             messageInput.val('');
             },
             error: function () {
             alert('An error occurred while sending your message');
             }
             });
             */
        }
    }

    /**
     * Handle incoming events from the server
     * @param {Object} event Event data
     */
    function handleEvent(event) {
        switch (event.type) {
            case 'new-player':
                handleNewPlayerEvent(event);
                break;

            case 'new-message':
                handleNewMessageEvent(event);
                break;

            case 'start-quest':
                handleStartQuestEvent(event);
                break;

            case 'game-action':
                handleGameActionEvent(event);
                break;
        }
    }

    /**
     * Handle new player event
     * @param {Object} event Event data
     */
    function handleNewPlayerEvent(event) {
        // Add player to the player list if not already there
        if ($('#player-list li[data-player-id="' + event.payload.playerId + '"]').length === 0) {
            $('#player-list').append(
                    '<li class="list-group-item" data-player-id="' + event.payload.playerId + '">' +
                    event.payload.playerName +
                    '</li>'
                    );
        }

        // Add system message to chat
        addSystemMessage(event.payload.playerName + ' has joined the quest');
    }

    /**
     * Handle new message event
     * @param {Object} event Event data
     */
    function handleNewMessageEvent(event) {
        addChatMessage({
            playerName: event.payload.playerName,
            message: event.payload.message,
            timestamp: event.payload.timestamp
        });

        scrollChatToBottom();
    }

    /**
     * Handle start quest event
     * @param {Object} event Event data
     */
    function handleStartQuestEvent(event) {
        // Add system message to chat
        addSystemMessage('The quest "' + event.payload.questName + '" has begun!');

        // Redirect to quest page after a short delay
        setTimeout(function () {
            window.location.href = 'quest/play?id=' + event.payload.questId;
        }, 3000);
    }

    /**
     * Handle game action event
     * @param {Object} event Event data
     */
    function handleGameActionEvent(event) {
        switch (event.payload.action) {
            case 'leave-quest':
                // Remove player from the list
                $('#player-list li[data-player-id="' + event.payload.playerId + '"]').remove();

                // Add system message to chat
                addSystemMessage(event.payload.playerName + ' has left the quest');
                break;

                // Handle other game actions as needed
            default:
                console.log('Unhandled game action:', event.payload.action);
                break;
        }
    }

    /**
     * Add a chat message to the chat window
     * @param {Object} message Message data
     */
    function addChatMessage(message) {
        $('#chat-messages').append(
                '<div class="message">' +
                '<span class="timestamp">[' + message.timestamp + ']</span> ' +
                '<span class="player-name">' + message.playerName + ':</span> ' +
                '<span class="message-text">' + message.message + '</span>' +
                '</div>'
                );

        scrollChatToBottom();
    }

    /**
     * Add a system message to the chat window
     * @param {String} message System message text
     */
    function addSystemMessage(message) {
        $('#chat-messages').append(
                '<div class="message system-message">' +
                '<span class="timestamp">[' + formatTimestamp(new Date()) + ']</span> ' +
                '<span class="system-text">' + message + '</span>' +
                '</div>'
                );

        scrollChatToBottom();
    }

    /**
     * Scroll the chat window to the bottom
     */
    function scrollChatToBottom() {
        var chatMessages = $('#chat-messages');
        chatMessages.scrollTop(chatMessages[0].scrollHeight);
    }

    /**
     * Format a timestamp
     * @param {Date} date Date object
     * @return {String} Formatted timestamp
     */
    function formatTimestamp(date) {
        return date.toISOString().replace('T', ' ').substr(0, 19);
    }

    // Public API
    return {
        init: init
    };
})();
