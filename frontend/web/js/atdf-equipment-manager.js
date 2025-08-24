class EquipmentHandler {

    constructor(options = {}) {
        this.zoneId = null;
        this.field = null;
        this.options = {
            onHeadClick: () => {
                this.zoneId = 'equipmentHeadZone';
                this.field = 'head_id';
                this._filterItems(['Helmet']);
                console.log("Head clicked");
            },
            onChestClick: () => {
                this.zoneId = 'equipmentChestZone';
                this.field = 'chest_id';
                this._filterItems(['Armor']);
                console.log("Chest clicked");
            },
            onRightHandClick: () => {
                this.zoneId = 'equipmentRightHandZone';
                this.field = 'right_hand_id';
                this._filterItems(['Weapon', 'Tool']);
                console.log("Right hand clicked");
            },
            onLeftHandClick: () => {
                this.zoneId = 'equipmentLeftHandZone';
                this.field = 'left_hand_id';
                this._filterItems(['Weapon', 'Shield']);
                console.log("Left hand clicked");
            },
            ...options
        };
        this.zoneStates = {
            equipmentHeadZone: {filled: false, image: null},
            equipmentChestZone: {filled: false, image: null},
            equipmentRightHandZone: {filled: false, image: null},
            equipmentLeftHandZone: {filled: false, image: null}
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
                    // Loop through the data and attach click events
                    $.each(response.itemData, (index, item) => {
                        this._attachEquipButtonEventListener(item);
                    });
                }
            }
        });
    }

    _attachEquipButtonEventListener(item) {
        const $button = $(`#${item.buttonId}`);

        if ($button.length) {
            $button.click(() => {
                console.log(`Button with ID ${item.buttonId} clicked!`);
                console.log(`Image: ${item.image}`);
                this._toggleZoneState(this.zoneId, item.image);
            });
        }
    }

    _filterItems(visibleTypes) {
        Logger.log(1, 'filter', `visibleTypes=${JSON.stringify(visibleTypes)}`);
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
    }

    /**
     * Toggle the appearance of a zone between a colored zone and a filled image.
     * @param {string} zoneId - The ID of the zone to toggle.
     * @param {string} image - The URL of the PNG image to fill the zone.
     */
    _toggleZoneState(zoneId, image) {
        Logger.log(1, '_toggleZoneState', `zoneId=${zoneId}, image=${image}`);
        const zone = this.svgElement.querySelector(`#${zoneId}`);
        if (!zone)
            return;

        const state = this.zoneStates[zoneId];
        if (state.filled) {
            // Revert to the initial colored zone
            this._revertZoneToInitialState(zone, zoneId);
        } else {
            // Fill the zone with the provided image
            this._fillZoneWithImage(zone, image);
            state.image = image;
        }
        state.filled = !state.filled;
    }

    /**
     * Fill a zone with a PNG image.
     * @param {SVGZoneElement} zone - The zone element to fill.
     * @param {string} image - The URL of the PNG image.
     */
    _fillZoneWithImage(zone, imageFile) {
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
    }

    /**
     * Revert a zone to its initial colored state.
     * @param {SVGZoneElement} zone - The zone element to revert.
     * @param {string} zoneId - The ID of the zone.
     */
    _revertZoneToInitialState(zone, zoneId) {
        const state = this.zoneStates[zoneId];
        zone.setAttribute("fill", "white");
        zone.setAttribute("fill-opacity", "0.5");

        // Remove the pattern if it exists
        const pattern = this.svgElement.querySelector(`#pattern-${zoneId}`);
        if (pattern) {
            pattern.remove();
        }
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
