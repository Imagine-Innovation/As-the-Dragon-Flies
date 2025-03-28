/**
 * ChartDrawer Class
 * Handles the generation and drawing of SVG-based circular charts for ability scores.
 * Provides utilities for both percentage-based and value-based circular representations.
 */
class ChartDrawer {
    /**
     * Initializes drawing of all ability charts present on the page
     * Searches for elements with 'lite-chart' class and processes each one
     */
    static drawAbilityCharts() {
        Logger.log(1, 'drawAbilityCharts', '');
        // Process each chart element found
        $('.lite-chart').each((index, chart) => {
            this._drawCircleChart($(chart));
        });
    }

    /**
     * Processes an individual circle chart element
     * @param {jQuery} chart - jQuery object representing the chart element
     */
    static _drawCircleChart(chart) {
        Logger.log(2, '_drawCircleChart', `chart=${chart}`);
        
        // Extract chart parameters from data attributes
        const path = chart.find("[id^='abilityChart-']");
        const chartId = path.attr("id");
        const percent = path.data("percent");
        const score = path.data("score");
        const bonus = path.data("bonus");
        const value = score + bonus;
        const min = path.data("min");
        const max = path.data("max");

        // Handle percentage-based charts
        if (percent > 0) {
            Logger.log(10, '_drawCircleChart', `chartId=${chartId}, percent=${percent}`);
            path.attr("d", this._drawPercent(30, 30, 25, percent));
        } 
        // Handle value-based charts
        else if (value > 0) {
            Logger.log(10, '_drawCircleChart', `chartId=${chartId}, value=${value}`);
            // Calculate value range
            const minValue = Math.min(min || 0, value);
            const maxValue = Math.max(max || 100, value);
            path.attr("d", this.drawValue(30, 30, 25, value, minValue, maxValue));
        }
    }

    /**
     * Generates SVG path for a value-based circular chart
     * @param {number} x - Center X coordinate
     * @param {number} y - Center Y coordinate
     * @param {number} r - Circle radius
     * @param {number} v - Current value
     * @param {number} min - Minimum scale value
     * @param {number} max - Maximum scale value
     * @returns {string} SVG path definition
     */
    static drawValue(x, y, r, v, min, max) {
        Logger.log(2, 'drawValue', `x=${x}, y=${y}, r=${r}, v=${v}, min=${min}, max=${max}`);
        // Convert value to percentage
        const percentage = Math.round((v - min + 1) / (max - min + 1) * 100);
        return this._drawPercent(x, y, r, percentage);
    }

    /**
     * Generates SVG path for a percentage-based circular chart
     * @param {number} x - Center X coordinate
     * @param {number} y - Center Y coordinate
     * @param {number} r - Circle radius
     * @param {number} p - Percentage to draw (0-100)
     * @returns {string} SVG path definition
     */
    static _drawPercent(x, y, r, p) {
        Logger.log(3, '_drawPercent', `x=${x}, y=${y}, r=${r}, p=${p}`);
        // Convert percentage to arc angles
        const startAngle = 0;
        const endAngle = Math.round(360 * p / 100);
        return this._drawArc(x, y, r, startAngle, endAngle);
    }

    /**
     * Generates SVG arc path between two angles
     * @param {number} x - Center X coordinate
     * @param {number} y - Center Y coordinate
     * @param {number} r - Circle radius
     * @param {number} a1 - Start angle in degrees
     * @param {number} a2 - End angle in degrees
     * @returns {string} SVG path definition
     */
    static _drawArc(x, y, r, a1, a2) {
        Logger.log(3, '_drawArc', `x=${x}, y=${y}, r=${r}, a1=${a1}, a2=${a2}`);
        // Calculate start and end points
        const origin = this._polarToCartesian(x, y, r, a2);
        const destination = this._polarToCartesian(x, y, r, a1);
        
        // Determine arc size flag
        const largeArcFlag = a2 - a1 <= 180 ? "0" : "1";

        // Construct SVG path
        return [
            "M", origin.x, origin.y,           // Move to start
            "A", r, r, 0, largeArcFlag, 0,     // Arc parameters
            destination.x, destination.y        // End point
        ].join(" ");
    }

    /**
     * Converts polar coordinates to Cartesian coordinates
     * @param {number} cx - Center X coordinate
     * @param {number} cy - Center Y coordinate
     * @param {number} r - Radius
     * @param {number} angleDegrees - Angle in degrees
     * @returns {Object} {x, y} coordinates
     */
    static _polarToCartesian(cx, cy, r, angleDegrees) {
        Logger.log(3, '_polarToCartesian', `cx=${cx}, cy=${cy}, r=${r}, angleDegrees=${angleDegrees}`);
        // Convert degrees to radians and adjust for SVG coordinate system
        const angleRadians = (angleDegrees - 90) * Math.PI / 180.0;

        // Calculate Cartesian coordinates
        return {
            x: cx + (r * Math.cos(angleRadians)),
            y: cy + (r * Math.sin(angleRadians))
        };
    }
}
