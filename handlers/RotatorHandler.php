<?php
/**
 * Handler pour les actions CRUD sur les rotators
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

/**
 * Ajouter un nouveau rotator
 */
function handleAddRotator(PDO $pdo): ?string
{
    $name = trim($_POST['name'] ?? '');
    $baseUrl = trim($_POST['base_url'] ?? '');
    $apiToken = trim($_POST['api_token'] ?? '');

    if (empty($name) || empty($baseUrl) || empty($apiToken)) {
        return 'Tous les champs sont requis.';
    }

    if (!isValidUrl($baseUrl)) {
        return 'URL invalide.';
    }

    $stmt = $pdo->prepare('INSERT INTO sites (name, base_url, api_token, is_active) VALUES (?, ?, ?, 1)');
    $stmt->execute([$name, $baseUrl, $apiToken]);

    setFlashMessage('success', 'Rotator ajouté avec succès.');
    header('Location: dashboard.php');
    exit;
}

/**
 * Modifier un rotator existant
 */
function handleEditRotator(PDO $pdo): ?string
{
    $id = (int) ($_POST['rotator_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $baseUrl = trim($_POST['base_url'] ?? '');
    $apiToken = trim($_POST['api_token'] ?? '');
    $isActive = isset($_POST['is_active']) ? 1 : 0;

    if (!$id || empty($name) || empty($baseUrl) || empty($apiToken)) {
        return 'Tous les champs sont requis.';
    }

    $stmt = $pdo->prepare('UPDATE sites SET name = ?, base_url = ?, api_token = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
    $stmt->execute([$name, $baseUrl, $apiToken, $isActive, $id]);

    setFlashMessage('success', 'Rotator modifié avec succès.');
    header('Location: dashboard.php');
    exit;
}

/**
 * Supprimer un rotator
 */
function handleDeleteRotator(PDO $pdo): ?string
{
    $id = (int) ($_POST['rotator_id'] ?? 0);

    if (!$id) {
        return 'ID invalide.';
    }

    $stmt = $pdo->prepare('DELETE FROM sites WHERE id = ?');
    $stmt->execute([$id]);

    setFlashMessage('success', 'Rotator supprimé.');
    header('Location: dashboard.php');
    exit;
}
