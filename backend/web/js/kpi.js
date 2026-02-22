class KpiManager {
    constructor(KPIs, delay) {
        this.KPIs = KPIs;
        this.delay = (delay ?? 300) * 1000; // Convert seconds to milliseconds
        this.timer = null;
    }

    /**
     * Initializes the refresh cycle.
     * We trigger an immediate update first, then start the interval.
     */
    init() {
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

        // We use Promise.allSettled so that if one API fails, 
        // the others still have a chance to update.
        const tasks = this.KPIs.map(kpi => this.refreshSingle(kpi));
        await Promise.allSettled(tasks);
    }

    /**
     * Internal helper to fetch data and call the existing updateSingleKpi function.
     * 
     * @param array{containerName: string, api: string} kpi
     * @returns void
     */
    async refreshSingle(kpi) {
        try {
            this.updateSingleKpi(kpi.containerName, kpi.api);
        } catch (error) {
            console.error(`Failed to update KPI: ${kpi.containerName}`, error);
        }
    }

    updateSingleKpi(container, api) {
        Logger.log(2, 'updateSingleKpi', `container=${container}, api=${api}`);
        const target = `#${container}`;
        if (!DOMUtils.exists(target))
            return;

        AjaxUtils.request({
            url: api,
            method: 'GET',
            successCallback: (response) => {
                $(target).text(response.kpi);
            }
        });
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