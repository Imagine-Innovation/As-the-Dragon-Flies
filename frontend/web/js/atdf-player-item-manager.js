class PlayerItemManager {
    /**
     * Toggles an item in player's pack
     * @param {number} itemId - Item identifier to toggle
     */
    static togglePack(itemId) {
        Logger.log(1, 'togglePack', `itemId=${itemId}`);

        const inputControl = $(`#pack-${itemId}`);
        if (!inputControl)
            return;

        const checked = inputControl.is(":checked");
        const status = checked ? 1 : 0;

        Logger.log(10, 'togglePack', `checked=${checked}, status=${status}`);

        AjaxUtils.request({
            url: 'player-item/ajax-toggle',
            data: {item_id: itemId, status},
            successCallback: (response) => {
                if (response.error) {
                    inputControl.prop("checked", !checked);
                    ToastManager.show("Error", response.msg, 'error');
                } else {
                    ToastManager.show("Player's pack", response.msg, 'info');
                }
            }
        });
    }
}
