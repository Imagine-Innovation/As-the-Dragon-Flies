/**
 * ButtonManager Class
 * Handles action buttons click events
 */
class ActionButtonManager {

    /***********************************************/
    /*        Page initialization Methods          */
    /***********************************************/

    static initActionButton() {
        Logger.log(1, 'initActionButton', ``);
        $(document).ready(function () {

            // Select all elements with an ID that starts with 'actionButton-'
            const buttons = document.querySelectorAll('[id^="actionButton-"]');

            // Loop through each button and add the event listener
            buttons.forEach(button => {
                button.addEventListener('click', function (event) {
                    Logger.log(10, 'initActionButton', `Click on button ${button.getAttribute('id')}`);
                    // Prevent the default action
                    event.preventDefault();

                    // Retrieve the suffix from the button's ID
                    const suffix = button.getAttribute('id');

                    // Split the ID to extract the operation and item ID
                    var parts = suffix.split('-');
                    var controller = parts[1]; // This will be 'add', 'remove', or 'delete'
                    var action = parts[2]; // This will be 'add', 'remove', or 'delete'
                    var id = parts[3]; // This will be the item ID (e.g., '20', '22', '120')

                    // You can now use the operation and id to perform the desired action
                    // For example, call a function to handle the cart operation
                    ActionButtonManager.handleAction(controller, action, id);
                });
            });
        });
    }

    static handleAction(controller, action, id) {
        Logger.log(1, 'handleAction', `controller=${controller}, action=${action}, id=${id}`);
        AjaxUtils.request({
            url: `${controller}/${action}`,
            data: {id},
            successCallback: (response) => {
                ToastManager.show('Shop', response.msg, response.error ? 'error' : 'info');
            }
        });
    }
}
