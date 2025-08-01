// Core configuration
const CONFIG = {
    LOG_LEVEL: 10,
    ROOT_URL: null,
    CSRF_TOKEN: null
};

// Library initialization
class CoreLibrary {
    static init() {
        CONFIG.CSRF_TOKEN = this.getCsrfToken();
        CONFIG.ROOT_URL = $('meta[name="ajax-root-url"]').attr('content');
        console.log(`Library initialized with log level ${CONFIG.LOG_LEVEL}`);
    }

    static getCsrfToken() {
        return $('meta[name="csrf-token"]').attr('content') || null;
    }
}

// Logging utility
class Logger {
    static log(level, fx, msg) {
        if (level <= CONFIG.LOG_LEVEL) {
            const offset = level < 5 ? ' '.repeat(level * 4) : '--> ';
            console.log(`[${this._getFullTimestamp()}] ${offset}${this._getCaller()}.${fx}: ${msg}`);
        }
    }

    static _getCaller() {
        try {
            throw new Error();
        } catch (err) {
            let stack = err.stack;
            // N.B. stack === "Error\n  at Hello ...\n  at main ... \n...."
            let m = stack.match(/.*?log.*?\n(.*?)\n/);
            if (m) {
                const callingLine = m[1];
                const startPos = callingLine.indexOf(" at ") + 4;
                const endPos = callingLine.indexOf(".");
                const caller = callingLine.substring(startPos, endPos);
                return caller;
            }
        }
    }

    static _getFullTimestamp() {
        const pad = (n, s = 2) => (`${new Array(s).fill(0)}${n}`).slice(-s);
        const d = new Date();

        return `${pad(d.getFullYear(), 4)}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}:${pad(d.getSeconds())}.${pad(d.getMilliseconds(), 3)}`;
    }

    static getCallerStack(msg) {
        return;
        console.log(`-----------------------------------------`);
        console.trace(`Error returned with message ${msg} from:`);
        console.log(`-----------------------------------------`);
    }
}

// DOM utilities
class DOMUtils {
    static exists(target) {
        const exists = $(target).length > 0;
        Logger.log(3, 'exists', `target=${target} => ${exists}`);
        if (!exists) {
            Logger.log(10, 'exists', `=> ${target} not found!`);
        }
        return exists;
    }

