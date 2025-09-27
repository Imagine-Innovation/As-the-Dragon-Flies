function searchSelect(searchField, ajaxFunction) {
    Logger.log(1, 'searchSelect', `searchField=${searchField}, ajaxFunction=${ajaxFunction}`);
    $(`#${searchField}`).select2({
        ajax: {
            url: `/frontend/web/index.php?r=search/${ajaxFunction}`,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term // search term
                };
            },
            dropdownCssClass: 'form-select',
            processResults: function (data, params) {
                return {
                    results: data.results
                };
            },
            cache: true
        },
        placeholder: 'Search for a dialog',
        minimumInputLength: 3,
        templateResult: formatSearchResult,
        templateSelection: function (data) {
            return data.name ?? data.text;
        }
    });

    function formatSearchResult(result) {
        if (result.loading) {
            return result.text;
        }

        if (result.name) {
            return $(
                    `<div class='select2-result-${searchField} clearfix'>` +
                    `<span class='fw-medium'>${result.name}:</span> ${result.text}` +
                    `</div>`
                    );
        } else {
            return $(`<div class='select2-result-${searchField} clearfix'>${result.text}</div>`);
        }
    }
}

$(document).ready(function () {

    let searchField = 'npc-first_dialog_id';
    if (DOMUtils.exists(`#${searchField}`))
        searchSelect(searchField, 'dialog');

    searchField = 'npc-npc_type_id';
    if (DOMUtils.exists(`#${searchField}`))
        searchSelect(searchField, 'npc-type');

    searchField = 'trap-damage_type_id';
    if (DOMUtils.exists(`#${searchField}`))
        searchSelect(searchField, 'damage-type');

    searchField = 'missionitem-item_id';
    if (DOMUtils.exists(`#${searchField}`))
        searchSelect(searchField, 'item');

    searchField = 'monster-creature_id';
    if (DOMUtils.exists(`#${searchField}`))
        searchSelect(searchField, 'creature');
});
