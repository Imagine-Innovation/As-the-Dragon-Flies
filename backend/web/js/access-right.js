/**
 * AccessRightManager Class
 * Handles loading of the access rights table, with application and route filters
 */
class AccessRightManager {
    static lastRequestId = 0;
    static lastFilterName = null;
    static lastSearchId = null;

    static loadGenericAjaxTable(pageNumber, filterName = null, searchId = null) {
        if (filterName !== null) {
            AccessRightManager.lastFilterName = filterName;
        }
        if (searchId !== null) {
            AccessRightManager.lastSearchId = searchId;
        }
        const resolvedFilterName = AccessRightManager.lastFilterName ?? 'application';
        const resolvedSearchId = AccessRightManager.lastSearchId ?? 'routeSearch';

        Logger.log(1, 'loadGenericAjaxTable', `pageNumber=${pageNumber}, filterName=${resolvedFilterName}, searchId=${resolvedSearchId}`);

        const requestId = ++AccessRightManager.lastRequestId;

        const params = {
            page: pageNumber,
            limit: DOMUtils.getParam("limit", 10),
            container: DOMUtils.getParam("container", "ajaxContainer"),
            route: DOMUtils.getParam("route", null),
            currentTab: DOMUtils.getParam("currentTab"),
            currentId: DOMUtils.getParam("currentId"),
            applications: AccessRightManager.getSelectedApplications(resolvedFilterName),
            search: $(`#${resolvedSearchId}`).val() ?? ''
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
                if (requestId !== AccessRightManager.lastRequestId) {
                    return;
                }
                if (response.error) {
                    ToastManager.show('Access rights', response.msg, 'error');
                    return;
                }
                $(`#${params.container}`).html(response.content);
                window.location.href = "#top";
            },
            errorCallback: (response) => {
                if (requestId !== AccessRightManager.lastRequestId) {
                    return;
                }
                ToastManager.show('Access rights', response.msg, 'error');
            }
        });
    }

    /**
     * Returns the list of selected values, from checked boxes and selected
     * options of the filter controls bearing the given name
     * @param {string} filterName
     * @returns {string[]}
     */
    static getSelectedApplications(filterName = 'application') {
        return $(`input[name="${filterName}"]:checked, select[name="${filterName}"] option:selected`)
                .map((_, element) => element.value)
                .get()
                .filter((value) => value !== '');
    }

    static setLimit(limit) {
        Logger.log(1, 'setLimit', `limit=${limit}`);
        $("#limit").html(limit);
        this.loadGenericAjaxTable(0);
    }
}
