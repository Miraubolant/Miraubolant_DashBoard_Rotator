/**
 * Stats JS - Rafraîchissement automatique des statistiques
 */

let isRefreshing = false;
let lastStatsData = null;

// Stockage des valeurs précédentes pour détecter les changements
let previousStats = { devices: {}, browsers: {}, countries: {}, cities: {}, os: {}, urls: {} };

/**
 * Met à jour l'heure de dernière mise à jour
 */
function updateLastUpdateTime() {
    const el = document.getElementById('statsLastUpdate');
    if (el) {
        const now = new Date();
        el.textContent = now.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit', second: '2-digit'});
    }
}

/**
 * Met à jour le timestamp des stats détaillées
 */
function updateDetailedStatsTime() {
    const el = document.getElementById('detailedStatsTime');
    if (el) {
        const now = new Date();
        el.textContent = now.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit', second: '2-digit'});
    }
}

/**
 * Rafraîchissement des stats (silencieux ou manuel)
 */
async function refreshStats(showSpinner = false) {
    if (isRefreshing) return;
    isRefreshing = true;

    const btn = document.getElementById('refreshStatsBtn');
    const icon = document.getElementById('refreshIcon');
    const currentPeriod = window.currentPeriod || '24h';

    // Spinner seulement si refresh manuel
    if (showSpinner && btn && icon) {
        btn.disabled = true;
        icon.classList.add('animate-spin');
    }

    try {
        const response = await fetch('api-stats.php?period=' + currentPeriod + '&_=' + Date.now(), {
            credentials: 'same-origin'
        });
        if (!response.ok) throw new Error('Erreur réseau');

        const data = await response.json();

        if (data.success && data.stats) {
            // Mise à jour des valeurs
            updateStatValue('statClicks', data.stats.totalClicks, showSpinner);
            updateStatValue('statUniqueIps', data.stats.totalUniqueIps, showSpinner);
            updateStatValue('statLinks', data.stats.linksCount, showSpinner);
            updateStatValue('statRotators', data.stats.rotatorsCount, showSpinner);

            // Mise à jour des stats détaillées
            if (data.devices) updateDetailedStats('statsDevices', data.devices, 'device');
            if (data.browsers) updateDetailedStats('statsBrowsers', data.browsers, 'browser');
            if (data.countries) updateDetailedStats('statsCountries', data.countries, 'country');
            if (data.cities) updateDetailedStats('statsCities', data.cities, 'city');
            if (data.os) updateDetailedStats('statsOS', data.os, 'os');
            if (data.urls) updateUrlStats('statsUrls', data.urls);

            lastStatsData = data.stats;
            updateLastUpdateTime();
        }
    } catch (error) {
        if (showSpinner) console.error('Erreur refresh stats:', error);
    } finally {
        isRefreshing = false;
        if (showSpinner && btn && icon) {
            btn.disabled = false;
            icon.classList.remove('animate-spin');
        }
    }
}

/**
 * Bouton manuel avec animation
 */
function manualRefreshStats() {
    refreshStats(true);
}

/**
 * Mise à jour des valeurs avec animation subtile
 */
function updateStatValue(elementId, newValue, animate = false) {
    const el = document.getElementById(elementId);
    if (!el) return;

    const currentText = el.textContent.replace(/\s/g, '');
    const currentValue = parseInt(currentText) || 0;

    if (currentValue === newValue) return;

    const isIncrease = newValue > currentValue;

    if (animate) {
        el.style.transition = 'color 0.2s, transform 0.2s';
        el.style.color = isIncrease ? '#4ade80' : '#f87171';
        el.style.transform = 'scale(1.1)';
        setTimeout(() => {
            el.textContent = formatNumber(newValue);
            setTimeout(() => {
                el.style.color = '';
                el.style.transform = '';
            }, 300);
        }, 200);
    } else {
        el.style.transition = 'color 0.3s, transform 0.15s';
        el.style.color = isIncrease ? '#4ade80' : '#f87171';
        el.style.transform = 'scale(1.05)';
        el.textContent = formatNumber(newValue);
        setTimeout(() => {
            el.style.transform = '';
            setTimeout(() => {
                el.style.color = '';
            }, 400);
        }, 150);
    }
}

/**
 * Obtenir la couleur hex selon le type
 */
function getBarColorHex(type, value) {
    if (type === 'device') {
        const colors = { mobile: '#3b82f6', desktop: '#22c55e', tablet: '#a855f7', bot: '#6b7280' };
        return colors[value] || '#9ca3af';
    }
    if (type === 'browser') {
        const colors = { Chrome: '#eab308', Safari: '#60a5fa', Firefox: '#f97316', Edge: '#22d3ee', Bot: '#6b7280' };
        return colors[value] || '#9ca3af';
    }
    if (type === 'os') {
        const colors = { 'Windows 10': '#0ea5e9', 'Windows': '#0ea5e9', 'macOS': '#a3a3a3', 'iOS': '#3b82f6', 'Android': '#22c55e', 'Linux': '#f97316' };
        return colors[value] || '#9ca3af';
    }
    if (type === 'city') {
        return '#22d3ee';
    }
    return '#22c55e';
}

