const formName = $('#hiddenFormName').html();
const imagePath = $('#hiddenImagePath').html();
const config = [
    {
        form: 'mission',
        params: [
            {
                field: 'mission-image',
                ajax: 'image',
                minChar: 1,
                imagePath: imagePath
            }
        ]
    },
    {
        form: 'npc',
        params: [
            {
                field: 'npc-first_dialog_id',
                ajax: 'dialog',
                minChar: 3,
                imagePath: null
            },
            {
                field: 'npc-image',
                ajax: 'image',
                minChar: 1,
                imagePath: imagePath
            },
            {
                field: 'npc-npc_type_id',
                ajax: 'npc-type',
                minChar: 3,
                imagePath: null
            }
        ]
    },
    {
        form: 'trap',
        params: [
            {
                field: 'trap-damage_type_id',
                ajax: 'damage-type',
                minChar: 3,
                imagePath: null
            },
            {
                field: 'trap-image',
                ajax: 'image',
                minChar: 1,
                imagePath: imagePath
            }
        ]
    },
    {
        form: 'missionitem',
        params: [
            {
                field: 'missionitem-item_id',
                ajax: 'item',
                minChar: 3,
                imagePath: null
            },
            {
                field: 'missionitem-image',
                ajax: 'image',
                minChar: 1,
                imagePath: imagePath
            }
        ]
    },
    {
        form: 'monster',
        params: [
            {
                field: 'monster-creature_id',
                ajax: 'creature',
                minChar: 3,
                imagePath: null
            },
            {
                field: 'monster-image',
                ajax: 'image',
                minChar: 1,
                imagePath: imagePath
            }
        ]
    }
];

function searchSelect(searchField, ajaxFunction, minChar = 3, imagePath = null) {
    Logger.log(1, 'searchSelect', `searchField=${searchField}, ajaxFunction=${ajaxFunction}`);
    $(`#${searchField}`).select2({
        ajax: {
            url: `/frontend/web/index.php?r=search/${ajaxFunction}`,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    search: params.term, // search term
                    folder: imagePath
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
        minimumInputLength: minChar,
        templateResult: formatSearchResult,
        templateSelection: function (data) {
            return imagePath ? data.id : data.name ?? data.text;
        }
    });

    function formatSearchResult(result) {
        if (result.loading) {
            return result.text;
        }

        if (imagePath) {
            return $(
                    `<div class='select2-result-repository clearfix'>` +
                    `    <span><img src="${result.img}" style="max-height: 50px;"/></span> ${result.text}` +
                    `</div>`
                    );
        } else {
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
}

function initSearchSelect(formName) {
    // Find the form configuration that matches the formName
    const formConfig = config.find(form => form.form === formName);

    if (!formConfig) {
        console.error(`Form '${formName}' not found in config.`);
        return;
    }

    // Iterate over each parameter in the form configuration
    formConfig.params.forEach(param => {
        const searchField = param.field;
        const ajaxType = param.ajax;
        const minChar = param.minChar;
        const imagePathValue = param.imagePath;

        // Check if the DOM element exists
        if (DOMUtils.exists(`#${searchField}`)) {
            searchSelect(searchField, ajaxType, minChar, imagePathValue);
        }
    });
}

$(document).ready(function () {
    if (formName)
        initSearchSelect(formName);
});
