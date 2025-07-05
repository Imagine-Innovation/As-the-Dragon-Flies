class PlayerBuilder {

    /***********************************************/
    /*        Hight level builder functions        */
    /***********************************************/

    /**
     * Updates a player property and triggers related actions
     * @param {string} prop - Property name to update
     * @param {string|number} val - New value for the property
     */
    static setProperty(prop, val) {
        Logger.log(1, 'setProperty', `prop=${prop}, val=${val}`);
        // Update the property value in the DOM
        $(`#playerbuilder-${prop}`).val(val);

        // Handle specific property-related actions
        const actions = {
            'gender': () => {
                this.loadRandomNames();
                this.loadAdvancedProperties('images', 'ajaxAvatarChoice');
            },
            'age': () => this._setAge(val),
            'race_id': () => this.loadAdvancedProperties('all'),
            'class_id': () => this.loadAdvancedProperties('all')
        };

        actions[prop]?.();

        // Update progress after any property change
        this.updateProgress();
    }

    /**
     * Loads advanced character properties based on current selections
     * @param {string} property - Property type to load ('all' or specific property name)
     * @param {string} div - The div id where to store the ajax response
     * 
     */
    static loadAdvancedProperties(property, div) {
        Logger.log(1, 'loadAdvancedProperties', `property=${property}, div=${div}`);

        // Gather all required parameters
        const params = {
            playerId: $('#hiddenPlayerId').html(),
            raceId: $('#playerbuilder-race_id').val(),
            classId: $('#playerbuilder-class_id').val(),
            backgroundId: $('#playerbuilder-background_id').val(),
            gender: $('#playerbuilder-gender').val(),
            imageId: $('#playerbuilder-image_id').val()
        };

        // Only proceed if mandatory parameters are present
        if (params.playerId) {
            AjaxUtils.getContent({
                target: `#${div}` ?? `#ajax-${property}`,
                url: `player-builder/ajax-${property}`,
                data: params
            });
        }
    }

    /**
     * Updates the character creation progress bar
     */
    static updateProgress() {
        Logger.log(1, 'updateProgress', '');
        const target = '#builderProgressBar';

        if (!DOMUtils.exists(target))
            return;

        // Define required properties for completion
        const playerId = $('#hiddenPlayerId').html();
        const properties = playerId === '' ?
                ['race_id', 'class_id', 'background_id', 'history_id'] :
                ['alignment_id', 'image_id', 'name', 'gender', 'age', 'abilities', 'skills', 'items'];

        // Count completed properties
        const completedProperties = properties.filter(
                property => $(`#playerbuilder-${property}`).val()
        ).length;

        // Calculate and update progress percentage
        const progress = Math.round((completedProperties / properties.length) * 100);
        Logger.log(10, 'updateProgress', `completedProperties=${completedProperties}, properties.length=${properties.length}`);
        Logger.log(10, 'updateProgress', `progress=${progress}`);
        $(target)
                .css('width', `${progress}%`)
                .text(`${progress}%`);

        // Trigger save if complete
        if (progress === 100) {
            if (playerId) {
                $('#showValidateModal-hiddenButton').click();
            } else {
                $('#showSaveModal-hiddenButton').click();
            }
        }
    }


    /***********************************************/
    /*        Page initialization Methods          */
    /***********************************************/

    static initCreatePage(firstTabWizard) {
        $(document).ready(function () {
            PlayerBuilder.initWizard(firstTabWizard);
            PlayerBuilder.updateProgress();
        });
    }

    static initUpdatePage(firstTabWizard) {
        $(document).ready(function () {
            PlayerBuilder.initWizard(firstTabWizard);
            PlayerBuilder.updateProgress();
            PlayerBuilder.loadAdvancedProperties('images', 'ajaxAvatarChoice');

            $('#equipmentModal').on('click', '#exitEquipmentModal-button', function () {
                const selectedValue = $('input[name="initialEquipmentRadio"]:checked').val();
                if (selectedValue) {
                    const [choice, ...itemIds] = selectedValue.split(',');
                    $('#closeEquipmentModal-hiddenButton').click();
                    PlayerBuilder.setCategoryItem(choice, itemIds);
                }
            });
        });
    }

    static initDescriptionTab(gender, alignmentId, age) {
        $(document).ready(function () {
            PlayerBuilder.loadRandomNames();
            PlayerBuilder.loadAges(age);

            if (gender) {
                $('#gender' + gender).prop('checked', true);
            }
            if (alignmentId) {
                $('#alignment' + alignmentId).prop('checked', true);
            }

            $('a.bi-arrow-repeat').click(function (event) {
                event.preventDefault();
                PlayerBuilder.loadRandomNames();
            });
        });
    }

    static initSkillsTab() {
        $(document).ready(function () {
            PlayerBuilder.loadAdvancedProperties('skills', 'ajaxSkills');
            $('a.bi-arrow-repeat').click(function (event) {
                event.preventDefault();
                PlayerBuilder.loadAdvancedProperties('traits', 'ajaxTraits');
            });
        });
    }

    static initAbilitiesTab() {
        $(document).ready(function () {
            if (typeof ChartDrawer !== 'undefined' && ChartDrawer.drawAbilityCharts) {
                ChartDrawer.drawAbilityCharts();
            } else {
                console.error('ChartDrawer or drawAbilityCharts method not found.');
            }
        });
    }

    static initAvatarTab() {
        $(document).ready(function () {
            PlayerBuilder.loadAdvancedProperties('images', 'ajaxAvatarChoice');
        });
    }
    
    static initEndowmentTab(category, choices, endowments) {
        $(document).ready(function () {
            if (category) {
                 PlayerBuilder.chooseBackgroundEquipment(category);
            }
            for (let choice = 1; choice <= choices; choice++) {
                if (endowments[choice] && endowments[choice][1] && Object.keys(endowments[choice]).length === 1) {
                    PlayerBuilder.chooseEquipment(choice, endowments[choice][1].id);
                }
            }
        });
    }

    /***********************************************/
    /*          Wizard Navigation Methods          */
    /***********************************************/

    static initWizard(topic) {
        Logger.log(1, 'initWizard', `topic=${topic}`);
        const target = "#hiddenWizard-topic";

        if (!DOMUtils.exists(target))
            return;

        if (topic) {
            $('#showBuilderWizardModal-button').css('visibility', 'visible');
            $(target).html(topic);
            const id = $(`#hiddenWizard-firstQuestion-${topic}`).html();
            Logger.log(10, 'initWizard', `=> id=${id}`);
            this._loadWizardModalContent('question', id, 'wizard/ajax-question', true);
        } else {
            $('#showBuilderWizardModal-button').css('visibility', 'hidden');
        }
    }

    static setNextStep(model, id) {
        Logger.log(1, 'setNextStep', `model=${model}, id=${id}`);
        $('#hiddenWizard-nextQuestion-Model').html(model);
        $('#hiddenWizard-nextQuestion-Id').html(id);
    }

    static nextQuestion() {
        const model = $('#hiddenWizard-nextQuestion-Model').html();
        const id = $('#hiddenWizard-nextQuestion-Id').html();
        Logger.log(10, 'nextQuestion', `id=${id}, model=${model}`);

        const loaders = {
            question: () => this._loadWizardModalContent('question', id, 'wizard/ajax-question', true),
            class: () => this._loadWizardModalContent('class', id, 'character-class/ajax-wizard'),
            race: () => this._loadWizardModalContent('race', id, 'race/ajax-wizard'),
            alignment: () => this._loadWizardModalContent('alignment', id, 'alignment/ajax-wizard')
        };

        loaders[model]?.();
    }

    static _loadWizardModalContent(type, id, url, showNextButton = false) {
        Logger.log(2, `_load${type}`, `id=${id}`);

        const target = `#ajaxBuilderWizardQA`;
        if (!DOMUtils.exists(target))
            return;

        $('#nextQuestionButton').css('visibility', showNextButton ? 'visible' : 'hidden');

        if (type !== 'question') {
            $(`#${type}${id}`).prop('checked', true);
        }

        const data = type === 'question' ?
                {id, topic: $('#hiddenWizard-topic').html()} :
                {id};

        AjaxUtils.request({
            url: url,
            data: data,
            successCallback: (response) => {
                $(target).html(response.content);
                if (type !== 'question') {
                    this.setProperty(`${type}_id`, id);
                }
            }
        });
    }

    /***********************************************/
    /*        Player description functions         */
    /***********************************************/

    /**
     * Loads random names based on race and gender
     */
    static loadRandomNames() {
        const count = 5;
        Logger.log(1, 'loadRandomNames', `count=${count}`);

        // Gather required parameters
        const params = {
            raceId: $('#playerbuilder-race_id').val(),
            gender: $('#playerbuilder-gender').val(),
            name: $('#playerbuilder-name').val(),
            n: count
        };

        // Only proceed if required parameters are present
        if (params.raceId && params.gender) {
            AjaxUtils.getContent({
                target: '#ajaxNameSelection',
                url: 'player-builder/ajax-names',
                data: params,
                callback: () => {
                    $('#tmpName').val(params.name);
                }
            });
        }
    }

    /**
     * Loads and updates player age information
     * @param {number} age - Selected age value
     */
    static loadAges(age) {
        Logger.log(1, 'loadAges', `age=${age}`);

        const raceId = $('#playerbuilder-race_id').val();
        Logger.log(10, 'loadAges', `raceId=${raceId}`);

        if (raceId) {
            AjaxUtils.getContent({
                target: '#ajaxAgeSelection',
                url: 'player-builder/ajax-age',
                data: {raceId, age},
                callback: (response) => {
                    $('#hiddenAgeTable').html(response.ageTable);
                }
            });
        }
    }

    /**
     * Sets and validates player age based on race restrictions
     * @param {number} age - Age value to set
     */
    static _setAge(age) {
        Logger.log(2, '_setAge', `age=${age}`);

        // Get age table from DOM
        const ageTable = JSON.parse($('#hiddenAgeTable').text());
        const label = ageTable.find(item => age <= item.age)?.lib || 'fine';
        $('#useSliderAgeLabel').css('visibility', 'hidden');
        $('#displayAgeLabel').css('visibility', 'visible');
        $('#playerAgeNum').html(age);
        $('#playerAgeLabel').html(label);
    }

    /***********************************************/
    /*             Abiity score Methods            */
    /***********************************************/

    /**
     * 
     * @param {type} score
     * @param {type} bonus
     * @returns {Number}
     */
    static _computeModifier(score, bonus) {
        Logger.log(2, '_computeModifier', `score=${score}, bonus=${bonus}`);
        const abilityScore = score + bonus;
        const m = (abilityScore - 10) / 2;
        return m >= 0 ? Math.floor(m) : Math.ceil(m);
    }

    /**
     * 
     * @param {type} id
     * @param {type} score
     * @returns {undefined}
     */
    static checkAbility(id, score) {
        Logger.log(1, 'checkAbility', `id=${id}, score=${score}`);
        const target = `#abilityText-${id}`;

        if (!DOMUtils.exists(target))
            return;

        const radios = document.querySelectorAll(`.score${score}`);
        const checkedCount = Array.from(radios).filter(radio => radio.checked).length;

        if (checkedCount > 1) {
            $(`#abilityRadio-${id}-${score}`).prop('checked', false);
        } else {
            const bonus = $(`#abilityChart-${id}`).data("bonus");
            $(target).html(score + bonus);
            Logger.log(1, 'checkAbility', `bonus=${bonus}`);

            $(`#abilityChart-${id}`)
                    .attr('data-score', score)
                    .attr("d", ChartDrawer.drawValue(30, 30, 25, score + bonus, 0, 30));

            $(`#modifier-${id}`).html(this._computeModifier(score, bonus));
        }
        this._saveAbilities();
    }

    /**
     * 
     * @returns {undefined}
     */
    static clearAbilities() {
        Logger.log(1, 'clearAbilities', '');

        $("[id^='abilityRadio-']").prop('checked', false);

        for (let i = 1; i <= 6; i++) {
            const bonus = $(`#abilityChart-${i}`).data("bonus");

            $(`#abilityText-${i}`).html(bonus);
            $(`#abilityChart-${i}`)
                    .attr('data-score', "0")
                    .attr("d", "");
            $(`#modifier-${i}`).html(this._computeModifier(0, bonus));
        }
        this.setProperty('abilities', '');
    }

    /**
     * 
     * @returns {undefined}
     */
    static _saveAbilities() {
        Logger.log(2, '_saveAbilities', '');

        const radios = $("[id*=abilityRadio-]");
        const checkedRadios = Array.from(radios).filter(radio => radio.checked);

        if (checkedRadios.length === 6) {
            const abilities = Object.fromEntries(
                    checkedRadios
                    .map(radio => radio.id.split('-').slice(1))
                    .map(([key, value]) => [key, Number(value)])
                    );

            const playerId = $('#hiddenPlayerId').html();
            Logger.log(10, '_saveAbilities', `abilities=${abilities}`);

            AjaxUtils.request({
                url: 'player-builder/ajax-save-abilities',
                data: {playerId, abilities},
                successCallback: (response) => {
                    ToastManager.show('Abilities', response.msg, response.error ? 'error' : 'info');
                    this.setProperty('abilities', 'ok');
                }
            });
        }
    }

    /***********************************************/
    /*             Skills score Methods            */
    /***********************************************/

    /**
     * Persists selected skills to the server
     * 
     * @returns {undefined}
     */
    static _saveSkills() {
        Logger.log(2, '_saveSkills', '');

        const checkboxes = $("[id*=skillCheckbox-]");
        Logger.log(10, '_saveSkills', `checkboxes.length=${checkboxes.length}`);

        const selectedSkills = Array.from(checkboxes)
                .filter(checkbox => checkbox.checked)
                .map(checkbox => Number(checkbox.id.split('-')[1]));

        const playerId = $('#hiddenPlayerId').html();
        Logger.log(10, '_saveSkills', `playerId=${playerId}, selectedSkills=${selectedSkills}`);

        AjaxUtils.request({
            url: 'player-builder/ajax-save-skills',
            data: {
                playerId: playerId,
                skills: selectedSkills
            },
            successCallback: (response) => {
                ToastManager.show('Skills', response.msg, response.error ? 'error' : 'info');
            }
        });
    }

    /**
     * Validates selected skills against maximum allowed
     * 
     * @param {number} id - Skill identifier
     * @param {number} max - Maximum allowed skills
     */
    static validateSkills(id, max) {
        Logger.log(1, 'validateSkills', `id=${id}, max=${max}`);

        const checkboxes = document.querySelectorAll('input[type="checkbox"][name="playerSkills"]');
        const selectedCount = Array.from(checkboxes).reduce((count, checkbox) =>
            count + (checkbox.checked ? 1 : 0), 0);

        if (selectedCount > max) {
            $(`#skillCheckbox-${id}`).prop('checked', false);
            ToastManager.show('Skills', `You have reached the maximum number of skills (${max})`, 'warning');
        } else {
            this._saveSkills();
            this.setProperty('skills', selectedCount === max ? 'ok' : '');
        }
    }

    /***********************************************/
    /*          Initial equipment Methods          */
    /***********************************************/

    /**
     * 
     * @param {type} playerId
     * @returns {undefined}
     */
    static loadEquipmentTab(playerId) {
        Logger.log(1, 'loadEquipmentTab', `playerId=${playerId}`);

        AjaxUtils.getContent({
            target: `#equipment-tab`,
            url: 'player-builder/ajax-endowment',
            data: {playerId: playerId}
        });
    }

    /**
     * 
     * @returns {unresolved}
     */
    static __getItems() {
        Logger.log(3, '__getItems', ``);

        const itemsArray = $('div[id^="ajaxItemChoice-"]')
                .map(function () {
                    return $(this).html().trim();
                }).get()
                .filter(content => content !== '');

        Logger.log(10, '__getItems', `itemsArray=${itemsArray}`);

        return itemsArray;
    }

    static _saveEquipment() {
        Logger.log(2, '_saveEquipment', '');

        const playerId = $('#hiddenPlayerId').html();
        const selectedIds = this.__getItems();
        const selectedCount = selectedIds.length;
        const max = $('div[id^="ajaxItemChoice-"]').length;
        Logger.log(10, '_saveEquipment', `playerId=${playerId}, selectedIds=${selectedIds}`);
        Logger.log(10, '_saveEquipment', `selectedCount=${selectedCount}, max=${max}`);

        AjaxUtils.request({
            url: 'player-builder/ajax-save-equipment',
            data: {
                playerId: playerId,
                itemIds: selectedIds
            },
            successCallback: (response) => {
                ToastManager.show('Initial equipment', response.msg, response.error ? 'error' : 'info');
                this.setProperty('items', selectedCount === max ? 'ok' : '');
            }
        });
    }

    /**
     * 
     * @param {type} choice
     * @param {type} alreadySelectedItems
     * @param {type} categoryIds
     * @returns {undefined}
     */
    static _loadCategoryModal(choice, alreadySelectedItems, categoryIds) {
        Logger.log(2, '_loadCategoryModal', `choice=${choice}, alreadySelectedItems=${alreadySelectedItems}, categoryIds=${categoryIds}`);

        AjaxUtils.getContent({
            target: '#ajaxCategoryItems',
            url: 'player-builder/ajax-item-category',
            data: {
                choice: choice,
                alreadySelectedItems: alreadySelectedItems,
                categoryIds: categoryIds
            },
            callback: () => {
                $('#showEquipmentModal-hiddenButton').click();
            }
        });
    }

    /**
     * 
     * @returns {undefined}
     */
    static _loadItemImages() {
        Logger.log(2, '_loadItemImages', '');

        let itemsArray = this.__getItems();

        AjaxUtils.getContent({
            target: `#ajaxItemImages`,
            url: 'item/ajax-images',
            data: {itemIds: itemsArray.join(',')}
        });
    }

    /**
     * 
     * @param {type} choice
     * @param {type} itemIds
     * @returns {undefined}
     */
    static setCategoryItem(choice, itemIds) {
        Logger.log(1, 'setCategoryItem', `choice=${choice}, itemIds=${itemIds}`);
        const target = `#ajaxItemChoice-${choice}`;

        if (!DOMUtils.exists(target))
            return;

        $(target).html(itemIds.join(','));
        this._loadItemImages();
        this._saveEquipment();
    }

    /**
     * 
     * @param {type} choice
     * @param {type} endowmentId
     * @returns {undefined}
     */
    static chooseEquipment(choice, endowmentId) {
        Logger.log(1, 'chooseEquipment', `choice=${choice}, endowmentId=${endowmentId}`);
        const target = `#ajaxItemChoice-${choice}`;

        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: 'player-builder/ajax-equipment',
            data: {choice: choice, endowmentId: endowmentId},
            successCallback: (response) => {
                if (response.content.items) {
                    Logger.log(10, 'chooseEquipment', `response.content.items=${response.content.items}`);
                    $(target).html(response.content.items);
                }
                if (response.content.categories) {
                    Logger.log(10, 'chooseEquipment', `response.content.categories=${response.content.categories}`);
                    this._loadCategoryModal(
                            response.content.choice,
                            response.content.items,
                            response.content.categories
                            );
                }
                this._loadItemImages();
                this._saveEquipment();
            }
        });
    }

    /**
     * 
     * @param {type} category
     * @returns {undefined}
     */
    static chooseBackgroundEquipment(category) {
        Logger.log(1, 'chooseBackgroundEquipment', `category=${category}`);

        const target = $('#ajaxItemChoice-background');
        const alreadySelectedItems = target.html();

        this._loadCategoryModal('background', alreadySelectedItems, category);

        const selectedItems = target.html();
        target.html(`${alreadySelectedItems},${selectedItems}`);

        this._loadItemImages();
        this._saveEquipment();
    }
}
