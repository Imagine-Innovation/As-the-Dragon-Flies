/**
 * AccessRightManager Class
 * Handles loading of the access rights table, with application and route filters
 */
class AccessRightManager {
    static loadGenericAjaxTable(pageNumber) {
        Logger.log(1, 'loadGenericAjaxTable', `pageNumber=${pageNumber}`);

        const params = {
            page: pageNumber,
            limit: DOMUtils.getParam("limit", 10),
            container: DOMUtils.getParam("container", "ajaxContainer"),
            route: DOMUtils.getParam("route", null),
            currentTab: DOMUtils.getParam("currentTab"),
            currentId: DOMUtils.getParam("currentId"),
            applications: AccessRightManager.getSelectedApplications(),
            search: $('#routeSearch').val() ?? ''
        };

        // Log parameters
        Object.entries(params).forEach(([key, value]) => {
            Logger.log(10, 'loadGenericAjaxTable', `${key}=${value}`);
        });

        AjaxUtils.request({
            url: params.route,
            data: params,
            successCallback: (response) => {
                Logger.log(4, 'loadGenericAjaxTable', `response=${Object.values(response)}`);
                $("#limit").html(response.limit);
                $(`#${params.container}`).html(response.content);
                window.location.href = "#top";
            }
        });
    }

    /**
     * Returns the list of checked application values
     * @returns {string[]}
     */
    static getSelectedApplications() {
        return $('input[name="application"]:checked')
                .map((_, checkbox) => checkbox.value)
                .get();
    }

    static setLimit(limit) {
        Logger.log(1, 'setLimit', `limit=${limit}`);
        $("#limit").html(limit);
        this.loadGenericAjaxTable(0);
    }
}
