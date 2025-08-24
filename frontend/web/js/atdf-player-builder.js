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
        const properties = (playerId === '') ?
                ['race_id', 'class_id', 'background_id', 'description'] :
                ['alignment_id', 'image_id', 'name', 'gender', 'age', 'languages', 'abilities', 'skills', 'items'];

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

        // Trigger save or validate if complete
        if (progress === 100) {
            var modalName = (playerId === '') ? 'builderSaveModal' : 'builderValidateModal';
            var modalElement = document.getElementById(modalName);
            if (modalElement) {
                var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                modal.show();
            }
        }
    }

    /***********************************************/
    /*        Page initialization Methods          */
    /***********************************************/

    static initCreatePage() {
        $(document).ready(function () {
            Logger.log(1, 'initCreatePage', '');
            const target = `#hiddenWizard-topic`;
            Logger.log(2, 'initCreatePage', `hiddenWizard-topic=${$(target).html()}`);
            if (!DOMUtils.exists(target))
                return;

            $('#playerBuilderSaveButton').click(function () {
                $("#w0").submit();
            });

            PlayerBuilder.initOnTabClick();
            PlayerBuilder.initWizard($(target).html());
            PlayerBuilder.updateProgress();
        });
    }

    static initUpdatePage() {
        $(document).ready(function () {
            Logger.log(1, 'initUpdatePage', '');
            const target = `#hiddenWizard-topic`;
            Logger.log(2, 'initCreatePage', `hiddenWizard-topic=${$(target).html()}`);
            if (!DOMUtils.exists(target))
                return;

            PlayerBuilder.initOnTabClick();
            PlayerBuilder.initWizard($(target).html());
            PlayerBuilder.updateProgress();
            PlayerBuilder.loadAdvancedProperties('images', 'ajaxAvatarChoice');

            $('#playerBuilderSaveButton').click(function () {
                $("#w0").submit();
            });

            $('#playerBuilderValidateButton').click(function () {
                PlayerBuilder.setProperty('status', 10);
                $("#w0").submit();
            });

            $('#builderEquipmentModal').on('click', '#exitEquipmentModal-button', function ()
            {
                const selectedValue = $('input[name="initialEquipmentRadio"]:checked').val();
                if (selectedValue) {
                    // Blur the button before closing the modal to prevent focus issues
                    $(this).blur();
                    const [choice, ...itemIds] = selectedValue.split(',');
                    var modalElement = document.getElementById('builderEquipmentModal');
                    if (modalElement) {
                        var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                        modal.hide();
                    }

                    PlayerBuilder.setCategoryItem(choice, itemIds);
                }
            });
            // More robust focus management on modal hide
            $('#builderEquipmentModal').on('hide.bs.modal', function ()
            {
                const focusedElement = document.activeElement;
                if (this.contains(focusedElement)) {
                    focusedElement.blur();
                    // Optionally, move focus to a specific element outside the modal, e.g.:
                    // $('#some-element-outside-modal').focus();
                }
            });
        });
    }

    static initOnTabClick()
    {
        // Select all elements with an ID that starts with 'addToCartButton-'
        const tabs = document.querySelectorAll('[id^="builderTab-"]');

        // Loop through each tab and add the event listener
        tabs.forEach(tab => {
            Logger.log(10, 'initOnTabClick', `Found tab ${tab.getAttribute('id')}`);
            tab.addEventListener('click', function (event) {
                Logger.log(10, 'initOnTabClick', `Click on tab ${tab.getAttribute('id')}`);
                // Prevent the default action
                event.preventDefault();

                // Retrieve the suffix from the tab's ID
                const suffix = tab.getAttribute('id');

                // Split the ID to extract the tab name and wizard
                const parts = suffix.split('-');
                const tabName = parts[1];
                const wizard = parts[2];
                console.log(`---------------- tabName=${tabName}, wizard=${wizard}`);
                if (tabName === 'equipment') {
                    // Special feature for equipement tab: 
                    // load the content only when displayed
                    PlayerBuilder.initEquipementTab();
                }
                PlayerBuilder.initWizard(wizard);
            });
        });
    }

    static initDescriptionTab(gender, alignmentId, age) {
        $(document).ready(function () {
            Logger.log(1, 'initDescriptionTab', `gender=${gender}, alignmentId=${alignmentId}, age=${age}`);
            PlayerBuilder.loadRandomNames();
            PlayerBuilder.loadAges(age);
            PlayerBuilder.loadAdvancedProperties('languages', 'ajaxLanguageSelection');

            if (gender) {
                $('#gender' + gender).prop('checked', true);
            }
            if (alignmentId) {
                $('#alignment' + alignmentId).prop('checked', true);
            }

            $('#generateNewNamesButton').click(function (event) {
                event.preventDefault();
                PlayerBuilder.loadRandomNames();
            });
        }
        );
    }

    static initAvatarTab() {
        $(document).ready(function () {
            Logger.log(1, 'initAvatarTab', '');
            PlayerBuilder.loadAdvancedProperties('images', 'ajaxAvatarChoice');
        }
        );
    }

    static initAbilitiesTab() {
        $(document).ready(function () {
            Logger.log(1, 'initAbilitiesTab', '');
            if (typeof ChartDrawer !== 'undefined' && ChartDrawer.drawAbilityCharts) {
                ChartDrawer.drawAbilityCharts();
            } else {
                console.error('ChartDrawer or drawAbilityCharts method not found.');
            }

            $('#clearAbilitiesButton').click(function (event) {
                event.preventDefault();
                PlayerBuilder.clearAbilities();
            });
        }
        );
    }

    static initSkillsTab() {
        $(document).ready(function () {
            Logger.log(1, 'initSkillsTab', '');
            PlayerBuilder.loadAdvancedProperties('skills', 'ajaxSkills');
            $('#generateNewTraitsButton').click(function (event) {
                event.preventDefault();
                PlayerBuilder.loadAdvancedProperties('traits', 'ajaxTraits');
            });
        }
        );
    }

    static initEquipementTab() {
        $(document).ready(function () {
            Logger.log(1, 'initEquipementTab', '');

            const target = "#hiddenCategory";
            if (DOMUtils.exists(target)) {
                const category = $(target).html();
                PlayerBuilder.chooseBackgroundEquipment(category);
            }

            PlayerBuilder.loadEquipmentTab();
        }
        );
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
        }
        ;

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
        }
        );
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
    /*                Skills Methods               */
    /***********************************************/

    /**
     * Persists selected skills to the server
     * 
     * @param {number} skillId - Skill identifier
     * @returns {undefined}
     */
    static _saveSkill(skillId) {
        Logger.log(2, '_saveSkill', `id=${skillId}`);

        const isChecked = $(`#skillCheckbox-${skillId}`).is(':checked');
        const playerId = $('#hiddenPlayerId').html();

        Logger.log(10, '_saveSkills', `playerId=${playerId}, checkbox=${isChecked ? 1 : 0}`);

        AjaxUtils.request({
            url: 'player-builder/ajax-update-skill',
            data: {
                playerId: playerId,
                skillId: skillId,
                isProficient: isChecked ? 1 : 0
            },
            successCallback: (response) => {
                ToastManager.show('Skills', response.msg, response.error ? 'error' : 'info');
            }
        }
        );
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
            //this._saveSkills();
            this._saveSkill(id);
            this.setProperty('skills', selectedCount === max ? 'ok' : '');
        }
    }

    /***********************************************/
    /*          Initial equipment Methods          */
    /***********************************************/

    /**
     * 
     * @returns {undefined}
     */
    static loadEquipmentTab() {
        const playerId = $('#hiddenPlayerId').html();
        Logger.log(1, 'loadEquipmentTab', `playerId=${playerId}`);

        const target = `#playerBuilderEndowment`;
        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: 'player-builder/ajax-endowment',
            data: {
                playerId: playerId
            },
            successCallback: (response) => {
                if (!response.error) {
                    $(target).html(response.content);
                    let endowment = {};
                    for (let choice = 1; choice <= response.choices; choice++) {
                        endowment = response.endowments[choice];
                        if (endowment && endowment[1] && Object.keys(endowment).length === 1) {
                            PlayerBuilder.chooseEquipment(choice, endowment[1].id);
                        }
                    }
                }
            }
        }
        );
    }

    /**
     * 
     * @returns {unresolved}
     */
    static __getItems() {
        Logger.log(3, '__getItems', ``);

        const itemsArray = $('span[id^="ajaxItemChoice-"]')
                .map(function () {
                    return $(this).html().trim();
                }
                ).get()
                .filter(content => content !== '');

        Logger.log(10, '__getItems', `itemsArray=${itemsArray}`);

        return itemsArray;
    }

    static _saveEquipment() {
        Logger.log(2, '_saveEquipment', '');

        const playerId = $('#hiddenPlayerId').html();
        const selectedIds = this.__getItems();
        const selectedCount = selectedIds.length;
        const max = $('span[id^="ajaxItemChoice-"]').length;
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
        }
        );
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
                var modalElement = document.getElementById('builderEquipmentModal');
                if (modalElement) {
                    var modal = bootstrap.Modal.getOrCreateInstance(modalElement);
                    modal.show();
                }
            }
        }
        );
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
        }
        );
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

    /***********************************************/
    /*              Languages Methods              */
    /***********************************************/

    static initLangages() {
        Logger.log(1, 'initLangages', ``);

        target = `#ajaxLanguageSelection`;
        if (!DOMUtils.exists(target))
            return;

        // Gather all required parameters
        const params = {
            playerId: $('#hiddenPlayerId').html(),
            raceId: $('#playerbuilder-race_id').val(),
            backgroundId: $('#playerbuilder-background_id').val()
        };

        AjaxUtils.request({
            url: 'player-builder/ajax-langages',
            data: {
                playerId: $('#hiddenPlayerId').html(),
                raceId: $('#playerbuilder-race_id').val(),
                backgroundId: $('#playerbuilder-background_id').val()
            },
            successCallback: (response) => {
                if (response.error === false) {
                    Logger.log(10, 'initLangages', `response.content.n=${response.content.n}`);
                    if (!response.content.n) {
                        this.setProperty('languages', 'ok');
                    }
                    $(target).html(response.content);
                }
            }
        });
    }

    /**
     * Persists selected languages to the server
     * 
     * @param {number} languageId - Language identifier
     * @returns {undefined}
     */
    static _saveLanguage(languageId) {
        Logger.log(2, '_saveLanguage', `id=${languageId}`);

        const isChecked = $(`#languageCheckbox-${languageId}`).is(':checked');
        const playerId = $('#hiddenPlayerId').html();

        Logger.log(10, '_saveLanguages', `playerId=${playerId}, checkbox=${isChecked ? 1 : 0}`);

        AjaxUtils.request({
            url: 'player-builder/ajax-update-language',
            data: {
                playerId: playerId,
                languageId: languageId,
                selected: isChecked ? 1 : 0
            },
            successCallback: (response) => {
                ToastManager.show('Languages', response.msg, response.error ? 'error' : 'info');
            }
        }
        );
    }

    /**
     * Validates selected languages against maximum allowed
     * 
     * @param {number} id - Language identifier
     * @param {number} max - Maximum allowed languages
     */
    static validateLanguages(id, max) {
        Logger.log(1, 'validateLanguages', `id=${id}, max=${max}`);

        const checkboxes = document.querySelectorAll('input[type="checkbox"][name="playerLanguages"]');
        const selectedCount = Array.from(checkboxes).reduce((count, checkbox) =>
            count + (checkbox.checked ? 1 : 0), 0);

        if (selectedCount > max) {
            $(`#languageCheckbox-${id}`).prop('checked', false);
            ToastManager.show('Languages', `You have reached the maximum number of languages (${max})`, 'warning');
        } else {
            this._saveLanguage(id);
            this.setProperty('languages', selectedCount === max ? 'ok' : '');
        }
    }
}
