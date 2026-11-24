/**
 * TableFilterManager Class
 * Generic AJAX table loader for tables with value filters (checkboxes, radios
 * or combo boxes as rendered by common\widgets\TableFilter) and a free-text search.
 * Widget managers subclass it to override the toast title or the load behavior.
 */
class TableFilterManager {
    static lastRequestId = 0;
    static lastFilterName = null;
    static lastSearchId = null;
    static toastTitle = 'Table';

    static loadGenericAjaxTable(pageNumber, filterName = null, searchId = null) {
        if (filterName !== null) {
            TableFilterManager.lastFilterName = filterName;
        }
        if (searchId !== null) {
            TableFilterManager.lastSearchId = searchId;
        }
        const resolvedFilterName = TableFilterManager.lastFilterName ?? 'application';
        const resolvedSearchId = TableFilterManager.lastSearchId ?? 'routeSearch';

        Logger.log(1, 'loadGenericAjaxTable', `pageNumber=${pageNumber}, filterName=${resolvedFilterName}, searchId=${resolvedSearchId}`);

        const requestId = ++this.lastRequestId;

        const params = {
            page: pageNumber,
            limit: DOMUtils.getParam("limit", 10),
            container: DOMUtils.getParam("container", "ajaxContainer"),
            route: DOMUtils.getParam("route", null),
            currentTab: DOMUtils.getParam("currentTab"),
            currentId: DOMUtils.getParam("currentId"),
            applications: TableFilterManager.getSelectedApplications(resolvedFilterName),
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
                if (requestId !== this.lastRequestId) {
                    return;
                }
                if (response.error) {
                    ToastManager.show(this.toastTitle, response.msg, 'error');
                    return;
                }
                $(`#${params.container}`).html(response.content);
                window.location.href = "#top";
            },
            errorCallback: (response) => {
                if (requestId !== this.lastRequestId) {
                    return;
                }
                ToastManager.show(this.toastTitle, response.msg, 'error');
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
