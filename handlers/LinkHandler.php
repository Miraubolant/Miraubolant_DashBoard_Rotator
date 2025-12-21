<?php
/**
 * Handler pour les actions CRUD sur les liens
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

/**
 * Ajouter un nouveau lien
 */
function handleAddLink(PDO $pdo): ?string
{
    $name = trim($_POST['name'] ?? '');
    $originalUrl = trim($_POST['original_url'] ?? '');
    $whitenedUrl = trim($_POST['whitened_url'] ?? '');
    $source = $_POST['source'] ?? '';

    if (empty($name) || empty($originalUrl) || empty($whitenedUrl) || empty($source)) {
        return 'Tous les champs sont requis.';
    }

    if (!isValidUrl($originalUrl) || !isValidUrl($whitenedUrl)) {
        return 'URLs invalides.';
    }

    $stmt = $pdo->prepare('INSERT INTO links (name, original_url, whitened_url, source) VALUES (?, ?, ?, ?)');
    $stmt->execute([$name, $originalUrl, $whitenedUrl, $source]);

    setFlashMessage('success', 'Lien ajouté avec succès.');
    header('Location: dashboard.php');
    exit;
}

/**
 * Modifier un lien existant
 */
function handleEditLink(PDO $pdo): ?string
{
    $id = (int) ($_POST['link_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $originalUrl = trim($_POST['original_url'] ?? '');
    $whitenedUrl = trim($_POST['whitened_url'] ?? '');
    $source = $_POST['source'] ?? '';

    if (!$id || empty($name) || empty($originalUrl) || empty($whitenedUrl) || empty($source)) {
        return 'Tous les champs sont requis.';
    }

    if (!isValidUrl($originalUrl) || !isValidUrl($whitenedUrl)) {
        return 'URLs invalides.';
    }

    $stmt = $pdo->prepare('UPDATE links SET name = ?, original_url = ?, whitened_url = ?, source = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
    $stmt->execute([$name, $originalUrl, $whitenedUrl, $source, $id]);

    setFlashMessage('success', 'Lien modifié avec succès.');
    header('Location: dashboard.php');
    exit;
}

/**
 * Supprimer un lien
 */
function handleDeleteLink(PDO $pdo): ?string
{
    $id = (int) ($_POST['link_id'] ?? 0);

    if (!$id) {
        return 'ID invalide.';
    }

    $stmt = $pdo->prepare('DELETE FROM links WHERE id = ?');
    $stmt->execute([$id]);

    setFlashMessage('success', 'Lien supprimé.');
    header('Location: dashboard.php');
    exit;
}
