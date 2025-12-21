<?php
/**
 * Composant: Statistiques détaillées
 * Inclut 8 tableaux: Devices, Browsers, Pays, URLs, Villes, OS, Sources, Sync
 *
 * Dépendances (fournies par dashboard.php):
 * - countryCodeToFlag() depuis layout.php
 * - formatRelativeDate(), formatDate(), e() depuis functions.php
 */

/**
 * Rend la section des statistiques détaillées
 */
function renderDetailedStats(array $rotatorStats, array $data, string $period): void
{
?>
<div class="card mb-4" id="section-detailed-stats" data-section="detailed-stats">
    <div class="px-3 py-2 border-b border-border flex items-center justify-between">
        <div class="flex items-center gap-2 section-header flex-1" onclick="toggleSection('detailed-stats')">
            <svg class="h-4 w-4 text-text-secondary toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
            <h2 class="font-medium text-white text-sm">Statistiques détaillées</h2>
            <span class="flex items-center gap-1.5 text-xs text-green-400" id="detailedStatsLive">
                <span class="relative flex h-1.5 w-1.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-green-500"></span>
                </span>
                <span id="detailedStatsTime" class="text-text-secondary"></span>
            </span>
        </div>
        <div class="flex items-center gap-1" onclick="event.stopPropagation()">
            <button type="button" onclick="exportStats('csv')" class="btn btn-secondary text-xs py-1 px-2" title="Export CSV">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
            </button>
            <button type="button" onclick="exportStats('json')" class="btn btn-secondary text-xs py-1 px-2" title="Export JSON">
                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3" /></svg>
            </button>
        </div>
    </div>
    <div class="section-content">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-3 p-3" id="detailedStatsContainer">
            <?php renderDevicesTable($rotatorStats); ?>
            <?php renderBrowsersTable($rotatorStats); ?>
            <?php renderCountriesTable($rotatorStats); ?>
            <?php renderUrlsTable($rotatorStats); ?>
            <?php renderCitiesTable($rotatorStats); ?>
            <?php renderOsTable($rotatorStats); ?>
            <?php renderSourcesTable($data); ?>
            <?php renderSyncTable($data); ?>
        </div>
    </div>
</div>
<?php
}

/**
 * Tableau des appareils
 */
function renderDevicesTable(array $rotatorStats): void
{
    $totalDevices = array_sum($rotatorStats['devices']) ?: 1;
?>
<div class="bg-bg-primary/50 rounded-lg overflow-hidden">
    <div class="px-3 py-2 bg-bg-secondary/50 border-b border-border flex items-center gap-2">
        <svg class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
        <span class="text-xs font-medium text-white">Appareils</span>
    </div>
    <div id="statsDevices">
        <table class="mini-table">
            <thead>
                <tr><th>Type</th><th class="col-value">Clics</th><th class="col-pct">%</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rotatorStats['devices'] as $device => $count):
                $pct = round(($count / $totalDevices) * 100);
                $color = match($device) {
                    'mobile' => '#3b82f6',
                    'desktop' => '#22c55e',
                    'tablet' => '#a855f7',
                    'bot' => '#6b7280',
                    default => '#9ca3af'
                };
            ?>
            <tr>
                <td class="col-label capitalize"><?= e($device) ?></td>
                <td class="col-value"><?= number_format($count) ?></td>
                <td class="col-pct"><?= $pct ?>%<span class="pct-bar" style="width: <?= $pct * 0.4 ?>px; background: <?= $color ?>;"></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($rotatorStats['devices'])): ?>
            <tr><td colspan="3" class="text-center text-text-secondary py-4">Aucune donnée</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
}

/**
 * Tableau des navigateurs
 */
function renderBrowsersTable(array $rotatorStats): void
{
    $totalBrowsers = array_sum($rotatorStats['browsers']) ?: 1;
?>
<div class="bg-bg-primary/50 rounded-lg overflow-hidden">
    <div class="px-3 py-2 bg-bg-secondary/50 border-b border-border flex items-center gap-2">
        <svg class="h-4 w-4 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" /></svg>
        <span class="text-xs font-medium text-white">Navigateurs</span>
    </div>
    <div id="statsBrowsers">
        <table class="mini-table">
            <thead>
                <tr><th>Navigateur</th><th class="col-value">Clics</th><th class="col-pct">%</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rotatorStats['browsers'] as $browser => $count):
                $pct = round(($count / $totalBrowsers) * 100);
                $color = match($browser) {
                    'Chrome' => '#eab308',
                    'Safari' => '#60a5fa',
                    'Firefox' => '#f97316',
                    'Edge' => '#22d3ee',
                    'Bot' => '#6b7280',
                    default => '#9ca3af'
                };
            ?>
            <tr>
                <td class="col-label"><?= e($browser) ?></td>
                <td class="col-value"><?= number_format($count) ?></td>
                <td class="col-pct"><?= $pct ?>%<span class="pct-bar" style="width: <?= $pct * 0.4 ?>px; background: <?= $color ?>;"></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($rotatorStats['browsers'])): ?>
            <tr><td colspan="3" class="text-center text-text-secondary py-4">Aucune donnée</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
}

