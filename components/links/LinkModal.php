<?php
/**
 * Composant: Modal d'ajout/édition de lien
 */

/**
 * Rend la modal pour ajouter ou modifier un lien
 */
function renderLinkModal(array $sources): void
{
?>
<div class="modal-backdrop" id="linkModalBackdrop" onclick="closeLinkModal()"></div>
<div class="modal-container" id="linkModal">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-white" id="linkModalTitle">Nouveau lien</h3>
            <button type="button" onclick="closeLinkModal()" class="text-text-secondary hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" id="linkForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" id="linkAction" value="add_link">
            <input type="hidden" name="link_id" id="linkId" value="">
            <div class="modal-body space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Nom</label>
                    <input type="text" name="name" id="linkName" class="input" placeholder="Ex: Campagne Janvier" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">URL de destination</label>
                    <input type="url" name="original_url" id="linkOriginalUrl" class="input" placeholder="https://votresite.com/page" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">URL blanchie</label>
                    <input type="url" name="whitened_url" id="linkWhitenedUrl" class="input" placeholder="https://bit.ly/xxxxx" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Source</label>
                    <select name="source" id="linkSource" class="input" required>
                        <option value="">Sélectionnez...</option>
                        <?php foreach ($sources as $key => $label): ?>
                        <option value="<?= $key ?>"><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeLinkModal()" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Formulaire caché pour suppression -->
<form method="POST" id="deleteLinkForm" style="display: none;">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="delete_link">
    <input type="hidden" name="link_id" id="deleteLinkId">
</form>
<?php
}
