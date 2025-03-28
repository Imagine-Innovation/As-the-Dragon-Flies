/**
 * ItemManager Class
 * Handles item-related operations and tab management
 */
class ItemManager {
    /**
     * Loads and displays items for a specific type
     * @param {number} itemType - Type of items to load
     */
    static loadTypeTab(itemType) {
        Logger.log(1, 'loadTypeTab', `itemType=${itemType}`);

        $("#container").html(`ajax-${itemType}`);
        $("#currentTab").html(itemType);

        TableManager.loadGenericAjaxTable(0);
    }

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

    /**
     * 
     * @param {number} categoryId
     * @returns {string}
     */
    static getCategoryItems(categoryId) {
        Logger.log(1, 'getCategoryItems', `categoryId=${categoryId}`);

        AjaxUtils.request({
            url: 'item/ajax-category',
            data: {categoryId: categoryId},
            successCallback: (response) => {
                return response.content;
            }
        });
    }
}
