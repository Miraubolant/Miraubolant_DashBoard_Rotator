<?php
/**
 * Composant: Section des rotators
 */

/**
 * Rend la section des rotators
 */
function renderRotatorsSection(array $sites): void
{
?>
<div class="card mb-4" id="section-rotators" data-section="rotators">
    <div class="px-3 py-2 border-b border-border flex items-center justify-between">
        <div class="flex items-center gap-2 section-header flex-1" onclick="toggleSection('rotators')">
            <svg class="h-4 w-4 text-text-secondary toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
            <h2 class="font-medium text-white text-sm">Rotators</h2>
            <span class="text-text-secondary text-xs">(<?= count($sites) ?>)</span>
        </div>
        <button type="button" onclick="event.stopPropagation(); openRotatorModal()" class="btn btn-secondary text-xs py-1 px-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Ajouter
        </button>
    </div>
    <div class="section-content">
    <?php if (empty($sites)): ?>
    <p class="px-4 py-8 text-center text-text-secondary">Aucun rotator configuré.</p>
    <?php else: ?>
    <div class="divide-y divide-border">
        <?php foreach ($sites as $site): ?>
        <div class="px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="<?= $site['is_active'] ? 'text-green-400' : 'text-text-secondary' ?>">●</span>
                <div>
                    <p class="font-medium text-white"><?= e($site['name']) ?></p>
                    <p class="text-xs text-text-secondary"><?= e($site['base_url']) ?></p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick='openRotatorModal(<?= json_encode($site) ?>)' class="text-text-secondary hover:text-white" title="Modifier">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                <button type="button" onclick="deleteRotator(<?= $site['id'] ?>, '<?= e(addslashes($site['name'])) ?>')" class="text-text-secondary hover:text-danger" title="Supprimer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    </div>
</div>
<?php
}
