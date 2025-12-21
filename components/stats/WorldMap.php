<?php
/**
 * Composant: Carte du monde avec distribution géographique
 */

/**
 * Rend la section carte du monde
 */
function renderWorldMap(): void
{
?>
<div class="card" id="section-map" data-section="map">
    <div class="px-3 py-2 border-b border-border section-header flex items-center justify-between" onclick="toggleSection('map')">
        <h2 class="font-medium text-white text-sm">Distribution géographique</h2>
        <svg class="h-4 w-4 text-text-secondary toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
    </div>
    <div class="section-content p-3">
        <div id="world-map" style="height: 200px; background: #0a0a0a; border-radius: 6px;"></div>
    </div>
</div>
<?php
}
