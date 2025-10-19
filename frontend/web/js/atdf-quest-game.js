class VirtualTableTop {
    constructor(options = {}) {
        this.questId = null;
        this.options = {
            ...options
        };
    }

    init(questId) {
        this.questId = questId;

        $(document).ready(() => {
            this.missionDescription(questId);
        });
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
        Logger.log(2, 'missionDescription', `questId=${questId}`);

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
}