    static getParam(parameterId, defaultValue) {
        Logger.log(2, 'getParam', `parameterId=${parameterId}, defaultValue=${defaultValue}`);

        if (!parameterId) {
            Logger.log(10, 'getParam', `--> return default: ${defaultValue}`);
            return defaultValue;
        }

        const element = $(`#${parameterId}`);
        if (element.length) {
            const value = element.html();
            if (value) {
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

    static request( {url, method = 'POST', data = {}, successCallback, errorCallback}) {
        return $.ajax({
            url: this.buildUrl(url),
            method,
            dataType: 'json',
            headers: {'X-CSRF-Token': CONFIG.CSRF_TOKEN},
            data
        })
                .done(response => {
                    Logger.log(4, 'request', `success => error=${response.error} response=${JSON.stringify(response.msg)}`);
                    //response.error ? errorCallback?.(response) : successCallback?.(response);
                    response.error ? Logger.getCallerStack(response.msg) : successCallback?.(response);
                })
                .fail(error => {
                    Logger.log(4, 'request', `error=${error}`);
                    errorCallback?.({error: true, msg: 'Network error occurred'});
                });
    }

    static getContent( {target, url, data = {}, callback}) {
        Logger.log(3, 'getContent', `target=${target}, url=${url}`);

        if (!DOMUtils.exists(target))
            return;

        this.request({
            url,
            data,
            successCallback: (response) => {
                $(target).html(response.content);
                callback?.(response);
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
                $("#limit").html(response.limit);
                $(`#${params.container}`).html(response.content);
                window.location.href = "#top";
            }
        });
    }

    static setLimit(limit) {
        Logger.log(1, 'setLimit', `limit=${limit}`);
        $("#limit").html(limit);
        this.loadGenericAjaxTable(0);
    }
}

/**
 * PlayerSelector Class
 * Manages player selection and badge display functionality
 */
class PlayerSelector {
    /**
     * Sets the current player badge in the UI
     * @param {number} id - Player identifier in the tooltips/initials arrays
     */
    static ids = [];
    static initials = [];
    static tooltips = [];

    static initializeFromDOM() {
        Logger.log(1, 'initializeFromDOM', 'Initializing PlayerSelector from DOM elements.');
        this.ids = this.getDivValue("ids");
        this.initials = this.getDivValue("initials");
        this.tooltips = this.getDivValue("tooltips");

        const selectedPlayerId = $('#hiddenSelectedPlayerId')?.html();
        if (selectedPlayerId && this.ids.length > 0) {
            const playerId = String(selectedPlayerId);
            const id = this.ids.indexOf(playerId);
            if (id !== -1) {
                this.setBadge(id);
            } else {
                Logger.log(5, 'initializeFromDOM', `Player ID ${playerId} not found in available IDs.`);
                this.setBadge(-1); // Or handle as appropriate if playerId not in list
            }
        } else if (this.ids.length === 0 && selectedPlayerId) {
            Logger.log(5, 'initializeFromDOM', 'Player IDs array is empty, cannot set badge based on hiddenSelectedPlayerId.');
            this.setBadge(-1);
        } else {
            Logger.log(5, 'initializeFromDOM', '#hiddenSelectedPlayerId element not found or empty, or no player IDs loaded. Not setting initial badge.');
            this.setBadge(-1); // No player selected or data available
        }
    }

    static getDivValue(divName) {
        Logger.log(2, 'getDivValue', `divName=${divName}`);
        const div = `#${divName}`;
        const str = $(div)?.html();
        if (str) {
            return str.split(';');
        } else {
            Logger.log(5, 'getDivValue', `${div} element not found or empty.`);
            return [];
        }
    }

    static setBadge(id) {
        Logger.log(1, 'setBadge', `id=${id}`);
        const target = '#currentPlayerBadge';

        if (!DOMUtils.exists(target))
            return;

        // Generate badge HTML if valid ID provided
        let badge = "";
        if (id >= 0) {
            const tooltip = this.tooltips[id];
            const initial = this.initials[id];
            badge = `<a title="${tooltip}" data-bs-toggle="tooltip" data-placement="top">
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            ${initial}
                        </span>
                    </a>`;
        }

        $(target).html(badge);
    }

    /**
     * Selects a player and updates the application context
     * 
     * @param {number} userId - User identifier
     * @param {number} playerId - Player identifier
     */
    static select(userId, playerId) {
        Logger.log(1, 'select', `userId=${userId}, playerId=${playerId}`);

        AjaxUtils.request({
            url: 'player/ajax-set-context',
            data: {userId: userId, playerId: playerId},
            successCallback: (response) => {
                console.log(`response=${Object.values(response)}`);
                Logger.log(4, 'select', `response=${Object.values(response)}`);
                // Update badge and redirect
                const playerIds = this.ids;
                const id = playerIds.indexOf(String(playerId));
                this.setBadge(id);
                console.log("************");
                window.location.href = '/frontend/web/';
                console.log("************");
            }
        });
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
        const currentContent = $(target).html();
        $(target).html(currentContent + response.content);
        this._displayToast(response.UUID);
    }

    /**
     * Displays and manages toast lifecycle
     * @param {string} UUID - Unique identifier for the toast
     */
    static _displayToast(UUID) {
        Logger.log(2, '_displayToast', `UUID=${UUID}`);

        const target = `#${UUID}`;
        if (!DOMUtils.exists(target))
            return;

        // Initialize and show Bootstrap toast
        const toast = new bootstrap.Toast($(target));
        toast.show();

        // Cleanup after display
        setTimeout(() => $(target).remove(), 3000);
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

        const inputControl = $(`#user-${role}-${userId}`);
        if (!inputControl)
            return;

        const checked = inputControl.is(":checked");
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

        const inputControl = $(`#access-right-${access}-${id}`);
        if (!inputControl)
            return;

        const checked = inputControl.is(":checked");
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
        $(document).ready(function () {
            if (typeof TableManager !== 'undefined' && TableManager.loadGenericAjaxTable) {
                TableManager.loadGenericAjaxTable(0);
            } else {
                console.error('TableManager or loadGenericAjaxTable method not found.');
            }
        });
    }

    static initNavbarLobby() {
        $(document).ready(function () {
            $('#notificationDropdown').on('show.bs.dropdown', function () {
                let n = $('#notificationCounter').text();
                // Assuming playerId is globally available or accessible here. 
                // This might need adjustment based on how playerId is set in your actual application.
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
        });
    }
}

// Initialize library when DOM is ready
$(document).ready(() => CoreLibrary.init());
