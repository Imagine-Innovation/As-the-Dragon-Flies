<?php

use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'WebSocket Test';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="site-websocket">
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Connection</h3>
                </div>
                <div class="card-body">
                    <div id="status" class="alert alert-warning">
                        Status: Disconnected
                    </div>

                    <div class="form-group">
                        <label for="player-id">Player ID:</label>
                        <input type="text" id="player-id" class="form-control" value="player_<?= rand(1000, 9999) ?>">
                    </div>

                    <div class="form-group">
                        <label for="quest-id">Quest ID (optional):</label>
                        <input type="text" id="quest-id" class="form-control" value="quest_<?= rand(1000, 9999) ?>">
                    </div>

                    <div class="form-group">
                        <button id="connect-btn" class="btn btn-primary">Connect</button>
                        <button id="disconnect-btn" class="btn btn-danger" disabled>Disconnect</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3>Send Message</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="message-input">Message:</label>
                        <input type="text" id="message-input" class="form-control" placeholder="Type a message..." disabled>
                    </div>

                    <div class="form-group">
                        <button id="send-btn" class="btn btn-success" disabled>Send</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3>Messages</h3>
                </div>
                <div class="card-body">
                    <div id="messages" style="height: 300px; overflow-y: auto; border: 1px solid #ccc; padding: 10px;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // DOM Elements
    const statusDiv = document.getElementById('status');
    const messagesDiv = document.getElementById('messages');
    const playerIdInput = document.getElementById('player-id');
    const questIdInput = document.getElementById('quest-id');
    const connectBtn = document.getElementById('connect-btn');
    const disconnectBtn = document.getElementById('disconnect-btn');
    const messageInput = document.getElementById('message-input');
    const sendBtn = document.getElementById('send-btn');

    // WebSocket variables
    let socket = null;
    let isConnected = false;

    // Add a message to the messages div
    function addMessage(text, type = 'info') {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message ${type}`;
        messageDiv.style.marginBottom = '5px';
        messageDiv.style.padding = '5px';

        switch (type) {
            case 'error':
                messageDiv.style.backgroundColor = '#f8d7da';
                messageDiv.style.color = '#721c24';
                break;
            case 'sent':
                messageDiv.style.backgroundColor = '#d1ecf1';
                messageDiv.style.color = '#0c5460';
                break;
            case 'received':
                messageDiv.style.backgroundColor = '#d4edda';
                messageDiv.style.color = '#155724';
                break;
            default:
                messageDiv.style.backgroundColor = '#e2e3e5';
                messageDiv.style.color = '#383d41';
                break;
        }

        messageDiv.textContent = text;
        messagesDiv.appendChild(messageDiv);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    // Update UI based on connection status
    function updateUI() {
        statusDiv.textContent = `Status: \${isConnected ? 'Connected' : 'Disconnected'}`;
        statusDiv.className = isConnected ? 'alert alert-success' : 'alert alert-warning';

        connectBtn.disabled = isConnected;
        disconnectBtn.disabled = !isConnected;
        messageInput.disabled = !isConnected;
        sendBtn.disabled = !isConnected;
    }

    // Connect to WebSocket server
    function connect() {
        if (isConnected)
            return;

        const urls = [
            `ws://\${window.location.hostname}:8082`,
            'ws://localhost:8082',
            'ws://127.0.0.1:8082'
        ];

        let currentUrlIndex = 0;

        function tryConnect() {
            if (currentUrlIndex >= urls.length) {
                addMessage('Failed to connect to any URL', 'error');
                return;
            }

            const url = urls[currentUrlIndex];
            addMessage(`Trying to connect to \${url}...`);

            socket = new WebSocket(url);

            socket.onopen = function () {
                isConnected = true;
                addMessage(`Connected to \${url}`, 'info');
                updateUI();

                // Send registration
                const playerId = playerIdInput.value;
                const questId = questIdInput.value;

                const registrationData = {
                    playerId: playerId
                };

                if (questId) {
                    registrationData.questId = questId;
                }

                socket.send(JSON.stringify(registrationData));
                addMessage(`Sent registration: \${JSON.stringify(registrationData)}`, 'sent');
            };

            socket.onmessage = function (event) {
                addMessage(`Received: \${event.data}`, 'received');

                try {
                    const data = JSON.parse(event.data);
                    // You can handle specific message types here
                } catch (e) {
                    addMessage(`Error parsing message: \${e.message}`, 'error');
                }
            };

            socket.onclose = function () {
                if (isConnected) {
                    isConnected = false;
                    addMessage('Connection closed', 'info');
                    updateUI();
                }
            };

            socket.onerror = function (error) {
                addMessage(`Connection error with \${url}`, 'error');
                console.error('WebSocket error:', error);

                // Try the next URL
                currentUrlIndex++;
                setTimeout(tryConnect, 1000);
            };
        }

        tryConnect();
    }

    // Disconnect from WebSocket server
    function disconnect() {
        if (!isConnected)
            return;

        socket.close();
        isConnected = false;
        addMessage('Disconnected from server', 'info');
        updateUI();
    }

    // Send a message to the server
    function sendMessage() {
        if (!isConnected)
            return;

        const message = messageInput.value.trim();
        if (!message)
            return;

        try {
            // Try to parse as JSON
            const jsonData = JSON.parse(message);
            socket.send(JSON.stringify(jsonData));
            addMessage(`Sent: \${JSON.stringify(jsonData)}`, 'sent');
        } catch (e) {
            // Send as plain text
            socket.send(message);
            addMessage(`Sent: \${message}`, 'sent');
        }

        messageInput.value = '';
    }

    // Event listeners
    connectBtn.addEventListener('click', connect);
    disconnectBtn.addEventListener('click', disconnect);
    sendBtn.addEventListener('click', sendMessage);

    messageInput.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });

    // Initialize UI
    updateUI();
    addMessage('WebSocket test page loaded. Click "Connect" to start.', 'info');
</script>
