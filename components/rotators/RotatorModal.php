<?php
/**
 * Composant: Modal d'ajout/édition de rotator
 */

/**
 * Rend la modal pour ajouter ou modifier un rotator
 */
function renderRotatorModal(): void
{
?>
<div class="modal-backdrop" id="rotatorModalBackdrop" onclick="closeRotatorModal()"></div>
<div class="modal-container" id="rotatorModal">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-white" id="rotatorModalTitle">Nouveau rotator</h3>
            <button type="button" onclick="closeRotatorModal()" class="text-text-secondary hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <form method="POST" id="rotatorForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" id="rotatorAction" value="add_rotator">
            <input type="hidden" name="rotator_id" id="rotatorId" value="">
            <div class="modal-body space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Nom</label>
                    <input type="text" name="name" id="rotatorName" class="input" placeholder="Ex: Rotator FR" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">URL de base</label>
                    <input type="url" name="base_url" id="rotatorBaseUrl" class="input" placeholder="https://rotator.example.com" required>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Token API</label>
                    <input type="text" name="api_token" id="rotatorApiToken" class="input" placeholder="votre_token_secret" required>
                </div>
                <div id="rotatorActiveContainer" style="display: none;">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="rotatorIsActive" class="rounded border-border bg-bg-primary text-accent" checked>
                        <span class="text-sm">Actif</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeRotatorModal()" class="btn btn-secondary">Annuler</button>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
</div>

<!-- Formulaire caché pour suppression -->
<form method="POST" id="deleteRotatorForm" style="display: none;">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="delete_rotator">
    <input type="hidden" name="rotator_id" id="deleteRotatorId">
</form>
<?php
}
