<?php
/**
 * Composant: Logs en temps réel
 */

/**
 * Rend la section des logs en direct
 */
function renderLiveLogs(): void
{
?>
<div class="card" id="section-logs" data-section="logs">
    <div class="px-3 py-2 border-b border-border section-header flex items-center justify-between" onclick="toggleSection('logs')">
        <div class="flex items-center gap-2">
            <h2 class="font-medium text-white text-sm">Requêtes entrantes</h2>
            <span class="flex items-center gap-1 text-xs text-green-400" id="liveIndicator">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
                </span>
            </span>
            <span class="text-text-secondary text-xs" id="lastUpdate"></span>
        </div>
        <svg class="h-4 w-4 text-text-secondary toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </div>
    <div class="section-content">
        <div id="logsContainer" class="divide-y divide-border overflow-y-auto custom-scrollbar" style="max-height: 200px;">
            <p class="px-3 py-6 text-center text-text-secondary text-sm" id="logsLoading">Chargement...</p>
        </div>
    </div>
</div>
<?php
}
