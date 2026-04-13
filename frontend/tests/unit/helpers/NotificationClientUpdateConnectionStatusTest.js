const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const sourcePath = path.resolve(__dirname, '../../../web/js/atdf-quest-events.js');
const sourceCode = fs.readFileSync(sourcePath, 'utf8');

function loadNotificationClient({statusIcon = null, toasts = [], logs = []} = {}) {
    const context = {
        console,
        setTimeout,
        clearTimeout,
        URL,
        document: {
            getElementById: (id) => (id === 'eventHandlerStatus' ? statusIcon : null),
            addEventListener: () => {}
        },
        window: {
            addEventListener: () => {},
            location: {href: 'http://localhost/frontend/web/index.php?r=quest%2Fplay&id=95'}
        },
        ToastManager: {
            show: (title, message, severity) => toasts.push({title, message, severity})
        },
        Logger: {
            log: (...args) => logs.push(args)
        },
        AjaxUtils: {request: () => {}},
        Audio: function Audio() {
            this.preload = 'none';
        },
        WebSocket: function WebSocket() {}
    };

    vm.runInNewContext(`${sourceCode}\nthis.NotificationClient = NotificationClient;`, context, {filename: sourcePath});
    return {NotificationClient: context.NotificationClient};
}

test('updateConnectionStatus does not crash without #eventHandlerStatus and keeps toast severity', () => {
    const toasts = [];
    const logs = [];
    const {NotificationClient} = loadNotificationClient({toasts, logs});
    const client = new NotificationClient('ws://localhost:8082', 'session-1', 1, 'Player', 'avatar.png', 95, 'Quest');

    assert.doesNotThrow(() => client.updateConnectionStatus('Connection Error'));
    assert.deepEqual(toasts.at(-1), {
        title: 'Connection status',
        message: 'Connection Error',
        severity: 'error'
    });
    assert.equal(logs.at(-1)[1], 'updateConnectionStatus');
});

test('updateConnectionStatus marks status icon in error state for Connection Error', () => {
    const classNames = new Set();
    const statusIcon = {
        classList: {
            add: (name) => classNames.add(name),
            remove: (name) => classNames.delete(name)
        },
        style: {}
    };
    const toasts = [];
    const {NotificationClient} = loadNotificationClient({statusIcon, toasts});
    const client = new NotificationClient('ws://localhost:8082', 'session-2', 2, 'Player 2', 'avatar2.png', 96, 'Quest 2');

    client.updateConnectionStatus('Connection Error');

    assert.equal(classNames.has('blink'), true);
    assert.equal(statusIcon.style.color, 'var(--error)');
    assert.equal(toasts.at(-1).severity, 'error');
});
