<?php
/**
 * Composant: Modal de configuration PopCash
 * Permet de configurer l'API et de gérer les campagnes
 */

/**
 * Rend la modal PopCash complète
 */
function renderPopcashModal(array $popcashData): void
{
?>
<div class="modal-backdrop" id="campaignModalBackdrop" onclick="closeCampaignModal()"></div>
<div class="modal-container" id="campaignModal">
    <div class="modal-content" onclick="event.stopPropagation()">
        <div class="modal-header">
            <h3 class="text-lg font-semibold text-white">PopCash</h3>
            <button type="button" onclick="closeCampaignModal()" class="text-text-secondary hover:text-white">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Section Configuration API -->
        <form method="POST" id="popcashApiForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="save_popcash_key">
            <div class="modal-body space-y-4 border-b border-border pb-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Clé API PopCash</label>
                    <div class="flex gap-2">
                        <input type="password" name="popcash_api_key" id="popcashApiKey" class="input flex-1"
                               placeholder="Entrez votre clé API PopCash"
                               value="<?= e(getPopcashApiKey()) ?>">
                        <button type="button" onclick="toggleApiKeyVisibility()" class="btn btn-secondary px-3" title="Afficher/Masquer">
                            <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                        <button type="submit" class="btn btn-primary px-4">Sauvegarder</button>
                    </div>
                    <p class="text-xs text-text-secondary mt-1">Obtenez votre clé API sur <a href="https://popcash.net/dashboard/api" target="_blank" class="text-accent hover:underline">popcash.net/dashboard/api</a></p>
                </div>
                <?php if ($popcashData['configured'] && $popcashData['account']): ?>
                <div class="flex items-center gap-4 p-2 bg-green-500/10 rounded-lg border border-green-500/20">
                    <span class="text-green-400 text-sm flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                        Connecté
                    </span>
                    <span class="text-white text-sm">Balance: <strong class="text-green-400">$<?= number_format($popcashData['account']['balance'] ?? 0, 2) ?></strong></span>
                </div>
                <?php elseif ($popcashData['configured']): ?>
                <div class="p-2 bg-red-500/10 rounded-lg border border-red-500/20">
                    <span class="text-red-400 text-sm flex items-center gap-2">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        Clé API invalide ou erreur de connexion
                    </span>
                </div>
                <?php endif; ?>
            </div>
        </form>

        <!-- Section Gestion des campagnes -->
        <?php if ($popcashData['configured'] && $popcashData['account']): ?>
        <?php $savedCampaigns = getSavedCampaignIds(); ?>

        <!-- Campagnes sauvegardées -->
        <div class="modal-body space-y-4 border-t border-white/10 pt-4">
            <h4 class="text-sm font-medium text-white">Mes campagnes</h4>

            <?php if (!empty($savedCampaigns)): ?>
            <div class="space-y-2">
                <?php foreach ($savedCampaigns as $cid): ?>
                <div class="flex items-center justify-between bg-bg-tertiary rounded-lg px-3 py-2">
                    <span class="text-white font-mono">#<?= $cid ?></span>
                    <form method="POST" class="inline">
                        <?= csrfField() ?>
                        <input type="hidden" name="action" value="remove_campaign">
                        <input type="hidden" name="campaign_id" value="<?= $cid ?>">
                        <button type="submit" class="text-red-400 hover:text-red-300 p-1" title="Supprimer">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p class="text-text-secondary text-sm">Aucune campagne enregistrée</p>
            <?php endif; ?>

            <!-- Ajouter une campagne -->
            <form method="POST" class="flex gap-2">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="add_campaign">
                <input type="number" name="campaign_id" class="input flex-1" placeholder="ID de campagne" min="1" required>
                <button type="submit" class="btn btn-primary px-3">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                </button>
            </form>
        </div>

        <!-- Contrôle des campagnes -->
        <?php if (!empty($savedCampaigns)): ?>
        <form method="POST" id="campaignForm">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="campaign_status">
            <input type="hidden" name="campaign_status" id="campaignStatusInput" value="">
            <div class="modal-body space-y-4 border-t border-white/10 pt-4">
                <h4 class="text-sm font-medium text-white">Contrôler une campagne</h4>

                <div>
                    <label class="block text-sm font-medium mb-2">Campagne</label>
                    <select name="manual_campaign_id" id="campaignSelect" class="input" required>
                        <option value="">Sélectionner...</option>
                        <?php foreach ($savedCampaigns as $cid): ?>
                        <option value="<?= $cid ?>">#<?= $cid ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-2">Action</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button type="button" onclick="setCampaignStatus('active')" class="btn btn-success py-2 text-sm campaign-action-btn" data-status="active">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Démarrer
                        </button>
                        <button type="button" onclick="setCampaignStatus('paused')" class="btn btn-warning py-2 text-sm campaign-action-btn" data-status="paused">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Pause
                        </button>
                    </div>
                </div>
            </div>
        </form>
        <?php endif; ?>
        <?php endif; ?>

        <div class="modal-footer">
            <button type="button" onclick="closeCampaignModal()" class="btn btn-secondary">Fermer</button>
        </div>
    </div>
</div>
<?php
}
