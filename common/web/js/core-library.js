/**
 * Core library for the application.
 * Refactored to pure JavaScript (ES6+).
 */

// Core configuration
const CONFIG = {
    LOG_LEVEL: 10,
    ROOT_URL: null,
    CSRF_TOKEN: null
};

// Library initialization
class CoreLibrary {
    static init() {
        if (!this.checkCompatibility()) {
            console.error("Browser is not compatible with required JavaScript features.");
            return;
        }

        CONFIG.CSRF_TOKEN = this.getCsrfToken();
        const rootUrlMeta = document.querySelector('meta[name="ajax-root-url"]');
        CONFIG.ROOT_URL = rootUrlMeta ? rootUrlMeta.getAttribute('content') : null;
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

    static getCsrfToken() {
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        return csrfMeta ? csrfMeta.getAttribute('content') : null;
    }
}

// Logging utility
class Logger {
    static log(level, fx, msg) {
        if (level <= CONFIG.LOG_LEVEL) {
            const offset = level < 5 ? ' '.repeat(level * 4) : '--> ';
            console.log(`${offset}${this._getCaller()}.${fx}: ${msg}`);
        }
    }

    static _getCaller() {
        try {
            throw new Error();
        } catch (err) {
            let stack = err.stack;
            if (!stack) return "unknown";
            // N.B. stack === "Error\n  at Hello ...\n  at main ... \n...."
            let m = stack.match(/.*?log.*?\n(.*?)\n/);
            if (m) {
                const callingLine = m[1];
                const atIndex = callingLine.indexOf(" at ");
                if (atIndex === -1) return "unknown";
                const startPos = atIndex + 4;
                const endPos = callingLine.indexOf(".", startPos);
                if (endPos === -1) return callingLine.substring(startPos).split(' ')[0] || "unknown";
                const caller = callingLine.substring(startPos, endPos);
                return caller;
            }
            return "unknown";
        }
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
    /**
     * Checks if a DOM element exists based on the selector.
     * @param {string|Element} target - CSS selector or DOM element
     * @returns {boolean}
     */
    static exists(target) {
        if (!target) return false;
        let element;
        try {
            element = typeof target === 'string' ? document.querySelector(target) : target;
        } catch (e) {
            element = null;
        }
        const exists = !!element;
        Logger.log(3, 'exists', `target=${target} => ${exists}`);
        if (!exists) {
            Logger.log(10, 'exists', `=> ${target} not found!`);
        }
        return exists;
    }

    /**
     * Gets the value or inner HTML of a parameter element.
     * @param {string} parameterId
     * @param {any} defaultValue
     * @returns {any}
     */
    static getParam(parameterId, defaultValue) {
        Logger.log(2, 'getParam', `parameterId=${parameterId}, defaultValue=${defaultValue}`);

        if (!parameterId) {
            Logger.log(10, 'getParam', `--> return default: ${defaultValue}`);
            return defaultValue;
        }

        const element = document.getElementById(parameterId);
        if (element) {
            const isInput = ['INPUT', 'SELECT', 'TEXTAREA'].includes(element.tagName);
            const value = isInput ? element.value : element.innerHTML;
            if (value !== null && value !== undefined && value !== '') {
                Logger.log(10, 'getParam', `--> return element: ${value}`);
                return value;
            }
        }

        Logger.log(10, 'getParam', `--> element '${parameterId}' not found, return default: ${defaultValue}`);
        return defaultValue;
    }
}

// AJAX utilities
class AjaxUtils {
    static buildUrl(route = 'site/index') {
        Logger.log(3, 'buildUrl', `route=${route}`);
        const actualRoute = route ?? 'site/index';
        return `${CONFIG.ROOT_URL}/index.php?r=${actualRoute.toLowerCase()}`;
    }

    /**
     * Performs an AJAX request using Fetch API.
     * @param {Object} options
     */
    static async request({url, method = 'POST', data = {}, successCallback, errorCallback}) {
        let targetUrl = this.buildUrl(url);
        const options = {
            method: method.toUpperCase(),
            headers: {
                'X-CSRF-Token': CONFIG.CSRF_TOKEN,
                'X-Requested-With': 'XMLHttpRequest'
            }
        };

        if (options.method === 'GET') {
            const queryString = new URLSearchParams(data).toString();
            if (queryString) {
                targetUrl += `&${queryString}`;
            }
        } else {
            // Check if data is FormData, otherwise use URLSearchParams for standard POST
            if (data instanceof FormData) {
                options.body = data;
            } else {
                options.headers['Content-Type'] = 'application/x-www-form-urlencoded';
                options.body = new URLSearchParams(data).toString();
            }
        }

        try {
            const response = await fetch(targetUrl, options);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            const result = await response.json();

            Logger.log(4, 'request', `success => error=${result.error} response=${JSON.stringify(result.msg, null, 2)}`);
            if (result.error) {
                Logger.getCallerStack(result.msg);
            }
            successCallback?.(result);
            return result;
        } catch (error) {
            Logger.log(4, 'request', `error=${error}`);
            errorCallback?.({error: true, msg: 'Network error occurred'});
            return {error: true, msg: error.message};
        }
    }

