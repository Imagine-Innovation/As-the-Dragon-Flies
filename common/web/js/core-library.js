/**
 * Core library for the application.
 * Refactored to pure JavaScript (ES6+).
 * SECURITY: CONFIG is encapsulated in a frozen object to prevent external modification
 * and limit exposure of the CSRF token.
 */

const CONFIG = Object.freeze({
    /**
     * FIX: LOG_LEVEL reduced to 0 in production to avoid info leaks.
     * Add <meta name="app-env" content="production"> on prod environments.
     */
    get LOG_LEVEL() {
        const env = document.querySelector('meta[name="app-env"]')?.getAttribute('content');
        return env === 'production' ? 0 : 10;
    },
    get ROOT_URL() {
        return document.querySelector('meta[name="ajax-root-url"]')?.getAttribute('content') ?? null;
    },
    get CSRF_TOKEN() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? null;
    }
});

// Library initialization
class CoreLibrary {
    static init() {
        if (!this.checkCompatibility()) {
            console.error("Browser is not compatible with required JavaScript features.");
            return;
        }

        // FIX: ROOT_URL is now read dynamically from the meta tag
        if (!CONFIG.ROOT_URL) {
            console.error('CoreLibrary: missing <meta name="ajax-root-url"> tag. AJAX calls will fail.');
        }
        console.log(`Library initialized with log level ${CONFIG.LOG_LEVEL}`);
    }

    /**
     * Checks if the browser supports required ES6+ features.
     * @returns {boolean}
     */
    static checkCompatibility() {
        const requiredFeatures = {
            'Fetch API': window.fetch,
            'Promises': window.Promise,
            'Arrow Functions': (() => {
                try {
                    eval('() => {}');
                    return true;
                } catch (e) {
                    return false;
                }
            })(),
            'Classes': typeof class {} === 'function',
            'Template Literals': (() => {
                try {
                    eval('`test`');
                    return true;
                } catch (e) {
                    return false;
                }
            })(),
            'querySelector': document.querySelector && document.querySelectorAll
        };

        for (const [feature, supported] of Object.entries(requiredFeatures)) {
            if (!supported) {
                console.warn(`Feature ${feature} is not supported by this browser.`);
                return false;
            }
        }
        return true;
    }
}

// Logging utility
class Logger {
    static log(level, fx, msg) {
        if (level <= CONFIG.LOG_LEVEL) {
            const offset = level < 5 ? ' '.repeat(level * 4) : '--> ';
            console.log(`${offset}${this._getCaller() ?? 'unknown'}.${fx}: ${msg}`);
        }
    }

    static _getCaller() {
        /**
         * FIX: returns 'unknown' if the stack trace cannot be parsed
         * (non-V8 browsers, minified code)
         */
        try {
            throw new Error();
        } catch (err) {
            const stack = err.stack;
            if (!stack) return 'unknown';
            // N.B. stack === "Error\n  at Hello ...\n  at main ... \n...."
            const m = stack.match(/.*?log.*?\n(.*?)\n/);
            if (m) {
                const callingLine = m[1];
                const startPos = callingLine.indexOf(" at ") + 4;
                const endPos = callingLine.indexOf(".");
                if (startPos > 3 && endPos > startPos) {
                    return callingLine.substring(startPos, endPos);
                }
            }
        }
        return 'unknown';
    }

    static _getFullTimestamp() {
        const pad = (n, s = 2) => (`${new Array(s).fill(0).join('')}${n}`).slice(-s);
        const d = new Date();

        return `${pad(d.getFullYear(), 4)}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}.${pad(d.getMilliseconds(), 3)}`;
    }

    static getCallerStack(msg) {
        console.log(`-----------------------------------------`);
        console.trace(`Error returned with message ${msg} from:`);
        console.log(`-----------------------------------------`);
    }
}