/**
 * Tableau des pays
 */
function renderCountriesTable(array $rotatorStats): void
{
    $totalCountries = array_sum($rotatorStats['countries']) ?: 1;
?>
<div class="bg-bg-primary/50 rounded-lg overflow-hidden">
    <div class="px-3 py-2 bg-bg-secondary/50 border-b border-border flex items-center gap-2">
        <svg class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span class="text-xs font-medium text-white">Pays</span>
        <span class="text-xs text-text-secondary" id="countriesCount">(<?= count($rotatorStats['countries']) ?>)</span>
    </div>
    <div class="px-2 py-1.5 border-b border-border/50">
        <input type="text" id="searchCountries" placeholder="Rechercher..." class="w-full bg-bg-secondary/50 border border-border rounded px-2 py-1 text-xs text-white placeholder-text-secondary focus:outline-none focus:border-accent" onkeyup="filterTable('statsCountries', this.value)">
    </div>
    <div class="overflow-y-auto custom-scrollbar" style="max-height: 150px;" id="statsCountries">
        <table class="mini-table">
            <thead>
                <tr><th>Pays</th><th class="col-value">Clics</th><th class="col-pct">%</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rotatorStats['countries'] as $country => $count):
                $pct = round(($count / $totalCountries) * 100);
            ?>
            <tr data-search="<?= e(strtolower($country)) ?>">
                <td class="col-label"><span class="mr-1.5"><?= countryCodeToFlag($country) ?></span><?= e($country) ?></td>
                <td class="col-value"><?= number_format($count) ?></td>
                <td class="col-pct"><?= $pct ?>%<span class="pct-bar" style="width: <?= $pct * 0.4 ?>px; background: #22c55e;"></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($rotatorStats['countries'])): ?>
            <tr><td colspan="3" class="text-center text-text-secondary py-4">Aucune donnée</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
}

/**
 * Tableau des URLs
 */
function renderUrlsTable(array $rotatorStats): void
{
?>
<div class="bg-bg-primary/50 rounded-lg overflow-hidden">
    <div class="px-3 py-2 bg-bg-secondary/50 border-b border-border flex items-center gap-2">
        <svg class="h-4 w-4 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
        <span class="text-xs font-medium text-white">URLs</span>
        <span class="text-xs text-text-secondary" id="urlsCount">(<?= count($rotatorStats['urls']) ?>)</span>
    </div>
    <div class="px-2 py-1.5 border-b border-border/50">
        <input type="text" id="searchUrls" placeholder="Rechercher..." class="w-full bg-bg-secondary/50 border border-border rounded px-2 py-1 text-xs text-white placeholder-text-secondary focus:outline-none focus:border-accent" onkeyup="filterTable('statsUrls', this.value)">
    </div>
    <div class="overflow-y-auto custom-scrollbar" style="max-height: 150px;" id="statsUrls">
        <table class="mini-table">
            <thead>
                <tr><th>URL</th><th class="col-value">Clics</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rotatorStats['urls'] as $url => $count):
                $shortUrl = strlen($url) > 30 ? substr($url, 0, 30) . '...' : $url;
            ?>
            <tr data-search="<?= e(strtolower($url)) ?>">
                <td class="col-label truncate max-w-[180px]" title="<?= e($url) ?>"><?= e($shortUrl) ?></td>
                <td class="col-value"><?= number_format($count) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($rotatorStats['urls'])): ?>
            <tr><td colspan="2" class="text-center text-text-secondary py-4">Aucune donnée</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
}

/**
 * Tableau des villes
 */
function renderCitiesTable(array $rotatorStats): void
{
    $totalCities = array_sum($rotatorStats['cities']) ?: 1;
?>
<div class="bg-bg-primary/50 rounded-lg overflow-hidden">
    <div class="px-3 py-2 bg-bg-secondary/50 border-b border-border flex items-center gap-2">
        <svg class="h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
        <span class="text-xs font-medium text-white">Villes</span>
        <span class="text-xs text-text-secondary" id="citiesCount">(<?= count($rotatorStats['cities']) ?>)</span>
    </div>
    <div class="px-2 py-1.5 border-b border-border/50">
        <input type="text" id="searchCities" placeholder="Rechercher..." class="w-full bg-bg-secondary/50 border border-border rounded px-2 py-1 text-xs text-white placeholder-text-secondary focus:outline-none focus:border-accent" onkeyup="filterTable('statsCities', this.value)">
    </div>
    <div class="overflow-y-auto custom-scrollbar" style="max-height: 150px;" id="statsCities">
        <table class="mini-table">
            <thead>
                <tr><th>Ville</th><th class="col-value">Clics</th><th class="col-pct">%</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rotatorStats['cities'] as $city => $count):
                $pct = round(($count / $totalCities) * 100);
            ?>
            <tr data-search="<?= e(strtolower($city)) ?>">
                <td class="col-label"><?= e($city) ?></td>
                <td class="col-value"><?= number_format($count) ?></td>
                <td class="col-pct"><?= $pct ?>%<span class="pct-bar" style="width: <?= $pct * 0.4 ?>px; background: #22d3ee;"></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($rotatorStats['cities'])): ?>
            <tr><td colspan="3" class="text-center text-text-secondary py-4">Aucune donnée</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
}

