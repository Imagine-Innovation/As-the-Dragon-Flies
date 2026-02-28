class KpiManager {
    constructor(delay) {
        this.delay = (delay ?? 300) * 1000; // Convert seconds to milliseconds
        this.timer = null;
    }

    /**
     * Initializes the refresh cycle.
     * We trigger an immediate update first, then start the interval.
     */
    init() {
        CoreLibrary.init();
        if (this.timer)
            return; // Prevent multiple initializations

        this.updateKpis();

        this.timer = setInterval(() => {
            this.updateKpis();
        }, this.delay);

        console.log(`KPI Refresh initialized. Interval: ${this.delay / 1000}s`);
    }

    /**
     * Iterates through the KPI list and triggers updates.
     */
    async updateKpis() {
        Logger.log(1, 'updateKpis', `Refreshing KPIs`);

        const task = this.refreshKpis();
    }

    /**
     * Internal helper to fetch data and call the existing updateEveryKpi function.
     * 
     * @param array kpis
     * @returns void
     */
    async refreshKpis() {
        Logger.log(2, 'refreshKpis', `async refresh`);
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
                Object.entries(response).forEach(([container, value]) => {
                    this.updateKpi(container, value);
                });
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
     * Clean up the timer to prevent memory leaks (useful for SPAs)
     */
    stop() {
        if (this.timer) {
            clearInterval(this.timer);
            this.timer = null;
        }
        Logger.log(1, 'stop', `KPI timer stopped`);
    }
}