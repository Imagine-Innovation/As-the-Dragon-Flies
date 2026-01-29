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
            currentPlayerName: $('#hiddenCurrentPlayerName').html(),
            missionId: $('#hiddenQuestMissionId').html(),
            questProgressId: $('#hiddenQuestProgressId').html(),
            actionId: $('#hiddenQuestActionId').html()
        };
        Logger.log(1, 'init', `context=${JSON.stringify(this.context)}`);

        VirtualTableTop._updateMission(this.context.missionId);
        VirtualTableTop._updateTurn(this.context.playerId, this.context.currentPlayerId, this.context.currentPlayerName);
        VirtualTableTop._updateActions(this.context.playerId, this.context.currentPlayerId, this.context.questProgressId);

    }

    static refresh(questId, sessionId, message = null) {
        Logger.log(1, 'refresh', `questId=${questId}, sessionId=${sessionId}, message=${message}`);
        if (message)
            ToastManager.show('Tavern message', message, 'info');
        // Cannot use the context within a static method
        const playerId = $('#hiddenPlayerId').html();
        VirtualTableTop._updatePlayer(playerId);
        VirtualTableTop._updateQuestMembers(questId);
    }

    static refreshMission(questId, playerId, detail) {
        Logger.log(1, 'refreshMission', `questId=${questId}, playerId=${playerId}, detail=${JSON.stringify(detail)}`);

        const nextPlayer = (playerId === detail.nextPlayerId) ? 'your' : `${detail.nextPlayerName}'s`;
        const toastMessage = `${detail.currentPlayerName} has completed mission “${detail.currentMissionName}”.
 Now it's ${nextPlayer} turn to start mission “${detail.nextMissionName}”`;

        ToastManager.show('Game message', toastMessage, 'info');

        VirtualTableTop._updatePlayer(playerId);
        VirtualTableTop._updateQuestMembers(questId);
        VirtualTableTop._updateMission(detail.nextMissionId);
        VirtualTableTop._updateTurn(playerId, detail.nextPlayerId, detail.nextPlayerName);
        VirtualTableTop._updateActions(playerId, detail.nextPlayerId, detail.nextQuestProgressId);
    }

    static refreshTurn(questId, playerId, detail) {
        Logger.log(1, 'refreshTurn', `questId=${questId}, playerId=${playerId}, detail=${JSON.stringify(detail)}`);

        const nextPlayer = (playerId === detail.nextPlayerId) ? 'your' : `${detail.nextPlayerName}'s`;
        const toastMessage = `${detail.currentPlayerName} has finished his turn. Now it's ${nextPlayer} turn to play.`;

        ToastManager.show('Game message', toastMessage, 'info');

        VirtualTableTop._updatePlayer(playerId);
        VirtualTableTop._updateQuestMembers(questId);
        VirtualTableTop._updateTurn(playerId, detail.nextPlayerId, detail.nextPlayerName);
        VirtualTableTop._updateActions(playerId, detail.nextPlayerId, detail.questProgressId);
    }

    static _updateQuestMembers(questId) {
        Logger.log(2, '_updateQuestMembers', `questId=${questId}`);
        const asideTarget = `#questMembers-aside`;
        if (!DOMUtils.exists(asideTarget))
            return;
        const offcanvasTarget = `#questMembers-offcanvas`;
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

    static _updatePlayer(playerId) {
        Logger.log(2, '_updatePlayer', `playerId=${playerId}`);
        const asideTarget = `#player-aside`;
        if (!DOMUtils.exists(asideTarget))
            return;

        const offcanvasTarget = `#player-offcanvas`;
        if (!DOMUtils.exists(offcanvasTarget))
            return;

        AjaxUtils.request({
            url: 'game/ajax-player',
            method: 'GET',
            data: {id: playerId},
            successCallback: (response) => {
                if (!response.error) {
                    const content = response.content;
                    $(asideTarget).html(content);
                    $(offcanvasTarget).html(content);
                }
            }
        });
    }

    static _updateMission(missionId) {
        Logger.log(2, '_updateMission', `missionId=${missionId}`);
        
        const targetTitle = `#missionTitle`;
        if (!DOMUtils.exists(targetTitle))
            return;

        const targetDescription = `#missionDescription`;
        if (!DOMUtils.exists(targetDescription))
            return;

        AjaxUtils.request({
            url: 'game/ajax-mission',
            method: 'GET',
            data: {missionId: missionId},
            successCallback: (response) => {
                if (!response.error) {
                    const content = response.content;
                    $(targetDescription).html(content);
                    $(targetTitle).html(response.title);
                }
            }
        });
    }

    static _updateTurn(playerId, nextPlayerId, nextPlayerName) {
        Logger.log(2, '_updateTurn', `playerId=${playerId}, nextPlayerId=${nextPlayerId}, nextPlayerName=${nextPlayerName}`);
        const target = `#turnDescription`;
        if (!DOMUtils.exists(target))
            return;

        const nextPlayer = (playerId === nextPlayerId) ? 'your' : `${nextPlayerName}'s`;
        const message = `It's ${nextPlayer} turn to play`;
        $(target).text(message);
    }

    static _updateActions(playerId, currentPlayerId, questProgressId) {
        Logger.log(2, '_updateActions', `playerId=${playerId}, currentPlayerId=${currentPlayerId}, questProgressId=${questProgressId}`);
        const target = `#actionList`;
        if (!DOMUtils.exists(target))
            return;

        if (playerId !== currentPlayerId) {
            // The player is not the one who is playing, 
            // the action card is hidden, and we stop there.
            $(target).addClass('d-none');
            return;
        }
        $(target).removeClass('d-none');

        AjaxUtils.request({
            url: 'game/ajax-actions',
            method: 'GET',
            data: {questProgressId: questProgressId},
            successCallback: (response) => {
                if (!response.error) {
                    const content = response.content;
                    $(target).html(content);
                } else {
                    const content = response.msg;
                    $(target).text(content);
                }
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
        $(target).text(`Talk: actionId=${actionId}, replyId=${replyId}`);
        this._showModal('#gameModal');
        // Store the current action in the context
        this.context.actionId = actionId;
        this._dialog(replyId);
    }

    reply(replyId) {
        Logger.log(1, 'reply', `replyId=${replyId}`);
        const target = `#actionFeedback`;
        $(target).text(`Reply: replyId=${replyId}`);
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
                    Logger.log(2, '_dialog', `response.audio=${response.audio}`);

                    if (response.audio) {
                        this.__play();
                    } else {
                        this.__speakText(response.text);
                    }
                }
            }
        });
    }

    __play() {
        Logger.log(3, '__play', ``);

        const characterLines = document.getElementById('npcLines');

        if (!characterLines) {
            Logger.log(10, '__play', `No audio object found`);
            return;
        }

        // Unmute the audio before playing
        characterLines.muted = false;
        characterLines.volume = 1.0; // Ensure volume is set to maximum

        characterLines.play().catch((error) => {
            console.warn("Autoplay blocked:", error);
        });
    }

    __speakText(textToRead) {
        Logger.log(3, '__speakText', `textToRead=${textToRead}`);

        if (!textToRead)
            return;

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
        $(target).text(`Action: actionId=${actionId}`);
    }

    moveToNextPlayer(questProgressId, nextMissionId) {
        Logger.log(1, 'moveToNextPlayer', `questProgressId=${questProgressId}, nextMissionId=${nextMissionId}`);
        //return;
        AjaxUtils.request({
            url: 'game/ajax-next-turn',
            method: 'POST',
            data: {
                ...this.context,
                questProgressId: questProgressId,
                nextMissionId: nextMissionId
            },
            successCallback: (response) => {
                console.log(`moveToNextPlayer callback=${JSON.stringify(response)}`);
                if (!response.error) {
                    window.location.reload();
                }
            }
        });
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
                // console.log(`evaluateAction callback=${JSON.stringify(response)}`);
                if (!response.error) {
                    console.log(`evaluateAction callback=${JSON.stringify(response)}`);
                    this._showModal('#gameModal');
                    $(target).html(response.content);
                    // update action list
                    VirtualTableTop._updateActions(this.context.playerId, this.context.playerId, this.context.questProgressId);
                }
                VirtualTableTop._updatePlayer(this.context.playerId);
                notificationClient.updateChatMessages();
            }
        });
    }
}