    static getContent({target, url, data = {}, callback}) {
        Logger.log(3, 'getContent', `target=${target}, url=${url}`);

        const targetElement = typeof target === 'string' ? document.querySelector(target) : target;
        if (!targetElement) {
            Logger.log(10, 'getContent', `--> target '${target}' not found`);
            return;
        }

        this.request({
            url,
            data,
            successCallback: (response) => {
                targetElement.innerHTML = response.content;
                callback?.(response);
            }
        });
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

        AjaxUtils.request({
            url: 'user/ajax-set-language',
            data: {lang},
            successCallback: (response) => {
                if (!response.error) {
                    window.location.reload();
                } else {
                    ToastManager.show('Language', response.msg, 'error');
                }
            }
        });
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

        AjaxUtils.request({
            url: params.route,
            data: params,
            successCallback: (response) => {
                Logger.log(4, 'loadGenericAjaxTable', `response=${Object.values(response)}`);
                const limitElement = document.getElementById('limit');
                if (limitElement) {
                    limitElement.innerHTML = response.limit;
                }

                const containerElement = document.getElementById(params.container);
                if (containerElement) {
                    containerElement.innerHTML = response.content;
                }

                window.location.href = "#top";
            }
        });
    }

    static setLimit(limit) {
        Logger.log(1, 'setLimit', `limit=${limit}`);
        const limitElement = document.getElementById('limit');
        if (limitElement) {
            limitElement.innerHTML = limit;
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
        });
    }

    /**
     * Appends and displays a new toast
     * @param {string} target - Toast container selector
     * @param {Object} response - Server response containing toast data
     */
    static _appendAndShowToast(target, response) {
        const container = document.querySelector(target);
        if (container) {
            container.insertAdjacentHTML('beforeend', response.content);
            this._displayToast(response.UUID);
        }
    }

    /**
     * Displays and manages toast lifecycle
     * @param {string} UUID - Unique identifier for the toast
     */
    static _displayToast(UUID) {
        Logger.log(2, '_displayToast', `UUID=${UUID}`);

        const element = document.getElementById(UUID);
        if (!element)
            return;

        // Initialize and show Bootstrap toast
        // Bootstrap 5.x allows passing the DOM element directly
        if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
            const toast = new bootstrap.Toast(element);
            toast.show();
        } else {
            console.warn('Bootstrap Toast is not available.');
        }

        // Cleanup after display (3 seconds for display + some buffer)
        setTimeout(() => {
            if (element.parentNode) {
                element.remove();
            }
        }, 3500);
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

        AjaxUtils.request({
            url: 'user/ajax-set-role',
            data: {id: userId, role, status},
            successCallback: (response) => {
                ToastManager.show(
                        response.error ? "Error" : "User role",
                        response.msg,
                        response.error ? 'error' : 'info'
                        );
            }
        });
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

        AjaxUtils.request({
            url: 'access-right/ajax-set-access-right',
            data: {id, access, status},
            successCallback: (response) => {
                ToastManager.show(
                        response.error ? "Error" : "Access right",
                        response.msg,
                        response.error ? 'error' : 'info'
                        );
            }
        });
    }
}

class LayoutInitializer {
    static initAjaxPage() {
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof TableManager !== 'undefined' && TableManager.loadGenericAjaxTable) {
                TableManager.loadGenericAjaxTable(0);
            } else {
                console.error('TableManager or loadGenericAjaxTable method not found.');
            }
        });
    }

    static initNavbarLobby() {
        document.addEventListener('DOMContentLoaded', () => {
            const notificationDropdown = document.getElementById('notificationDropdown');
            if (notificationDropdown) {
                notificationDropdown.addEventListener('show.bs.dropdown', () => {
                    const counterElement = document.getElementById('notificationCounter');
                    let n = counterElement ? counterElement.textContent : '0';

                    let playerId = typeof currentPlayerId !== 'undefined' ? currentPlayerId : null;

                    if (playerId && parseInt(n) > 0) {
                        if (typeof NotificationHandler !== 'undefined' && NotificationHandler.executeRequest) {
                            let config = {
                                route: 'notification/ajax-mark-as-read',
                                method: 'POST',
                                placeholder: 'notificationCounter',
                                badge: true
                            };
                            let data = {playerId: playerId};
                            NotificationHandler.executeRequest(config, data);
                        } else {
                            console.error('NotificationHandler or executeRequest method not found.');
                        }
                    }
                });
            }
        });
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

        const container = document.getElementById('container');
        if (container) container.innerHTML = `ajax-${itemType}`;

        const currentTab = document.getElementById('currentTab');
        if (currentTab) currentTab.innerHTML = itemType;

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

    static initActionButton() {
        Logger.log(1, 'initActionButton', ``);
        document.addEventListener('DOMContentLoaded', () => {

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
                    const controller = parts[1]; // This will be 'add', 'remove', or 'delete'
                    const action = parts[2]; // This will be 'add', 'remove', or 'delete'
                    const id = parts[3]; // This will be the item ID (e.g., '20', '22', '120')

                    // You can now use the operation and id to perform the desired action
                    ActionButtonManager.handleAction(controller, action, id);
                });
            });
        });
    }

    static handleAction(controller, action, id) {
        Logger.log(1, 'handleAction', `controller=${controller}, action=${action}, id=${id}`);
        AjaxUtils.request({
            url: `${controller}/${action}`,
            data: {id},
            successCallback: (response) => {
                ToastManager.show('Shop', response.msg, response.error ? 'error' : 'info');
            }
        });
    }
}

// Initialize library immediately
CoreLibrary.init();

document.addEventListener('DOMContentLoaded', () => {
    ActionButtonManager.initActionButton();
});
