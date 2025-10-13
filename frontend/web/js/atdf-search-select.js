const formName = $('#hiddenFormName').html();
const imagePath = $('#hiddenImagePath').html();
const parentId = $('#hiddenParentId').html();
const config = [
    {
        form: 'mission',
        params: [
            {
                field: 'mission-image',
                valueType: 'image',
                minChar: 1,
                imagePath: imagePath
            }
        ]
    },
    {
        form: 'passage',
        params: [
            {
                field: 'passage-image',
                valueType: 'image',
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
                valueType: 'dialog',
                minChar: 3,
                imagePath: null
            },
            {
                field: 'npc-image',
                valueType: 'image',
                minChar: 1,
                imagePath: imagePath
            },
            {
                field: 'npc-npc_type_id',
                valueType: 'npc-type',
                minChar: 3,
                imagePath: null
            }
        ]
    },
    {
        form: 'actioninteraction',
        params: [
            {
                field: 'actioninteraction-previous_action_id',
                valueType: 'action',
                minChar: 0,
                imagePath: null
            },
            {
                field: 'actioninteraction-next_action_id',
                valueType: 'action',
                minChar: 0,
                imagePath: null
            }
        ]
    },
    {
        form: 'trap',
        params: [
            {
                field: 'trap-damage_type_id',
                valueType: 'damage-type',
                minChar: 0,
                imagePath: null
            },
            {
                field: 'trap-image',
                valueType: 'image',
                minChar: 0,
                imagePath: imagePath
            }
        ]
    },
    {
        form: 'decoritem',
        params: [
            {
                field: 'decoritem-item_id',
                valueType: 'item',
                minChar: 3,
                imagePath: null
            },
            {
                field: 'decoritem-image',
                valueType: 'image',
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
                valueType: 'creature',
                minChar: 3,
                imagePath: null
            },
            {
                field: 'monster-image',
                valueType: 'image',
                minChar: 1,
                imagePath: imagePath
            }
        ]
    },
    {
        form: 'decor',
        params: [
            {
                field: 'decor-item_id',
                valueType: 'item',
                minChar: 3,
                imagePath: null
            },
            {
                field: 'decor-trap_id',
                valueType: 'trap',
                minChar: 0,
                imagePath: null
            },
            {
                field: 'decor-image',
                valueType: 'image',
                minChar: 1,
                imagePath: imagePath
            }
        ]
    },
    {
        form: 'action',
        params: [
            {
                field: 'action-passage_id',
                valueType: 'passage',
                minChar: 0,
                imagePath: null
            },
            {
                field: 'action-action_type_id',
                valueType: 'action-type',
                minChar: 1,
                imagePath: null
            },
            {
                field: 'action-decor_id',
                valueType: 'decor',
                minChar: 0,
                imagePath: null
            },
            {
                field: 'action-decor_item_id',
                valueType: 'nested-item',
                minChar: 0,
                imagePath: null
            },
            {
                field: 'action-npc_id',
                valueType: 'npc',
                minChar: 0,
                imagePath: null
            },
            {
                field: 'action-reply_id',
                valueType: 'reply',
                minChar: 3,
                imagePath: null
            },
            {
                field: 'action-required_item_id',
                valueType: 'item',
                minChar: 3,
                imagePath: null
            },
            {
                field: 'action-skill_id',
                valueType: 'skill',
                minChar: 0,
                imagePath: null
            },
            {
                field: 'action-trap_id',
                valueType: 'nested-trap',
                minChar: 0,
                imagePath: null
            }
        ]
    }
];

function searchSelect(searchField, valueType, minChar = 3, imagePath = null) {
    Logger.log(1, 'searchSelect', `searchField=${searchField}, valueType=${valueType}, minChar=${minChar}, imagePath=${imagePath}`);
    $(`#${searchField}`).select2({
        ajax: {
            url: `/frontend/web/index.php?r=search/values`,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                const ajaxParams = {
                    valueType: valueType,
                    search: params.term, // search term
                    folder: imagePath,
                    parentId: parentId
                };
                console.log(JSON.stringify(ajaxParams));
                return ajaxParams;
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
        allowClear: true,
        dropdownAutoWidth: true,
        width: '100%',
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
        }
        if (result.name) {
            if (result.text) {
                return $(
                        `<div class='select2-result-${searchField} clearfix'>` +
                        `<span class='fw-medium'>${result.name}:</span> ${result.text}` +
                        `</div>`
                        );
            }
            return $(`<div class='select2-result-${searchField} clearfix'>${result.name}</div>`);
        }
        return $(`<div class='select2-result-${searchField} clearfix'>${result.text}</div>`);
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
        const valueType = param.valueType;
        const minChar = param.minChar;
        const imagePathValue = param.imagePath;

        // Check if the DOM element exists
        if (DOMUtils.exists(`#${searchField}`)) {
            searchSelect(searchField, valueType, minChar, imagePathValue);
        }
    });
}

$(document).ready(function () {
    if (formName)
        initSearchSelect(formName);
});
