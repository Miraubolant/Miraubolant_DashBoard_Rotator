<?php
/**
 * Handler pour les actions PopCash
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

/**
 * Modifier le statut d'une campagne PopCash
 */
function handleCampaignStatus(): ?string
{
    // Priorité à l'ID manuel, sinon ID de la liste
    $campaignId = (int) ($_POST['manual_campaign_id'] ?? 0);
    if (!$campaignId) {
        $campaignId = (int) ($_POST['campaign_id'] ?? 0);
    }
    $status = $_POST['campaign_status'] ?? '';

    if (!$campaignId || empty($status)) {
        return 'Campagne ou statut invalide.';
    }

    $result = updatePopcashCampaignStatus($campaignId, $status);

    if ($result['success']) {
        $statusLabels = ['active' => 'démarrée (Running)', 'paused' => 'mise en pause (Paused)'];
        setFlashMessage('success', 'Campagne #' . $campaignId . ' ' . ($statusLabels[$status] ?? $status) . ' avec succès.');
    } else {
        setFlashMessage('error', $result['error'] ?? 'Erreur lors de la mise à jour de la campagne #' . $campaignId . '.');
    }

    header('Location: dashboard.php');
    exit;
}

/**
 * Sauvegarder la clé API PopCash
 */
function handleSavePopcashKey(): ?string
{
    $apiKey = trim($_POST['popcash_api_key'] ?? '');

    // Sauvegarder la clé (même si vide, pour permettre la suppression)
    setSetting('popcash_api_key', $apiKey);

    if (empty($apiKey)) {
        setFlashMessage('success', 'Clé API PopCash supprimée.');
    } else {
        // Tester la connexion avec la nouvelle clé
        $account = getPopcashAccount();
        if ($account) {
            setFlashMessage('success', 'Clé API PopCash sauvegardée et connectée avec succès.');
        } else {
            setFlashMessage('warning', 'Clé API sauvegardée mais la connexion a échoué. Vérifiez votre clé.');
        }
    }

    header('Location: dashboard.php');
    exit;
}

/**
 * Ajouter une campagne à suivre
 */
function handleAddCampaign(): ?string
{
    $campaignId = (int) ($_POST['campaign_id'] ?? 0);

    if (!$campaignId) {
        setFlashMessage('error', 'ID de campagne invalide.');
        header('Location: dashboard.php');
        exit;
    }

    addCampaignId($campaignId);
    setFlashMessage('success', 'Campagne #' . $campaignId . ' ajoutée.');

    header('Location: dashboard.php');
    exit;
}

/**
 * Retirer une campagne suivie
 */
function handleRemoveCampaign(): ?string
{
    $campaignId = (int) ($_POST['campaign_id'] ?? 0);

    if (!$campaignId) {
        setFlashMessage('error', 'ID de campagne invalide.');
        header('Location: dashboard.php');
        exit;
    }

    removeCampaignId($campaignId);
    setFlashMessage('success', 'Campagne #' . $campaignId . ' supprimée.');

    header('Location: dashboard.php');
    exit;
}
