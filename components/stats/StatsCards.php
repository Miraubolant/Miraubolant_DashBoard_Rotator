<?php
/**
 * Composant: Cartes de statistiques principales
 * Affiche les 4 KPIs: Clics, IPs uniques, Liens, Rotators
 */

/**
 * Rend les 4 cartes de statistiques principales
 */
function renderStatsCards(array $rotatorStats, array $data): void
{
?>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4" id="statsCards">
    <!-- Clics -->
    <div class="stat-card" style="padding: 0.875rem;">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-text-secondary text-xs">Clics</p>
                <p class="text-xl font-bold text-white" id="statClicks"><?= number_format($rotatorStats['totalClicks'], 0, ',', ' ') ?></p>
            </div>
            <div class="icon-box icon-box-blue" style="width: 36px; height: 36px;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122" />
                </svg>
            </div>
        </div>
    </div>

    <!-- IPs uniques -->
    <div class="stat-card" style="padding: 0.875rem;">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-text-secondary text-xs">IPs uniques</p>
                <p class="text-xl font-bold text-white" id="statUniqueIps"><?= number_format($rotatorStats['totalUniqueIps'], 0, ',', ' ') ?></p>
            </div>
            <div class="icon-box icon-box-green" style="width: 36px; height: 36px;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Liens -->
    <div class="stat-card" style="padding: 0.875rem;">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-text-secondary text-xs">Liens</p>
                <p class="text-xl font-bold text-white" id="statLinks"><?= $data['linksTotal'] ?></p>
            </div>
            <div class="icon-box icon-box-purple" style="width: 36px; height: 36px;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
            </div>
        </div>
    </div>

    <!-- Rotators -->
    <div class="stat-card" style="padding: 0.875rem;">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-text-secondary text-xs">Rotators</p>
                <p class="text-xl font-bold text-white" id="statRotators"><?= count($data['activeSites']) ?></p>
            </div>
            <div class="icon-box icon-box-cyan" style="width: 36px; height: 36px;">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2" />
                </svg>
            </div>
        </div>
    </div>
</div>
<?php
}