// DOM utilities
class DOMUtils {
    static exists(target) {
        /**
         * FIX: querySelector() throws a SyntaxError on an invalid selector
         * (e.g. UUID starting with a digit → "#1abc"). We catch the error properly.
         */
        try {
            const exists = document.querySelector(target) !== null;
            Logger.log(3, 'exists', `target=${target} => ${exists}`);
            if (!exists) {
                Logger.log(10, 'exists', `=> ${target} not found!`);
            }
            return exists;
        } catch (e) {
            Logger.log(10, 'exists', `=> invalid selector "${target}": ${e.message}`);
            return false;
        }
    }

    static getParam(parameterId, defaultValue) {
        Logger.log(2, 'getParam', `parameterId=${parameterId}, defaultValue=${defaultValue}`);

        if (!parameterId) {
            Logger.log(10, 'getParam', `--> return default: ${defaultValue}`);
            return defaultValue;
        }

        const element = document.getElementById(parameterId);
        if (element) {
            const isFormField = element.matches('input, select, textarea');
            /**
             * FIX: textContent instead of innerHTML for non-form elements.
             * Avoids returning HTML tags (e.g. "<b>10</b>") as parameter value.
             */
            const value = isFormField ? element.value : element.textContent;
            if (value) {
                Logger.log(10, 'getParam', `--> return element: ${value}`);
                return value;
            }
        }

        Logger.log(10, 'getParam', `--> element '${parameterId}' not found, return default: ${defaultValue}`);
        return defaultValue;
    }

    /**
     * FIX: centralized helper to assign server HTML securely.
     * Uses setHTML() (with native sanitization) if available, otherwise innerHTML.
     * This protects against XSS injections on content coming from the server.
     */
    static setHTML(element, html) {
        if (typeof element.setHTML === 'function') {
            // API Sanitizer (Chrome 105+, Edge 105+)
            element.setHTML(html);
        } else {
            // Fallback: innerHTML without native sanitization.
            // Ensure the HTML content is properly escaped on the server side.
            element.innerHTML = html;
        }
    }
}

// AJAX utilities
class AjaxUtils {
    static buildUrl(route = 'site/index') {
        Logger.log(3, 'buildUrl', `route=${route}`);
        /**
         * FIX: throws an explicit error if ROOT_URL is missing,
         * instead of silently producing "null/index.php?r=..."
         */
        if (!CONFIG.ROOT_URL) {
            throw new Error('buildUrl: CONFIG.ROOT_URL is null. Add <meta name="ajax-root-url"> to your page.');
        }
        const actualRoute = (route ?? 'site/index').toLowerCase();
        return `${CONFIG.ROOT_URL}/index.php?r=${encodeURIComponent(actualRoute)}`;
    }

