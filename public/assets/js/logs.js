/**
 * Logs JS - Logs en temps réel
 */

(function() {
    const container = document.getElementById('logsContainer');
    const lastUpdateEl = document.getElementById('lastUpdate');
    const liveIndicator = document.getElementById('liveIndicator');

    if (!container) return;

    let isFirstLoad = true;
    let previousLogTimestamps = new Set();

    /**
     * Formate une entrée de log
     */
    function formatLogEntry(log, isNew = false) {
        if (log.type === 'request') {
            const deviceIcon = log.device === 'mobile'
                ? '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>'
                : log.device === 'tablet'
                    ? '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>'
                    : '<svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>';

            const browserShort = log.browser ? log.browser.split(' ')[0] : '';
            const osInfo = log.os || '';
            const flashClass = isNew ? 'flash-update' : '';

            // Afficher le nom du lien si trouvé, sinon le nom du rotator
            const linkName = getLinkNameByUrl(log.url) || log.site;

            return `
                <div class="px-3 py-2 hover:bg-white/5 transition-colors text-xs border-l-2 border-transparent hover:border-green-500 ${flashClass}">
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="text-lg flex-shrink-0">${countryCodeToFlag(log.country)}</span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-white font-medium">${escapeHtml(linkName)}</span>
                                    ${log.city ? `<span class="text-text-secondary">• ${escapeHtml(log.city)}</span>` : ''}
                                </div>
                                <div class="flex items-center gap-2 text-text-secondary mt-0.5">
                                    <span class="flex items-center gap-1" title="${escapeHtml(log.device || 'desktop')}">
                                        ${deviceIcon}
                                        <span>${escapeHtml(log.device || 'desktop')}</span>
                                    </span>
                                    ${browserShort ? `<span>• ${escapeHtml(browserShort)}</span>` : ''}
                                    ${osInfo ? `<span>• ${escapeHtml(osInfo)}</span>` : ''}
                                </div>
                            </div>
                        </div>
                        <span class="text-text-secondary whitespace-nowrap flex-shrink-0">${escapeHtml(log.relative)}</span>
                    </div>
                </div>
            `;
        }
        return '';
    }

    /**
     * Récupère les logs depuis l'API
     */
    async function fetchLogs() {
        try {
            const response = await fetch('api-logs.php?limit=15&_=' + Date.now(), {
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error('Erreur réseau');

            const data = await response.json();

            if (data.success && data.logs) {
                if (data.logs.length === 0) {
                    container.innerHTML = '<p class="px-4 py-8 text-center text-text-secondary">Aucun log récent</p>';
                } else {
                    // Détecter les nouveaux logs pour l'effet flash
                    const currentTimestamps = new Set();
                    const html = data.logs.map(log => {
                        const logKey = log.timestamp + log.site + log.country;
                        currentTimestamps.add(logKey);
                        const isNew = !isFirstLoad && !previousLogTimestamps.has(logKey);
                        return formatLogEntry(log, isNew);
                    }).join('');

                    container.innerHTML = html;
                    previousLogTimestamps = currentTimestamps;
                    isFirstLoad = false;
                }
            }

            if (lastUpdateEl) {
                lastUpdateEl.textContent = new Date().toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
            }
            if (liveIndicator) {
                liveIndicator.classList.remove('text-red-400');
                liveIndicator.classList.add('text-green-400');
            }

        } catch (error) {
            console.error('Erreur fetch logs:', error);
            if (liveIndicator) {
                liveIndicator.classList.remove('text-green-400');
                liveIndicator.classList.add('text-red-400');
            }
            if (lastUpdateEl) {
                lastUpdateEl.textContent = 'Erreur de connexion';
            }
        }
    }

    // Premier chargement
    fetchLogs();

    // Refresh silencieux toutes les secondes
    setInterval(fetchLogs, 1000);
})();
