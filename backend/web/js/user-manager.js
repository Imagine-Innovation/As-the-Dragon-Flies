/**
 * UserManager Class
 * Handles user role management and access rights
 */
class UserManager {
    /**
     * Updates user role status
     * @param {number} userId - User identifier
     * @param {string} role - Role to set
     */
    static setRole(userId, role) {
        Logger.log(1, 'setRole', `userId=${userId}, role=${role}`);

        const inputControl = $(`#user-${role}-${userId}`);
        if (!inputControl)
            return;

        const checked = inputControl.is(":checked");
        const status = checked ? 1 : 0;

        Logger.log(10, 'setRole', `checked=${checked}, status=${status}`);

        AjaxUtils.request({
            url: 'user/ajax-set-role',
            data: {id: userId, role, status},
            successCallback: (response) => {
                ToastManager.show(
                        response.error ? "Error" : "User role",
                        response.msg,
                        response.error ? 'error' : 'info'
                        );
            }
        });
    }

    /**
     * Updates access right status
     * @param {number} id - Access right identifier
     * @param {string} access - Access type to set
     */
    static setAccessRight(id, access) {
        Logger.log(1, 'setAccessRight', `id=${id}, access=${access}`);

        const inputControl = $(`#access-right-${access}-${id}`);
        if (!inputControl)
            return;

        const checked = inputControl.is(":checked");
        const status = checked ? 1 : 0;

        Logger.log(10, 'setAccessRight', `checked=${checked}, status=${status}`);

        AjaxUtils.request({
            url: 'access-right/ajax-set-access-right',
            data: {id, access, status},
            successCallback: (response) => {
                ToastManager.show(
                        response.error ? "Error" : "Access right",
                        response.msg,
                        response.error ? 'error' : 'info'
                        );
            }
        });
    }
}