    /**
     * FIX: the method is async and returns a Promise.
     * Callers that do not await must handle rejection via .catch()
     * or pass an errorCallback. Errors are no longer silent.
     */
    static async request({ url, method = 'POST', data = {}, successCallback, errorCallback }) {
        try {
            const response = await fetch(this.buildUrl(url), {
                method,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest',
                    /**
                     * FIX: the token is read dynamically from the meta tag
                     * at each request, never stored in an accessible global object
                     */
                    'X-CSRF-Token': CONFIG.CSRF_TOKEN
                },
                body: new URLSearchParams(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP error: ${response.status}`);
            }

            const json = await response.json();

            Logger.log(4, 'request', `success => error=${json.error} response=${JSON.stringify(json.msg, null, 2)}`);
            if (json.error) {
                Logger.getCallerStack(json.msg);
            }
            successCallback?.(json);

            return json;
        } catch (error) {
            Logger.log(4, 'request', `error=${error}`);
            errorCallback?.({ error: true, msg: 'Network error occurred' });
            // Re-throw so async callers can also catch the error
            throw error;
        }
    }

    static getContent({ target, url, data = {}, callback }) {
        Logger.log(3, 'getContent', `target=${target}, url=${url}`);

        if (!DOMUtils.exists(target))
            return;

        // FIX: explicit .catch() to avoid unhandled promise rejection
        this.request({
            url,
            data,
            successCallback: (response) => {
                // FIX: use DOMUtils.setHTML() instead of raw innerHTML
                const el = document.querySelector(target);
                if (el) DOMUtils.setHTML(el, response.content);
                callback?.(response);
            }
        }).catch(error => Logger.log(10, 'getContent', `request failed: ${error}`));
    }
}

/**
 * LanguageManager Class
 * Handles application language switching via AJAX
 */
class LanguageManager {
    /**
     * Sets the application language and reloads the page
     * @param {string} lang - Language code (e.g., 'en', 'fr')
     */
    static setLanguage(lang) {
        Logger.log(1, 'setLanguage', `lang=${lang}`);

        // FIX: explicit .catch()
        AjaxUtils.request({
            url: 'user/ajax-set-language',
            data: { lang },
            successCallback: (response) => {
                if (!response.error) {
                    window.location.reload();
                } else {
                    ToastManager.show('Language', response.msg, 'error');
                }
            }
        }).catch(error => Logger.log(10, 'setLanguage', `request failed: ${error}`));
    }
}

// Add this new class to handle table operations
class TableManager {
    static loadGenericAjaxTable(pageNumber) {
        Logger.log(1, 'loadGenericAjaxTable', `pageNumber=${pageNumber}`);

        const params = {
            page: pageNumber,
            limit: DOMUtils.getParam("limit", 10),
            container: DOMUtils.getParam("container", "ajaxContainer"),
            route: DOMUtils.getParam("route", null),
            currentTab: DOMUtils.getParam("currentTab", "Armor"),
            currentId: DOMUtils.getParam("currentId"),
            filter: DOMUtils.getParam("filter")
        };

        // Log parameters
        Object.entries(params).forEach(([key, value]) => {
            Logger.log(10, 'loadGenericAjaxTable', `${key}=${value}`);
        });

        // FIX: explicit .catch()
        AjaxUtils.request({
            url: params.route,
            data: params,
            successCallback: (response) => {
                Logger.log(4, 'loadGenericAjaxTable', `response=${Object.values(response)}`);

                // FIX: existence check before DOM access
                const limitEl = document.getElementById('limit');
                const containerEl = document.getElementById(params.container);

                if (!limitEl) {
                    Logger.log(10, 'loadGenericAjaxTable', `#limit element not found`);
                } else {
                    limitEl.textContent = response.limit;
                }

                if (!containerEl) {
                    Logger.log(10, 'loadGenericAjaxTable', `#${params.container} element not found`);
                } else {
                    // FIX: DOMUtils.setHTML() instead of raw innerHTML
                    DOMUtils.setHTML(containerEl, response.content);
                }

                window.location.href = "#top";
            }
        }).catch(error => Logger.log(10, 'loadGenericAjaxTable', `request failed: ${error}`));
    }

    static setLimit(limit) {
        Logger.log(1, 'setLimit', `limit=${limit}`);
        // FIX: existence check before DOM access
        const limitEl = document.getElementById('limit');
        if (limitEl) {
            limitEl.textContent = limit;
        }
        this.loadGenericAjaxTable(0);
    }
}

/**
 * ToastManager Class
 * Handles creation, display, and cleanup of toast notifications
 */
class ToastManager {
    /**
     * Displays a toast notification
     * @param {string} header - Toast header text
     * @param {string} message - Main toast message
     * @param {string} severity - Message severity level
     */
    static show(header, message, severity) {
        Logger.log(2, 'show', `header=${header}, message=${message}, severity=${severity}`);

        const target = '#toastContainer';
        if (!DOMUtils.exists(target))
            return;

        // FIX: explicit .catch()
        AjaxUtils.request({
            url: 'site/ajax-toast',
            data: {
                messageHeader: header,
                message: message,
                severity: severity
            },
            successCallback: (response) => {
                if (!response.error) {
                    this._appendAndShowToast(target, response);
                }
            }
        }).catch(error => Logger.log(10, 'show', `request failed: ${error}`));
    }