/**
 * Mise à jour des stats détaillées (format tableau)
 */
function updateDetailedStats(containerId, data, type) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const total = Object.values(data).reduce((a, b) => a + b, 0) || 1;
    const typeToKey = { device: 'devices', browser: 'browsers', country: 'countries', city: 'cities', os: 'os' };
    const prevData = previousStats[typeToKey[type]] || {};
    let hasChanges = false;

    const headerLabels = { device: 'Type', browser: 'Navigateur', country: 'Pays', city: 'Ville', os: 'OS' };
    const headerLabel = headerLabels[type] || type;

    let html = `<table class="mini-table">
        <thead><tr><th>${headerLabel}</th><th class="col-value">Clics</th><th class="col-pct">%</th></tr></thead>
        <tbody>`;

    const entries = Object.entries(data);

    // Mettre à jour le compteur
    const countEl = document.getElementById(type === 'country' ? 'countriesCount' : type === 'city' ? 'citiesCount' : null);
    if (countEl) countEl.textContent = `(${entries.length})`;

    entries.forEach(([key, count]) => {
        const pct = Math.round((count / total) * 100);
        const color = getBarColorHex(type, key);
        const prevCount = prevData[key] || 0;
        const delta = count - prevCount;
        const hasChanged = delta !== 0 && prevCount > 0;

        if (hasChanged) hasChanges = true;

        const deltaHtml = hasChanged
            ? `<span class="stat-delta ${delta > 0 ? 'stat-delta-positive' : 'stat-delta-negative'}">${delta > 0 ? '+' : ''}${delta}</span>`
            : '';

        const valueClass = hasChanged ? (delta > 0 ? 'stat-value-changed' : 'stat-value-changed stat-value-decreased') : '';
        const rowClass = hasChanged ? 'stat-item-updated' : '';

        const labelContent = type === 'country'
            ? `<span class="mr-1.5">${countryCodeToFlag(key)}</span>${escapeHtml(key)}`
            : escapeHtml(key);

        html += `<tr class="${rowClass}" data-search="${escapeHtml(key.toLowerCase())}">
            <td class="col-label ${type === 'device' ? 'capitalize' : ''}">${labelContent}</td>
            <td class="col-value ${valueClass}">${formatNumber(count)}${deltaHtml}</td>
            <td class="col-pct">${pct}%<span class="pct-bar" style="width: ${pct * 0.4}px; background: ${color};"></span></td>
        </tr>`;
    });

    if (entries.length === 0) {
        html += '<tr><td colspan="3" class="text-center text-text-secondary py-4">Aucune donnée</td></tr>';
    }

    html += '</tbody></table>';
    container.innerHTML = html;

    previousStats[typeToKey[type]] = {...data};

    if (hasChanges) {
        updateDetailedStatsTime();
    }
}

/**
 * Mise à jour des stats URLs - format tableau
 */
function updateUrlStats(containerId, data) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const prevData = previousStats.urls;
    let hasChanges = false;
    const entries = Object.entries(data);

    const countEl = document.getElementById('urlsCount');
    if (countEl) countEl.textContent = `(${entries.length})`;

    let html = `<table class="mini-table">
        <thead><tr><th>URL</th><th class="col-value">Clics</th></tr></thead>
        <tbody>`;

    entries.forEach(([url, count]) => {
        const shortUrl = url.length > 30 ? url.substring(0, 30) + '...' : url;
        const prevCount = prevData[url] || 0;
        const delta = count - prevCount;
        const hasChanged = delta !== 0 && prevCount > 0;

        if (hasChanged) hasChanges = true;

        const deltaHtml = hasChanged
            ? `<span class="stat-delta ${delta > 0 ? 'stat-delta-positive' : 'stat-delta-negative'}">${delta > 0 ? '+' : ''}${delta}</span>`
            : '';

        const valueClass = hasChanged ? (delta > 0 ? 'stat-value-changed' : 'stat-value-changed stat-value-decreased') : '';
        const rowClass = hasChanged ? 'stat-item-updated' : '';

        html += `<tr class="${rowClass}" data-search="${escapeHtml(url.toLowerCase())}">
            <td class="col-label truncate max-w-[180px]" title="${escapeHtml(url)}">${escapeHtml(shortUrl)}</td>
            <td class="col-value ${valueClass}">${formatNumber(count)}${deltaHtml}</td>
        </tr>`;
    });

    if (entries.length === 0) {
        html += '<tr><td colspan="2" class="text-center text-text-secondary py-4">Aucune donnée</td></tr>';
    }

    html += '</tbody></table>';
    container.innerHTML = html;

    previousStats.urls = {...data};

    if (hasChanges) {
        updateDetailedStatsTime();
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', function() {
    updateLastUpdateTime();
    updateDetailedStatsTime();

    // Auto-refresh silencieux toutes les secondes
    setInterval(() => refreshStats(false), 1000);
});
