<?php
/**
 * Handler pour les actions de synchronisation
 */

require_once __DIR__ . '/../includes/functions.php';

/**
 * Synchroniser tous les liens vers tous les rotators actifs
 */
function handleSyncAll(): ?string
{
    $result = syncAllLinksToAllSites();

    if ($result['success']) {
        setFlashMessage('success', $result['message']);
    } else {
        setFlashMessage('error', $result['message']);
    }

    header('Location: dashboard.php');
    exit;
}