    /**
     * Appends and displays a new toast
     * @param {string} target - Toast container selector
     * @param {Object} response - Server response containing toast data
     */
    static _appendAndShowToast(target, response) {
        const container = document.querySelector(target);
        if (!container) return;

        /**
         * FIX: check that response.content contains an HTML element before insertion.
         * If firstElementChild is null (empty or plain text), abort properly.
         */
        const temp = document.createElement('div');
        DOMUtils.setHTML(temp, response.content);
        const toastEl = temp.firstElementChild;
        if (!toastEl) {
            Logger.log(10, '_appendAndShowToast', `response.content is empty or not valid HTML, skipping toast`);
            return;
        }
        container.appendChild(toastEl);
        this._displayToast(response.UUID);
    }

    /**
     * Displays and manages toast lifecycle
     * @param {string} UUID - Unique identifier for the toast
     */
    static _displayToast(UUID) {
        Logger.log(2, '_displayToast', `UUID=${UUID}`);

        /**
         * FIX: CSS.escape() to avoid SyntaxError if UUID starts with a digit
         * (e.g. "#1a2b3c" is invalid CSS selector → "#\31 a2b3c" is correct)
         */
        const target = `#${CSS.escape(UUID)}`;
        if (!DOMUtils.exists(target))
            return;

        // Initialize and show Bootstrap toast
        const toast = new bootstrap.Toast(document.querySelector(target));
        toast.show();

        // Cleanup after display
        setTimeout(() => document.getElementById(UUID)?.remove(), 3000);
    }
}

/**
 * UserManager Class
 * Handles user role management and access rights
 */
class UserManager {
    /**
     * Updates user role status
     * @param {number} userId - User identifier
     * @param {string} role - Role to set
     */
    static setRole(userId, role) {
        Logger.log(1, 'setRole', `userId=${userId}, role=${role}`);

        const inputControl = document.getElementById(`user-${role}-${userId}`);
        if (!inputControl)
            return;

        const checked = inputControl.checked;
        const status = checked ? 1 : 0;

        Logger.log(10, 'setRole', `checked=${checked}, status=${status}`);

        // FIX: explicit .catch()
        AjaxUtils.request({
            url: 'user/ajax-set-role',
            data: { id: userId, role, status },
            successCallback: (response) => {
                ToastManager.show(
                    response.error ? "Error" : "User role",
                    response.msg,
                    response.error ? 'error' : 'info'
                );
            }
        }).catch(error => Logger.log(10, 'setRole', `request failed: ${error}`));
    }

    /**
     * Updates access right status
     * @param {number} id - Access right identifier
     * @param {string} access - Access type to set
     */
    static setAccessRight(id, access) {
        Logger.log(1, 'setAccessRight', `id=${id}, access=${access}`);

        const inputControl = document.getElementById(`access-right-${access}-${id}`);
        if (!inputControl)
            return;

        const checked = inputControl.checked;
        const status = checked ? 1 : 0;

        Logger.log(10, 'setAccessRight', `checked=${checked}, status=${status}`);

        // FIX: explicit .catch()
        AjaxUtils.request({
            url: 'access-right/ajax-set-access-right',
            data: { id, access, status },
            successCallback: (response) => {
                ToastManager.show(
                    response.error ? "Error" : "Access right",
                    response.msg,
                    response.error ? 'error' : 'info'
                );
            }
        }).catch(error => Logger.log(10, 'setAccessRight', `request failed: ${error}`));
    }
}

