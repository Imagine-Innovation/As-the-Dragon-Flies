/**
 * NotificationHandler Class
 * Manages real-time notifications and updates across different application components
 */
class NotificationHandler {
    /**
     * Event configuration for different notification types
     */
    static eventBroker = {
        'new-message': {
            'tavern': {
                route: 'quest/ajax-get-messages',
                method: 'GET',
                placeholder: 'questChatContent',
                badge: false
            },
            'notificationCounter': {
                route: 'notification/ajax-counter',
                method: 'GET',
                placeholder: 'notificationCounter',
                badge: true
            },
            'notificationList': {
                route: 'notification/ajax-list',
                method: 'GET',
                placeholder: 'notificationList',
                badge: false
            }
        },
        'new-player': {
            'tavern': {
                route: 'quest/ajax-tavern',
                method: 'GET',
                placeholder: 'questTavernPlayersContainer',
                badge: false
            },
            'tavern-message': {
                route: 'quest/ajax-tavern-counter',
                method: 'GET',
                placeholder: 'tavernWelcomeMessage',
                badge: false
            },
            'notificationCounter': {
                route: 'notification/ajax-counter',
                method: 'GET',
                placeholder: 'notificationCounter',
                badge: true
            },
            'notificationList': {
                route: 'notification/ajax-list',
                method: 'GET',
                placeholder: 'notificationList',
                badge: false
            }
        },
        'start-quest': {
            'tavern': {
                route: 'quest/ajax-start',
                method: 'POST',
                placeholder: 'questView',
                badge: false
            },
            'notificationCounter': {
                route: 'notification/ajax-counter',
                method: 'GET',
                placeholder: 'notificationCounter',
                badge: true
            },
            'notificationList': {
                route: 'notification/ajax-list',
                method: 'GET',
                placeholder: 'notificationList',
                badge: false
            }
        }
    };

    /**
     * Initializes the notification handler
     * @param {Object} options - Configuration options
     */
    static init(options) {
        Logger.log(1, 'init', 'Notification Handler Initialization');

        this.pollingInterval = options.pollingInterval || 5000;
        this.userId = options.userId || null;
        this.playerId = options.playerId || null;
        this.questId = options.questId || null;

        this.checkNotifications();
        this.startPolling();
    }

    /**
     * Starts the notification polling system
     */
    static startPolling() {
        Logger.log(1, 'startPolling', `Polling interval: ${this.pollingInterval / 1000} seconds`);
        setInterval(() => this.checkNotifications(), this.pollingInterval);
    }

    /**
     * Checks for new notifications
     */
    static checkNotifications() {
        Logger.log(1, 'checkNotifications', `this.playerId=${this.playerId}`);
        if (!this.playerId)
            return;

        AjaxUtils.request({
            url: 'notification/check',
            method: 'GET',
            data: {playerId},
            successCallback: (response) => {
                if (!response.error) {
                    response.notifications.forEach(notifStr => {
                        const notification = JSON.parse(notifStr);
                        this.handleNotification(notification.type, notification.data);
                    });
                }
            }
        });
    }

    /**
     * Processes notification and triggers appropriate actions
     * @param {string} type - Notification type
     * @param {Object} data - Notification data
     */
    static handleNotification(type, data) {
        Logger.log(2, 'handleNotification', `type=${type}, data=${JSON.stringify(data)}`);
        const requestConfig = this.eventBroker[type];

        if (requestConfig) {
            Object.entries(requestConfig).forEach(([key, config]) => {
                this.executeRequest(config, data);
            });
        }
    }

    /**
     * Executes AJAX request based on configuration
     * @param {Object} config - Request configuration
     * @param {Object} data - Request data
     */
    static executeRequest(config, data) {
        Logger.log(1, 'executeRequest', `config=${JSON.stringify(config)}, data=${JSON.stringify(data)}`);

        const target = `#${config.placeholder}`;
        if (!DOMUtils.exists(target))
            return;

        const fixedData = {
            userId: this.userId,
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
    static _updateTarget(target, response, isBadge) {
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
