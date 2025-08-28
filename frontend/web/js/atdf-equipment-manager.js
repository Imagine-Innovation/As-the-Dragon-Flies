class EquipmentHandler {

    constructor(options = {}) {
        this.zoneId = null;
        this.options = {
            onHeadClick: () => {
                this.zoneId = 'equipmentHeadZone';
                this._filterItemType(['Helmet']);
                this._disarmPlayer(this.zoneId);
            },
            onChestClick: () => {
                this.zoneId = 'equipmentChestZone';
                this._filterItemType(['Armor']);
                this._disarmPlayer(this.zoneId);
            },
            onRightHandClick: () => {
                this.zoneId = 'equipmentRightHandZone';
                this._filterItemType(['Weapon', 'Tool']);
                this._disarmPlayer(this.zoneId);
            },
            onLeftHandClick: () => {
                this.zoneId = 'equipmentLeftHandZone';
                this._filterItemType(['Weapon', 'Shield']);
                this._disarmPlayer(this.zoneId);
            },
            ...options
        };
    }

    init(playerId, svgElement) {
        this.playerId = playerId;
        this.svgElement = svgElement;

        $(document).ready(() => {
            this._getInitPlayerItems(playerId);
        });
    }

    _getInitPlayerItems(playerId) {
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
                    this._updateSvg(response);
                }
            }
        });
    }

    _updateSvg(response) {
        let target = `#svg-modal`;
        if (DOMUtils.exists(target)) {
            $(target).html(response.contentModal);
            this.svgElement = document.getElementById('equipmentSvg'); // Re-select the SVG
            this._attachEventListeners(); // Re-attach event listeners
        }

        target = `#svg-aside-offcanvas`;
        if (DOMUtils.exists(target)) {
            $(target).html(response.contentOffcanvas);
        }

        target = `#svg-aside`;
        if (DOMUtils.exists(target)) {
            $(target).html(response.contentAside);
        }
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

        AjaxUtils.request({
            url: 'player-item/ajax-equip-player',
            data: data,
            successCallback: (response) => {
                console.log('Callback', response);
                if (!response.error) {
                    this._updateSvg(response);
                }
            }
        });
    }

    _disarmPlayer(zoneId) {
        const data = {
            playerId: this.playerId,
            bodyZone: zoneId
        };

        AjaxUtils.request({
            url: 'player-item/ajax-disarm-player',
            data: data,
            successCallback: (response) => {
                console.log('Callback', response);
                if (!response.error) {
                    this._updateSvg(response);
                }
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
}
