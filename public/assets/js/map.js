/**
 * Map JS - Initialisation de la carte du monde
 */

function initWorldMap() {
    if (typeof jsVectorMap === 'undefined') {
        setTimeout(initWorldMap, 200);
        return;
    }

    const countryData = window.countryData || {};
    const values = {};

    Object.keys(countryData).forEach(code => {
        if (code && code !== 'XX') {
            values[code.toUpperCase()] = countryData[code];
        }
    });

    new jsVectorMap({
        selector: '#world-map',
        map: 'world',
        zoomButtons: true,
        zoomOnScroll: true,
        zoomOnScrollSpeed: 3,
        focusOn: { x: 0.48, y: 0.22, scale: 4 },
        regionStyle: {
            initial: { fill: '#2a2a2a', stroke: '#3a3a3a', strokeWidth: 0.5 },
            hover: { fill: '#4a4a4a' },
            selected: { fill: '#22c55e' },
            selectedHover: { fill: '#4ade80' }
        },
        series: {
            regions: [{
                values: values,
                scale: ['#22c55e', '#4ade80'],
                normalizeFunction: 'polynomial'
            }]
        },
        onRegionTooltipShow: function(e, el, code) {
            const clicks = values[code] || 0;
            el.innerHTML = `${el.innerHTML}: ${clicks.toLocaleString()} clics`;
        }
    });
}

// Initialiser la carte au chargement
document.addEventListener('DOMContentLoaded', initWorldMap);