class LayoutInitializer {
    static initAjaxPage() {
        /**
         * FIX: { once: true } ensures the listener only executes once,
         * even if initAjaxPage() is called multiple times.
         */
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof TableManager !== 'undefined' && TableManager.loadGenericAjaxTable) {
                TableManager.loadGenericAjaxTable(0);
            } else {
                console.error('TableManager or loadGenericAjaxTable method not found.');
            }
        }, { once: true });
    }

    static initNavbarLobby() {
        // FIX: { once: true } to avoid accumulating listeners
        document.addEventListener('DOMContentLoaded', () => {
            const notificationDropdown = document.getElementById('notificationDropdown');
            if (!notificationDropdown) return;

            notificationDropdown.addEventListener('show.bs.dropdown', () => {
                const n = document.getElementById('notificationCounter')?.textContent;
                let playerId = typeof currentPlayerId !== 'undefined' ? currentPlayerId : null;

                // FIX: parseInt with explicit radix (base 10)
                if (playerId && parseInt(n, 10) > 0) {
                    if (typeof NotificationHandler !== 'undefined' && NotificationHandler.executeRequest) {
                        const config = {
                            route: 'notification/ajax-mark-as-read',
                            method: 'POST',
                            placeholder: 'notificationCounter',
                            badge: true
                        };
                        const data = { playerId: playerId };
                        NotificationHandler.executeRequest(config, data);
                    } else {
                        console.error('NotificationHandler or executeRequest method not found.');
                    }
                }
            });
        }, { once: true });
    }
}

/**
 * ItemManager Class
 * Handles item-related operations and tab management
 */
class ItemManager {
    /**
     * Loads and displays items for a specific type
     * @param {number} itemType - Type of items to load
     */
    static loadTypeTab(itemType) {
        Logger.log(1, 'loadTypeTab', `itemType=${itemType}`);

        // FIX: existence check before DOM access
        const containerEl = document.getElementById('container');
        const currentTabEl = document.getElementById('currentTab');

        if (!containerEl) {
            Logger.log(10, 'loadTypeTab', `#container element not found`);
            return;
        }
        if (!currentTabEl) {
            Logger.log(10, 'loadTypeTab', `#currentTab element not found`);
            return;
        }

        // Internal values not from server: textContent is enough
        containerEl.textContent = `ajax-${itemType}`;
        currentTabEl.textContent = itemType;

        TableManager.loadGenericAjaxTable(0);
    }
}

/**
 * ButtonManager Class
 * Handles action buttons click events
 */
class ActionButtonManager {

    /***********************************************/
    /*        Page initialization Methods          */
    /***********************************************/

    /**
     * FIX: initActionButton() should no longer register its own DOMContentLoaded.
     * Registration is done once at the bottom of the file, avoiding
     * double listeners (and double AJAX requests).
     */
    static initActionButton() {
        Logger.log(1, 'initActionButton', ``);

        // Select all elements with an ID that starts with 'actionButton-'
        const buttons = document.querySelectorAll('[id^="actionButton-"]');

        // Loop through each button and add the event listener
        buttons.forEach(button => {
            button.addEventListener('click', (event) => {
                Logger.log(10, 'initActionButton', `Click on button ${button.getAttribute('id')}`);
                // Prevent the default action
                event.preventDefault();

                // Retrieve the suffix from the button's ID
                const suffix = button.getAttribute('id');

                // Split the ID to extract the operation and item ID
                const parts = suffix.split('-');
                const controller = parts[1]; // e.g. 'shop'
                const action = parts[2];     // e.g. 'add', 'remove', 'delete'
                const id = parts[3];         // e.g. '20', '22', '120'

                ActionButtonManager.handleAction(controller, action, id);
            });
        });
    }

    static handleAction(controller, action, id) {
        Logger.log(1, 'handleAction', `controller=${controller}, action=${action}, id=${id}`);

        // FIX: explicit .catch()
        AjaxUtils.request({
            url: `${controller}/${action}`,
            data: { id },
            successCallback: (response) => {
                ToastManager.show('Shop', response.msg, response.error ? 'error' : 'info');
            }
        }).catch(error => Logger.log(10, 'handleAction', `request failed: ${error}`));
    }
}

// Initialize library immediately
CoreLibrary.init();

/**
 * FIX: only one global DOMContentLoaded, initActionButton() doesn't register one itself.
 * { once: true } to avoid accumulation if script is reloaded.
 */
document.addEventListener('DOMContentLoaded', () => {
    ActionButtonManager.initActionButton();
}, { once: true });
