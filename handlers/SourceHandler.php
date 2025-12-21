<?php
/**
 * Handler pour les actions CRUD sur les sources
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

/**
 * Ajouter une nouvelle source
 */
function handleAddSource(): ?string
{
    $label = trim($_POST['source_label'] ?? '');

    if (empty($label)) {
        return 'Le nom de la source est requis.';
    }

    if (strlen($label) > 50) {
        return 'Le nom de la source est trop long (max 50 caractères).';
    }

    $slug = addSource($label);

    if (!$slug) {
        return 'Erreur lors de l\'ajout de la source.';
    }

    setFlashMessage('success', 'Source "' . e($label) . '" ajoutée avec succès.');
    header('Location: dashboard.php');
    exit;
}

/**
 * Supprimer une source
 */
function handleDeleteSource(): ?string
{
    $slug = trim($_POST['source_slug'] ?? '');

    if (empty($slug)) {
        return 'Source invalide.';
    }

    if (!deleteSource($slug)) {
        return 'Impossible de supprimer cette source (utilisée par des liens ou source par défaut).';
    }

    setFlashMessage('success', 'Source supprimée.');
    header('Location: dashboard.php');
    exit;
}
