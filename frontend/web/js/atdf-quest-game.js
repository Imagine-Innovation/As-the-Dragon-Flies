class VirtualTableTop {
    constructor(options = {}) {
        this.questId = null;
        this.playerId = null;
        this.missionId = null;
        this.questProgressId = null;
        this.options = {
            ...options
        };
    }

    init() {
        this.questId = $('#hiddenQuestId').html();
        this.playerId = $('#hiddenCurrentPlayerId').html();
        this.missionId = $('#hiddenQuestMissionId').html();
        this.questProgressId = $('#hiddenQuestProgressId').html();
        console.log("");
        console.log("");
        console.log(`VirtualTableTop.init() => questId=${this.questId}, playerId=${this.playerId}, missionId=${this.missionId}, questProgressId=${this.questProgressId}`);
        console.log("");
        console.log("");
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
            data: {questId: questId},
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

    talk(replyId) {
        Logger.log(1, 'talk', `replyId=${replyId}`);
        const target = `#actionFeedback`;
        $(target).html(`Talk: replyId=${replyId}`);
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
