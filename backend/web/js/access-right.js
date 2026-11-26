/**
 * AccessRightManager Class
 * Handles loading of the access rights table with application and route filters,
 * delegating the generic AJAX table loading to TableFilterManager (common/web/js/table-filter.js)
 */
class AccessRightManager extends TableFilterManager {
    static toastTitle = 'Access rights';
}
