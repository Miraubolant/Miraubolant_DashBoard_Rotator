<?php
/**
 * Composant: Section des liens blanchis avec tableau et pagination
 */

/**
 * Rend la section des liens blanchis
 */
function renderLinksSection(array $data, string $period): void
{
?>
<div class="card mb-4" id="section-links" data-section="links">
    <div class="px-3 py-2 border-b border-border flex items-center justify-between">
        <div class="flex items-center gap-2 section-header flex-1" onclick="toggleSection('links')">
            <svg class="h-4 w-4 text-text-secondary toggle-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
            <h2 class="font-medium text-white text-sm">Liens blanchis</h2>
            <span class="text-text-secondary text-xs">(<?= $data['linksTotal'] ?>)</span>
        </div>
        <button type="button" onclick="event.stopPropagation(); openLinkModal()" class="btn btn-secondary text-xs py-1 px-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Ajouter
        </button>
    </div>
    <div class="section-content">
    <?php if (empty($data['links'])): ?>
    <p class="px-4 py-8 text-center text-text-secondary">Aucun lien. Cliquez sur "Ajouter" pour commencer.</p>
    <?php else: ?>
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b border-border text-left text-text-secondary text-xs uppercase">
                    <th class="px-4 py-3 font-medium">Nom</th>
                    <th class="px-4 py-3 font-medium">URL blanchie</th>
                    <th class="px-4 py-3 font-medium">Source</th>
                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-border">
                <?php foreach ($data['links'] as $link): ?>
                <tr class="table-row">
                    <td class="px-4 py-3">
                        <p class="font-medium text-white"><?= e($link['name']) ?></p>
                        <p class="text-xs text-text-secondary truncate max-w-xs">→ <?= e(truncate($link['original_url'], 40)) ?></p>
                    </td>
                    <td class="px-4 py-3">
                        <a href="<?= e($link['whitened_url']) ?>" target="_blank" class="text-accent hover:underline text-sm"><?= e(truncate($link['whitened_url'], 35)) ?></a>
                    </td>
                    <td class="px-4 py-3">
                        <span class="badge badge-neutral"><?= e($data['sources'][$link['source']] ?? $link['source']) ?></span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <button type="button" onclick='openLinkModal(<?= json_encode($link) ?>)' class="text-text-secondary hover:text-white" title="Modifier">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>
                            <button type="button" onclick="deleteLink(<?= $link['id'] ?>, '<?= e(addslashes($link['name'])) ?>')" class="text-text-secondary hover:text-danger" title="Supprimer">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($data['linksTotalPages'] > 1): ?>
    <div class="px-4 py-3 border-t border-border flex items-center justify-between">
        <span class="text-text-secondary text-sm">
            Page <?= $data['linksPage'] ?> sur <?= $data['linksTotalPages'] ?>
        </span>
        <div class="flex items-center gap-2">
            <?php if ($data['linksPage'] > 1): ?>
            <a href="?period=<?= e($period) ?>&links_page=<?= $data['linksPage'] - 1 ?>" class="btn btn-secondary text-sm py-1.5 px-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <?php endif; ?>
            <?php if ($data['linksPage'] < $data['linksTotalPages']): ?>
            <a href="?period=<?= e($period) ?>&links_page=<?= $data['linksPage'] + 1 ?>" class="btn btn-secondary text-sm py-1.5 px-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
    </div>
</div>
<?php
}
