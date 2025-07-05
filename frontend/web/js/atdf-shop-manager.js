/**
 * ShopManager Class
 * Handles shopping cart operations and item management
 */
class ShopManager {
    
    /***********************************************/
    /*        Page initialization Methods          */
    /***********************************************/

    static initCartPage() {
        $(document).ready(function () {
            if (typeof ShopManager !== 'undefined' && ShopManager.getCartInfo) {
                ShopManager.getCartInfo();
            } else {
                console.error('ShopManager or getCartInfo method not found.');
            }
        });
    }

    static initShopPage(userId, initialPlayerId, currentSessionPlayerId) {
        $(document).ready(function () {
            if (typeof ShopManager !== 'undefined' && ShopManager.getCartInfo) {
                ShopManager.getCartInfo();
            } else {
                console.error('ShopManager or getCartInfo method not found.');
            }

            if (typeof PlayerSelector !== 'undefined' && PlayerSelector.select) {
                if (currentSessionPlayerId) { // A player is set in session
                    if (initialPlayerId && currentSessionPlayerId !== initialPlayerId) {
                        // Player in session is different from URL param, prioritize session
                        // Potentially, could also choose to prioritize URL param by calling:
                        // PlayerSelector.select(userId, initialPlayerId);
                        // For now, we assume session is the correct current context if it exists
                         console.log('Player in session, no select action needed unless URL param forces a change.');
                    }
                } else { // No player in session
                    if (initialPlayerId) { // Player ID from URL
                        PlayerSelector.select(userId, initialPlayerId);
                    } else { // No player in session and no ID from URL
                        if ($('#showCurrentPlayerModal-button').length) {
                            $('#showCurrentPlayerModal-button').click();
                        } else {
                            console.error('#showCurrentPlayerModal-button not found.');
                        }
                    }
                }
            } else {
                console.error('PlayerSelector or select method not found.');
            }
        });
    }

    /**
     * Adds an item to shopping cart
     * @param {number} itemId - Item identifier to add
     */
    static addToCart(itemId) {
        Logger.log(1, 'addToCart', `itemId=${itemId}`);

        AjaxUtils.request({
            url: 'player-cart/ajax-add',
            data: { itemId },
            successCallback: (response) => {
                if (response.error) {
                    $('#noFundLabel').html(response.msg);
                    $('#hiddenSomethingWrongModelButton').click();
                } else {
                    this.getCartInfo();
                    this._getItemCount(itemId);
                    ToastManager.show('Shop', response.msg, 'info');
                }
            }
        });
    }

    /**
     * Removes items from shopping cart
     * @param {number} itemId - Item identifier to remove
     * @param {number} quantity - Quantity to remove
     */
    static removeFromCart(itemId, quantity) {
        Logger.log(1, 'removeFromCart', `itemId=${itemId}, quantity=${quantity}`);

        AjaxUtils.request({
            url: 'player-cart/ajax-remove',
            data: { itemId, quantity },
            successCallback: (response) => {
                if (response.error) {
                    $('#noFundLabel').html(response.msg);
                    $('#hiddenSomethingWrongModelButton').click();
                } else {
                    if (document.title === 'Cart') {
                        location.reload();
                    } else {
                        this.getCartInfo();
                        this._getItemCount(itemId);
                    }
                }
                ToastManager.show('Shop', response.msg, response.error ? 'error' : 'info');
            }
        });
    }

    /**
     * Gets item count in cart
     * @param {number} itemId - Item identifier to check
     */
    static _getItemCount(itemId) {
        Logger.log(2, '_getItemCount', `itemId=${itemId}`);
        
        const target = `#cartCount-${itemId}`;
        if (!DOMUtils.exists(target)) return;

        AjaxUtils.request({
            url: 'player-cart/ajax-item-count',
            data: { itemId },
            successCallback: (response) => {
                if (!response.error) {
                    $(target).html(response.count);
                }
            }
        });
    }

    /**
     * Updates cart information display
     */
    static getCartInfo() {
        Logger.log(1, 'getCartInfo', '');
        
        const target = '#cartItemCount';
        if (!DOMUtils.exists(target)) return;

        AjaxUtils.request({
            url: 'player-cart/ajax-info',
            successCallback: (response) => {
                if (!response.error) {
                    this._updateCartDisplay(response);
                }
            }
        });
    }

    /**
     * Updates cart display elements
     * @param {Object} data - Cart display data
     */
    static _updateCartDisplay(data) {
        $('#cartItemCount').html(data.count);
        $('#cartDisplay')?.html(data.cartString);
        $('#cartValueString')?.html(data.cartValueString);
        $('#purseContent')?.html(data.purseString);
    }

    /**
     * Validates and processes cart
     */
    static validateCart() {
        Logger.log(1, 'validateCart', '');

        AjaxUtils.request({
            url: 'player-cart/ajax-validate',
            successCallback: (response) => {
                if (!response.error) {
                    location.reload();
                }
                ToastManager.show('Shop', response.msg, response.error ? 'error' : 'info');
            }
        });
    }
}
