/**
 * Map JS - Initialisation et mise à jour de la carte du monde
 */

let worldMap = null;
let currentMapValues = {};

function initWorldMap() {
    if (typeof jsVectorMap === 'undefined') {
        setTimeout(initWorldMap, 200);
        return;
    }

    const countryData = window.countryData || {};
    currentMapValues = {};

    Object.keys(countryData).forEach(code => {
        if (code && code !== 'XX') {
            currentMapValues[code.toUpperCase()] = countryData[code];
        }
    });

    worldMap = new jsVectorMap({
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
                values: currentMapValues,
                scale: ['#22c55e', '#4ade80'],
                normalizeFunction: 'polynomial'
            }]
        },
        onRegionTooltipShow: function(e, el, code) {
            const clicks = currentMapValues[code] || 0;
            el.innerHTML = `${el.innerHTML}: ${clicks.toLocaleString()} clics`;
        }
    });
}

/**
 * Met à jour les données de la carte
 */
function updateWorldMap(countryData) {
    if (!worldMap) return;

    currentMapValues = {};
    Object.keys(countryData).forEach(code => {
        if (code && code !== 'XX') {
            currentMapValues[code.toUpperCase()] = countryData[code];
        }
    });

    // Mettre à jour les valeurs de la série
    worldMap.params.series.regions[0].values = currentMapValues;

    // Recalculer les couleurs basées sur les nouvelles valeurs
    worldMap.setValues(currentMapValues);
}

// Initialiser la carte au chargement
document.addEventListener('DOMContentLoaded', initWorldMap);