/**
 * Tableau des systèmes d'exploitation
 */
function renderOsTable(array $rotatorStats): void
{
    $totalOS = array_sum($rotatorStats['os']) ?: 1;
?>
<div class="bg-bg-primary/50 rounded-lg overflow-hidden">
    <div class="px-3 py-2 bg-bg-secondary/50 border-b border-border flex items-center gap-2">
        <svg class="h-4 w-4 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
        <span class="text-xs font-medium text-white">Systèmes</span>
    </div>
    <div id="statsOS">
        <table class="mini-table">
            <thead>
                <tr><th>OS</th><th class="col-value">Clics</th><th class="col-pct">%</th></tr>
            </thead>
            <tbody>
            <?php foreach ($rotatorStats['os'] as $os => $count):
                $pct = round(($count / $totalOS) * 100);
                $color = match($os) {
                    'Windows 10', 'Windows' => '#0ea5e9',
                    'macOS' => '#a3a3a3',
                    'iOS' => '#3b82f6',
                    'Android' => '#22c55e',
                    'Linux' => '#f97316',
                    default => '#9ca3af'
                };
            ?>
            <tr>
                <td class="col-label"><?= e($os) ?></td>
                <td class="col-value"><?= number_format($count) ?></td>
                <td class="col-pct"><?= $pct ?>%<span class="pct-bar" style="width: <?= $pct * 0.4 ?>px; background: <?= $color ?>;"></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($rotatorStats['os'])): ?>
            <tr><td colspan="3" class="text-center text-text-secondary py-4">Aucune donnée</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
}

/**
 * Tableau des sources
 */
function renderSourcesTable(array $data): void
{
    $totalSources = array_sum($data['sourcesDistrib']) ?: 1;
?>
<div class="bg-bg-primary/50 rounded-lg overflow-hidden">
    <div class="px-3 py-2 bg-bg-secondary/50 border-b border-border flex items-center gap-2">
        <svg class="h-4 w-4 text-pink-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14" /></svg>
        <span class="text-xs font-medium text-white">Sources</span>
    </div>
    <div id="statsSources">
        <table class="mini-table">
            <thead>
                <tr><th>Source</th><th class="col-value">Liens</th><th class="col-pct">%</th></tr>
            </thead>
            <tbody>
            <?php foreach ($data['sourcesDistrib'] as $source => $count):
                $pct = round(($count / $totalSources) * 100);
                $sourceName = $data['sources'][$source] ?? $source;
            ?>
            <tr>
                <td class="col-label"><?= e($sourceName) ?></td>
                <td class="col-value"><?= number_format($count) ?></td>
                <td class="col-pct"><?= $pct ?>%<span class="pct-bar" style="width: <?= $pct * 0.4 ?>px; background: #ec4899;"></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($data['sourcesDistrib'])): ?>
            <tr><td colspan="3" class="text-center text-text-secondary py-4">Aucune donnée</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php
}

/**
 * Tableau de synchronisation
 */
function renderSyncTable(array $data): void
{
?>
<div class="bg-bg-primary/50 rounded-lg overflow-hidden">
    <div class="px-3 py-2 bg-bg-secondary/50 border-b border-border flex items-center gap-2">
        <svg class="h-4 w-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
        <span class="text-xs font-medium text-white">Synchronisation</span>
    </div>
    <div class="p-3">
        <?php if ($data['lastSync']): ?>
        <div class="space-y-2 text-xs">
            <div class="flex justify-between">
                <span class="text-text-secondary">Dernière sync</span>
                <span class="text-white"><?= formatRelativeDate($data['lastSync']['created_at']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-text-secondary">Date</span>
                <span class="text-white"><?= formatDate($data['lastSync']['created_at']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-text-secondary">Rotators actifs</span>
                <span class="text-green-400 font-medium"><?= count($data['activeSites']) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-text-secondary">Total liens</span>
                <span class="text-white"><?= $data['linksTotal'] ?></span>
            </div>
        </div>
        <?php else: ?>
        <p class="text-text-secondary text-xs text-center py-4">Aucune synchronisation</p>
        <?php endif; ?>
    </div>
</div>
<?php
}
