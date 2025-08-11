/**
 * ShopManager Class
 * Handles shopping cart operations and item management
 */
class ShopManager {

    /***********************************************/
    /*        Page initialization Methods          */
    /***********************************************/

    static initCartPage() {
        Logger.log(1, 'initCartPage', ``);
        $(document).ready(function () {
            ShopManager.getCartInfo();

            // Select all elements with an ID that starts with 'addToCartButton-'
            const buttons = document.querySelectorAll('[id^="cartButton-"]');

            // Loop through each button and add the event listener
            buttons.forEach(button => {
                Logger.log(10, 'initCartPage', `Found button ${button.getAttribute('id')}`);
                button.addEventListener('click', function (event) {
                    Logger.log(10, 'initCartPage', `Click on button ${button.getAttribute('id')}`);
                    // Prevent the default action
                    event.preventDefault();

                    // Retrieve the suffix from the button's ID
                    const suffix = button.getAttribute('id');

                    // Split the ID to extract the operation and item ID
                    const parts = suffix.split('-');
                    const operation = parts[1]; // This will be 'add', 'remove', or 'delete'
                    const itemId = parts[2]; // This will be the item ID (e.g., '20', '22', '120')

                    ShopManager.handleCartOperation(operation, itemId);
                });
            });
        });
    }

    static handleCartOperation(operation, itemId) {
        Logger.log(1, 'handleCartOperation', `operation=${operation}, itemId=${itemId}`);
        AjaxUtils.request({
            url: `player-cart/ajax-${operation}`,
            data: {itemId},
            successCallback: (response) => {
                if (response.error) {
                    $('#noFundLabel').html(response.msg);
                    $('#somethingWrongModal-hiddenButton').click();
                } else {
                    ShopManager.getCartInfo();
                    ShopManager._getItemCount(itemId);
                }
                ToastManager.show('Shop', response.msg, response.error ? 'error' : 'info');
            }
        });
    }

    /**
     * Adds an item to shopping cart
     * @param {number} itemId - Item identifier to add
     * @param {number} quantity - Quantity to remove
     */
    static addToCart(itemId, quantity = 1) {
        Logger.log(1, 'addToCart', `itemId=${itemId}, quantity=${quantity}`);

        AjaxUtils.request({
            url: 'player-cart/ajax-add',
            data: {itemId, quantity},
            successCallback: (response) => {
                if (response.error) {
                    $('#noFundLabel').html(response.msg);
                    $('#somethingWrongModal-hiddenButton').click();
                } else {
                    this.getCartInfo();
                    this._getItemCount(itemId);
                }
                ToastManager.show('Shop', response.msg, response.error ? 'error' : 'info');
            }
        });
    }

    /**
     * Removes items from shopping cart
     * @param {number} itemId - Item identifier to remove
     * @param {number} quantity - Quantity to remove
     */
    static removeFromCart(itemId, quantity = 1) {
        Logger.log(1, 'removeFromCart', `itemId=${itemId}, quantity=${quantity}`);

        AjaxUtils.request({
            url: 'player-cart/ajax-remove',
            data: {itemId, quantity},
            successCallback: (response) => {
                if (response.error) {
                    $('#noFundLabel').html(response.msg);
                    $('#somethingWrongModal-hiddenButton').click();
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
     * Delete one item from shopping cart
     * @param {number} itemId - Item identifier to remove
     */
    static deleteFromCart(itemId) {
        Logger.log(1, 'deleteFromCart', `itemId=${itemId}`);

        AjaxUtils.request({
            url: 'player-cart/ajax-delete',
            data: {itemId},
            successCallback: (response) => {
                if (response.error) {
                    $('#noFundLabel').html(response.msg);
                    $('#somethingWrongModal-hiddenButton').click();
                } else {
                    this.getCartInfo();
                    this._getItemCount(itemId);
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
        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: 'player-cart/ajax-item-count',
            data: {itemId},
            successCallback: (response) => {
                if (!response.error) {
                    const n = response.count;
                    if (n === 0) {
                        // an item is no more in the cart, refresh the whole page
                        location.reload();
                    } else {
                        $(target).html(n);
                    }
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
        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: 'player-cart/ajax-info',
            successCallback: (response) => {
                if (!response.error) {
                    Logger.log(1, 'getCartInfo', JSON.stringify(response));
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
