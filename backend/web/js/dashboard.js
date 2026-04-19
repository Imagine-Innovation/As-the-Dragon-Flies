class DashboardManager {
    constructor(kpiDelay, activeQuestsDelay) {
        this.kpiDelay = (kpiDelay ?? 60) * 1000; // Default 60s
        this.activeQuestsDelay = (activeQuestsDelay ?? 300) * 1000; // Default 300s
        this.kpiTimer = null;
        this.activeQuestsTimer = null;
    }

    /**
     * Initializes the refresh cycles.
     */
    init() {
        CoreLibrary.init();

        // KPI Refresh - check for one of the KPI containers
        if (DOMUtils.exists('#active-users') && !this.kpiTimer) {
            this.updateKpis();
            this.kpiTimer = setInterval(() => {
                this.updateKpis();
            }, this.kpiDelay);
            console.log(`KPI Refresh initialized. Interval: ${this.kpiDelay / 1000}s`);
        }

        // Active Quests Refresh
        if (DOMUtils.exists('#activeQuestsTable') && !this.activeQuestsTimer) {
            this.updateActiveQuests();
            this.activeQuestsTimer = setInterval(() => {
                this.updateActiveQuests();
            }, this.activeQuestsDelay);
            console.log(`Active Quests Refresh initialized. Interval: ${this.activeQuestsDelay / 1000}s`);
        }
    }

    /**
     * Iterates through the KPI list and triggers updates.
     */
    updateKpis() {
        Logger.log(1, 'updateKpis', `Refreshing KPIs`);
        try {
            this.updateEveryKpi();
        } catch (error) {
            console.error(`Failed to update KPI`, error);
        }
    }

    updateEveryKpi() {
        Logger.log(3, 'updateEveryKpi', ``);

        AjaxUtils.request({
            url: 'kpi/update',
            method: 'GET',
            successCallback: (response) => {
                if (response.content) {
                    Object.entries(response.content).forEach(([container, value]) => {
                        this.updateKpi(container, value);
                    });
                }
            }
        });
    }

    updateKpi(container, value) {
        Logger.log(2, 'updateKpi', `container=${container}, value=${value}`);
        const target = `#${container}`;
        if (!DOMUtils.exists(target))
            return;

        $(target).text(value);
    }

    /**
     * Updates the active quests table.
     */
    updateActiveQuests() {
        Logger.log(1, 'updateActiveQuests', `Refreshing Active Quests`);

        const target = '#activeQuestsTable';
        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: 'site/ajax-active-quests',
            method: 'GET',
            successCallback: (response) => {
                if (!response.error && response.content) {
                    $(target).html(response.content);
                    Logger.log(2, 'updateActiveQuests', `Active Quests refreshed`);
                }
            }
        });
    }

    /**
     * Clean up timers to prevent memory leaks
     */
    stop() {
        if (this.kpiTimer) {
            clearInterval(this.kpiTimer);
            this.kpiTimer = null;
        }
        if (this.activeQuestsTimer) {
            clearInterval(this.activeQuestsTimer);
            this.activeQuestsTimer = null;
        }
        Logger.log(1, 'stop', `Dashboard timers stopped`);
    }
}
