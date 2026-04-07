class VirtualTableTop {
    constructor(options = {}) {
        this.context = {};
        this.modal = null;
        this.options = {
            ...options
        };
    }

    init() {
        this.context = {
            questId: $('#hiddenQuestId').html(),
            playerId: $('#hiddenPlayerId').html(),
        };
        Logger.log(1, 'init', `context=${JSON.stringify(this.context)}`);
    }

    refresh(questId, sessionId, message = null) {
        Logger.log(1, 'refresh', `questId=${questId}, sessionId=${sessionId}, message=${message}`);

        if (message)
            ToastManager.show('Tavern message', message, 'info');

        this._updateQuestMembers(questId);
        this._updateWelcomeMessages(questId);
    }

    _updateQuestMembers(questId) {
        Logger.log(2, '_updateQuestMembers', `questId=${questId}`);

        const target = `#tavernPlayersContainer`;
        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: 'quest/ajax-quest-members',
            method: 'GET',
            data: {questId: questId, render: 'tavern-members'},
            successCallback: (response) => {
                if (!response.error) {
                    $(target).html(response.content);
                    this._checkIfQuestCanStart(questId, this.context.sessionId);
                }
            }
        });

    }

    _updateWelcomeMessages(questId) {
        Logger.log(1, '_updateWelcomeMessages', `questId=${questId}`);
        let target = `#tavernWelcomeMessage`;
        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: 'quest/ajax-welcome-messages',
            method: 'GET',
            data: {questId: questId},
            successCallback: (response) => {
                if (!response.error) {
                    target = `#tavernWelcomeMessage`;
                    if (DOMUtils.exists(target))
                        $(target).html(response.welcomeMessage);

                    target = `#tavernMissingPlayers`;
                    if (DOMUtils.exists(target))
                        $(target).html(response.missingPlayers);

                    target = `#tavernMissingClasses`;
                    if (DOMUtils.exists(target))
                        $(target).html(response.missingClasses);
                }
            }
        });

    }

    _checkIfQuestCanStart(questId, sessionId) {
        Logger.log(2, '_checkIfQuestCanStart', `questId=${questId}, sessionId=${sessionId}`);

        const button = `#startQuestButton`;
        if (!DOMUtils.exists(button))
            return;

        AjaxUtils.request({
            url: 'quest/ajax-can-start',
            data: {sessionId: sessionId},
            successCallback: (response) => {
                Logger.log(3, '_checkIfQuestCanStart', `Can start? ${JSON.stringify(response)}`);
                if (response.canStart) {
                    $(button).removeClass('d-none');
                } else {
                    $(button).addClass('d-none');
                }
            }
        });
    }
}
