function searchSelect(selectId, ajaxFunction) {
    Logger.log(1, 'searchSelect', `selectId=${selectId}, ajaxFunction=${ajaxFunction}`);
//$(".js-example-data-ajax").select2({
    $(`#${selectId}`).select2({
        ajax: {
            url: `/frontend/web/index.php?r=mission/${ajaxFunction}`,
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

        var $container = $(
                `<div class='select2-result-${selectId} clearfix'>` +
                `    <span class='select2-result-${selectId}__text'></span>` +
                `</div>`
                );

        $container.find(`.select2-result-${selectId}__text`).text(result.text);

        return $container;
    }
}

$(document).ready(function () {

    if (DOMUtils.exists('#npc-first_dialog_id'))
        searchSelect('npc-first_dialog_id', 'ajax-search-dialog');

    if (DOMUtils.exists('#npc-npc_type_id'))
        searchSelect('npc-npc_type_id', 'ajax-search-npc-type');
});
