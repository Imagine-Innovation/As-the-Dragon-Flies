/**
 * QuestManager Class
 * Handles quest-related operations including chat messaging and updates
 */
class QuestManager {
    /**
     * Adds a new message to the quest chat
     * @param {number} questId - Quest identifier
     * @param {number} playerId - Message sender identifier
     * @param {string} message - Message content
     */
    static addMessage(questId, playerId, message) {
        Logger.log(1, 'addMessage', `questId=${questId}, playerId=${playerId}, message=${message}`);

        const target = '#questChatContent';
        if (!DOMUtils.exists(target))
            return;

        // Calculate timestamp for message grouping
        const timestamp = Math.floor(Date.now() / 1000);
        const roundedTime = Math.floor(timestamp / 60) * 60;
        const timeDiv = `#quest-chat-${roundedTime}`;

        AjaxUtils.request({
            url: 'quest/ajax-new-message',
            data: {
                questId,
                playerId,
                message,
                ts: timestamp
            },
            successCallback: (response) => {
                this._updateChatDisplay(response, timeDiv, target);
            }
        });
    }

    /**
     * Updates chat display with new message content
     * @param {Object} response - Server response with message content
     * @param {string} timeDiv - Time-based div selector
     * @param {string} target - Target container selector
     */
    static _updateChatDisplay(response, timeDiv, target) {
        if ($(timeDiv).length) {
            $(timeDiv).html(response.content);
        } else {
            $(target).prepend(response.content);
            this._resetChatInput();
        }
    }

    /**
     * Resets chat input and scrolls to latest message
     */
    static _resetChatInput() {
        $('#questChatNewMessage').val('');
        $('.messages__content').scrollTop(0);
    }
}
