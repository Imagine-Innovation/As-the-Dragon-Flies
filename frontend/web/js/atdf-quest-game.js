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
            storyId: $('#hiddenStoryId').html(),
            questId: $('#hiddenQuestId').html(),
            playerId: $('#hiddenPlayerId').html(),
            currentPlayerId: $('#hiddenCurrentPlayerId').html(),
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

        // Update context
        this.questId = questId;

        AjaxUtils.request({
            url: 'game/ajax-actions',
            method: 'GET',
            data: {
                questProgressId: this.context.questProgressId,
                playerId: this.context.playerId
            },
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

    _showModal(modalId) {
        Logger.log(2, '_showModal', `modalId=${modalId}`);
        if (!this.modal) {
            const modalElement = document.querySelector(modalId);
            this.modal = new bootstrap.Modal(modalElement);
        }
        this.modal.show();
    }

    _hideModal() {
        Logger.log(2, '_hideModal', ``);
        if (this.modal) {
            this.modal.hide();
            this.modal = null;
        }
    }

    talk(actionId, replyId) {
        Logger.log(1, 'talk', `actionId=${actionId}, replyId=${replyId}`);
        const target = `#actionFeedback`;
        $(target).html(`Talk: actionId=${actionId}, replyId=${replyId}`);
        this._showModal('#gameModal');
        // Store the current action in the context
        this.context.actionId = actionId;
        this._dialog(replyId);
    }

    reply(replyId) {
        Logger.log(1, 'reply', `replyId=${replyId}`);
        const target = `#actionFeedback`;
        $(target).html(`Reply: replyId=${replyId}`);
        this._dialog(replyId);
    }

    _dialog(replyId) {
        Logger.log(2, '_dialog', `replyId=${replyId}`);
        const target = `#currentAction`;
        if (!DOMUtils.exists(target))
            return;
        AjaxUtils.request({
            url: 'game/ajax-dialog',
            method: 'GET',
            data: {
                ...this.context,
                replyId: replyId
            },
            successCallback: (response) => {
                if (!response.error) {
                    $(target).html(response.content);
                    if (response.text) {
                        this.__speakText(response.text);
                    }
                }
            }
        });
    }

    __speakText(textToRead) {
        Logger.log(3, '__speakText', `textToRead=${textToRead}`);
        // 1. Check browser compatibility
        if ('speechSynthesis' in window) {
            // 2. Create a new instance of SpeechSynthesisUtterance
            const utterance = new SpeechSynthesisUtterance(textToRead);
            utterance.lang = 'fr-FR';
            utterance.rate = 1.1;   // Playback speed (1 is normal speed)
            utterance.pitch = 0.8;  // Pitch/tone (1 is normal pitch)



            speechSynthesis.addEventListener("voiceschanged", () => {
                const voices = speechSynthesis.getVoices();
                for (const voice of voices) {
                    if (voice.lang === 'fr-FR')
                        console.log(`Voice=${voice.name}`);
                    if (voice.name === 'Microsoft Paul - French (France)')
                        utterance.voice = voice;
                }
                // 3. Read the text
                window.speechSynthesis.speak(utterance);
            });
        } else {
            console.error("API Web Speech not supported!");
        }
    }

    makeAction(actionId) {
        Logger.log(1, 'makeAction', `actionId=${actionId}`);
        const target = `#actionFeedback`;
        $(target).html(`Action: actionId=${actionId}`);
    }

    evaluateAction(actionId) {
        Logger.log(1, 'evaluateAction', `actionId=${actionId ?? 'null'}`);

        // Store the current action in the context
        if (actionId)
            this.context.actionId = actionId;

        const target = `#currentAction`;
        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: 'game/ajax-evaluate',
            method: 'POST',
            data: this.context,
            successCallback: (response) => {
                console.log(`evaluateAction callback=${JSON.stringify(response)}`);
                if (!response.error) {
                    this._showModal('#gameModal');
                    $(target).html(response.content);
                }
                this.actions(this.questId);
            }
        });
    }
}
