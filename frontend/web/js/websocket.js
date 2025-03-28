/**
 * WebSocketClient class for handling real-time communication
 * Here are the key prerequisites to run the WebSocket connection code:
 *
 * Browser Support:
 *   - Modern browser with HTML5 WebSocket API support (Chrome, Firefox, Safari, Edge)
 *   - JavaScript enabled in the browser
 *
 * Server Requirements:
 *   - WebSocket server running and accessible at the specified URL
 *   - Proper server configuration to handle WebSocket protocol upgrade
 *   - Open port (typically 8080 or 443 for WSS) on the server
 *
 * Network Configuration:
 *   - No firewall blocking WebSocket connections
 *   - Proper proxy configuration if running behind a proxy
 *   - Valid SSL certificate if using secure WebSocket (wss://)
 *
 * URL Format:
 *   - Valid WebSocket URL starting with ws:// or wss:// protocol
 *   - Correct hostname and port number
 *   - Example: ws://localhost:8080 or wss://example.com/socket
 *
 * Development Environment:
 *   - Local development server configured for WebSocket connections
 *   - CORS headers properly set if connecting across different domains
 *
 */
class WebSocketClient {
    /**
     * Initialize WebSocket connection
     * @param {string} url - The WebSocket server URL to connect to
     */
    constructor(url) {
        this.url = url;        // Store the WebSocket server URL
        this.callbacks = {};   // Object to store event callbacks
        this.connect();        // Establish connection immediately
    }

    /**
     * Establishes WebSocket connection and sets up event handlers
     */
    connect() {
        this.ws = new WebSocket(this.url);    // Create new WebSocket instance

        // Handle incoming messages
        this.ws.onmessage = (event) => this.handleMessage(event);

        // Auto-reconnect if connection closes (attempts every 1 second)
        this.ws.onclose = () => setTimeout(() => this.connect(), 1000);
    }

    /**
     * Register event listeners
     * @param {string} event - Event name to listen for
     * @param {function} callback - Function to execute when event occurs
     */
    on(event, callback) {
        // Initialize callback array for this event if it doesn't exist
        if (!this.callbacks[event]) {
            this.callbacks[event] = [];
        }
        // Add the callback to the event's callback list
        this.callbacks[event].push(callback);
    }

    /**
     * Send data to the WebSocket server
     * @param {string} event - Event name
     * @param {*} data - Data to send
     */
    send(event, data) {
        // Convert event and data to JSON string
        const message = JSON.stringify({event, data});
        // Send the message through WebSocket
        this.ws.send(message);
    }

    /**
     * Process incoming WebSocket messages
     * @param {MessageEvent} event - WebSocket message event
     */
    handleMessage(event) {
        // Parse the received JSON message
        const message = JSON.parse(event.data);

        // Execute all registered callbacks for this event type
        if (this.callbacks[message.event]) {
            this.callbacks[message.event].forEach(callback => callback(message.data));
        }
    }
}
