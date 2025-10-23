class VirtualTableTop {
    constructor(options = {}) {
        this.context = {};
        this.options = {
            ...options
        };
    }

    init() {
        this.context = {
            questId: $('#hiddenQuestId').html(),
            playerId: $('#hiddenCurrentPlayerId').html(),
            missionId: $('#hiddenQuestMissionId').html(),
            questProgressId: $('#hiddenQuestProgressId').html(),
            actionId: $('#hiddenQuestActionId').html()
        };
    }

    static refresh(questId, sessionId, message = null) {
        Logger.log(1, 'refresh', `questId=${questId}, sessionId=${sessionId}, message=${message}`);

        if (message)
            ToastManager.show('Tavern message', message, 'info');

        VirtualTableTop._updateQuestMembers(questId);
    }

    static _updateQuestMembers(questId) {
        Logger.log(2, '_updateQuestMembers', `questId=${questId}`);

        const asideTarget = `#questMembers`;
        if (!DOMUtils.exists(asideTarget))
            return;

        const offcanvasTarget = `#offcanvasQuestMembers`;
        if (!DOMUtils.exists(offcanvasTarget))
            return;

        AjaxUtils.request({
            url: 'quest/ajax-quest-members',
            method: 'GET',
            data: {questId: questId, render: 'game-members'},
            successCallback: (response) => {
                if (!response.error) {
                    const content = response.content;
                    $(asideTarget).html(content);
                    $(offcanvasTarget).html(content);
                }
            }
        });

    }

    missionDescription(questId) {
        Logger.log(1, 'missionDescription', `questId=${questId}`);

        const target = `#missionDescription`;
        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: 'game/ajax-mission',
            method: 'GET',
            data: this.context,
            successCallback: (response) => {
                if (!response.error) {
                    const content = response.content;
                    $(target).html(content);
                }
            }
        });

    }

    actions(questId) {
        Logger.log(2, 'actions', `questId=${questId}`);

        const target = `#actionList`;
        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: 'game/ajax-actions',
            method: 'GET',
            data: {questId: questId},
            successCallback: (response) => {
                let content = `???`;
                if (!response.error) {
                    content = response.content;
                } else {
                    content = response.msg;
                }
                $(target).html(content);
            }
        });
    }

    talk(actionId, replyId) {
        Logger.log(1, 'talk', `actionId=${actionId}, replyId=${replyId}`);
        const target = `#actionFeedback`;
        $(target).html(`Talk: actionId=${actionId}, replyId=${replyId}`);

        // Show the modal
        const modalElement = `#npcDialogModal`;
        const modal = new bootstrap.Modal(modalElement);
        modal.show();
        
        this._dialog(actionId, replyId);
    }

    _dialog(actionId, replyId) {
        Logger.log(2, '_dialog', `actionId=${actionId}, replyId=${replyId}`);

        const previousTarget = `#previousDialogs`;
        if (!DOMUtils.exists(previousTarget))
            return;

        const currentTarget = `#currentDialog`;
        if (!DOMUtils.exists(currentTarget))
            return;

        AjaxUtils.request({
            url: 'game/ajax-dialog',
            method: 'GET',
            data: {
                actionId: actionId,
                replyId: replyId,
                ...this.context},
            successCallback: (response) => {
                if (!response.error) {
                    $(previousTarget).html(response.previousContent);
                    $(currentTarget).html(response.nextContent);
                }
            }
        });
    }

    makeAction(actionId) {
        Logger.log(1, 'makeAction', `actionId=${actionId}`);
        const target = `#actionFeedback`;
        $(target).html(`Action: actionId=${actionId}`);
    }

    nextDialog(nexDialogId) {
        Logger.log(1, 'nextDialog', `nexDialogId=${nexDialogId}`);
        return;
    }

    evaluateAction() {
        Logger.log(1, 'evaluateAction', ``);
        return;
    }
}
