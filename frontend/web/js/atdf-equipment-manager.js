class EquipmentHandler {

    constructor(options = {}) {
        this.zoneId = null;
        this.options = {
            onHeadClick: () => {
                this.zoneId = 'equipmentHeadZone';
                this._filterItemType(['Helmet']);
                console.log("Head clicked");
                this._disarmPlayer(this.zoneId);
            },
            onChestClick: () => {
                this.zoneId = 'equipmentChestZone';
                this._filterItemType(['Armor']);
                console.log("Chest clicked");
                this._disarmPlayer(this.zoneId);
            },
            onRightHandClick: () => {
                this.zoneId = 'equipmentRightHandZone';
                this._filterItemType(['Weapon', 'Tool']);
                console.log("Right hand clicked");
                this._disarmPlayer(this.zoneId);
            },
            onLeftHandClick: () => {
                this.zoneId = 'equipmentLeftHandZone';
                this._filterItemType(['Weapon', 'Shield']);
                console.log("Left hand clicked");
                this._disarmPlayer(this.zoneId);
            },
            ...options
        };
        this.zoneStates = {
            equipmentHeadZone: {filled: false, image: null, defaultColor: 'white'},
            equipmentChestZone: {filled: false, image: null, defaultColor: 'white'},
            equipmentRightHandZone: {filled: false, image: null, defaultColor: 'white'},
            equipmentLeftHandZone: {filled: false, image: null, defaultColor: 'white'},
            equipmentBackZone: {filled: false, image: null, defaultColor: 'black'}
        };
    }

    init(playerId, svgElement) {
        this.playerId = playerId;
        this.svgElement = svgElement;

        this._attachEventListeners();

        $(document).ready(() => {
            this.getPlayerItems(playerId);
        });
    }

    getPlayerItems(playerId) {
        const target = `#packageContent`;
        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: 'player-item/ajax-equipment',
            method: 'GET',
            data: {playerId: playerId},
            successCallback: (response) => {
                console.log('Callback', response);
                if (!response.error) {
                    $(target).html(response.content);
                    this._attachEquipButtonEventListeners(response.items);
                    this._updateZones(response.data);
                }
            }
        });
    }

    _attachEquipButtonEventListeners(items) {
        $.each(items, (index, item) => {
            const $button = $(`#${item.buttonId}`);

            if ($button.length) {
                $button.click(() => {
                    console.log(`Button with ID ${item.buttonId} clicked!`);
                    console.log(`Image: ${item.image}`);
                    this._equipPlayer(this.zoneId, item);
                });
            }
        });
    }

    _equipPlayer(zoneId, item) {
        const data = {
            playerId: this.playerId,
            itemId: item.itemId,
            bodyZone: zoneId
        };
        console.log(`_equipPlayer - data: ${JSON.stringify(data)}`);

        AjaxUtils.request({
            url: 'player-item/ajax-equip-player',
            data: data,
            successCallback: (response) => {
                console.log('Callback', response);
                if (!response.error) {
                    this._updateZones(response.data);
                }
            }
        });
    }

    _disarmPlayer(zoneId) {
        const state = this.zoneStates[zoneId];

        if (state.filled === false)
            return;

        const data = {
            playerId: this.playerId,
            bodyZone: zoneId
        };
        console.log(`_disarmPlayer - data: ${JSON.stringify(data)}`);

        AjaxUtils.request({
            url: 'player-item/ajax-disarm-player',
            data: data,
            successCallback: (response) => {
                console.log('Callback', response);
                if (!response.error) {
                    this._updateZones(response.data);
                }
            }
        });
    }

    _updateZones(data) {
        $.each(data, (zoneId, item) => {
            if (item.itemId === null) {
                this._clearZone(zoneId);
            } else {
                this._fillZoneWithImage(zoneId, item);
            }
        });
    }

    _filterItemType(visibleTypes) {
        Logger.log(1, '_filterItemType', `visibleTypes=${JSON.stringify(visibleTypes)}`);
        const divs = document.querySelectorAll('[id^="itemType-"]');

        // Loop through each div and check for visibility
        divs.forEach(div => {
            const id = div.getAttribute('id');

            // Split the ID to extract the div name and wizard
            const parts = id.split('-');
            const itemType = parts[1];
            const visible = visibleTypes.indexOf(itemType);

            if (visible < 0) {
                $(div).addClass('d-none');
            } else {
                $(div).removeClass('d-none');
            }
        });
    }

    _attachEventListener(zoneId, callback) {
        const zone = this.svgElement.querySelector(`#${zoneId}`);

        if (zone) {
            zone.addEventListener('click', () => callback(this));
            zone.style.cursor = 'pointer';
        }
    }

    _attachEventListeners() {
        this._attachEventListener('equipmentHeadZone', this.options.onHeadClick);
        this._attachEventListener('equipmentChestZone', this.options.onChestClick);
        this._attachEventListener('equipmentRightHandZone', this.options.onRightHandClick);
        this._attachEventListener('equipmentLeftHandZone', this.options.onLeftHandClick);
        // No listener on the back zone on purpose
    }

    /**
     * Fill a zone with a PNG image.
     * 
     * @param {string} zoneId - The ID of the zone to fill.
     * @param {array} item - Item data (id, name, image).
     */
    _fillZoneWithImage(zoneId, item) {
        const imageFile = item.image;
        console.log(`_fillZoneWithImage - imageFile=${imageFile}, zoneId=${zoneId}`);
        const state = this.zoneStates[zoneId];

        if (state.filled === true && state.image === imageFile) {
            console.log(`_fillZoneWithImage - imageFile=${imageFile} already in zoneId=${zoneId}`);
            return;
        }

        const zone = this.svgElement.querySelector(`#${zoneId}`);
        const {cx, cy, r} = zone.getAttributeNames().reduce((acc, name) => {
            acc[name] = zone.getAttribute(name);
            return acc;
        }, {});
        const imageUrl = `/frontend/web/img/item/${imageFile}`;

        const defs = document.createElementNS("http://www.w3.org/2000/svg", "defs");
        const pattern = document.createElementNS("http://www.w3.org/2000/svg", "pattern");
        pattern.setAttribute("id", `pattern-${zone.id}`);
        pattern.setAttribute("patternUnits", "userSpaceOnUse");
        pattern.setAttribute("width", r * 2);
        pattern.setAttribute("height", r * 2);
        pattern.setAttribute("x", cx - r);
        pattern.setAttribute("y", cy - r);

        const image = document.createElementNS("http://www.w3.org/2000/svg", "image");
        image.setAttribute("href", imageUrl);
        image.setAttribute("width", r * 2);
        image.setAttribute("height", r * 2);

        pattern.appendChild(image);

        defs.appendChild(pattern);
        console.log(defs);
        this.svgElement.insertBefore(defs, this.svgElement.firstChild);

        zone.setAttribute("fill", `url(#pattern-${zone.id})`);
        zone.setAttribute("fill-opacity", "1"); // Set fill-opacity to 1

        state.filled = true;
        state.image = imageFile;
    }

    /**
     * Revert a zone to its initial colored state.
     * @param {string} zoneId - The ID of the zone to fill.
     */
    _clearZone(zoneId) {
        console.log(`_clearZone - zoneId=${zoneId}`);

        const state = this.zoneStates[zoneId];
        if (state.filled === false)
            return;

        const zone = this.svgElement.querySelector(`#${zoneId}`);
        zone.setAttribute("fill", state.defaultColor);
        zone.setAttribute("fill-opacity", "0.5");

        // Remove the pattern if it exists
        const pattern = this.svgElement.querySelector(`#pattern-${zoneId}`);
        if (pattern) {
            pattern.remove();
        }
        state.filled = false;
        state.image = null;
    }

    /**
     * Update the callback for a specific zone.
     * @param {string} zoneId - The ID of the zone to update.
     * @param {Function} callback - The new callback function.
     */
    updateCallback(zoneId, callback) {
        const zone = this.svgElement.querySelector(`#${zoneId}`);
        if (zone) {
            switch (zoneId) {
                case 'equipmentHeadZone':
                    this.options.onHeadClick = callback;
                    break;
                case 'equipmentChestZone':
                    this.options.onChestClick = callback;
                    break;
                case 'equipmentRightHandZone':
                    this.options.onRightHandClick = callback;
                    break;
                case 'equipmentLeftHandZone':
                    this.options.onLeftHandClick = callback;
                    break;
                default:
                    console.warn(`Unknown zone ID: ${zoneId}`);
            }
            this._attachEventListeners();
        }
    }
}
