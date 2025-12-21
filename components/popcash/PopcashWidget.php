<?php
/**
 * Composant: Widget PopCash dans le dashboard
 * Affiche la balance et le nombre de campagnes
 */

/**
 * Rend le widget PopCash compact
 */
function renderPopcashWidget(array $popcashData): void
{
    if (!$popcashData['account']) {
        return;
    }

    $savedCampaignsCount = count(getSavedCampaignIds());
?>
<div class="card mb-4 p-3 cursor-pointer hover:bg-bg-tertiary transition-colors" onclick="openCampaignModal()">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <span class="text-sm font-medium text-white flex items-center gap-2"><span class="text-green-400">●</span> PopCash</span>
            <span class="text-green-400 font-bold">$<?= number_format($popcashData['account']['balance'] ?? 0, 2) ?></span>
        </div>
        <div class="flex items-center gap-4 text-sm">
            <span class="text-text-secondary"><?= $savedCampaignsCount ?> campagne<?= $savedCampaignsCount > 1 ? 's' : '' ?> enregistrée<?= $savedCampaignsCount > 1 ? 's' : '' ?></span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </div>
    </div>
</div>
<?php
}
