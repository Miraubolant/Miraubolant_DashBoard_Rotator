<?php
/**
 * API endpoint pour gérer les sources
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

startSecureSession();

if (!isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Ajouter une source
    $input = json_decode(file_get_contents('php://input'), true);
    $label = trim($input['label'] ?? '');

    if (empty($label)) {
        http_response_code(400);
        echo json_encode(['error' => 'Le nom de la source est requis']);
        exit;
    }

    if (strlen($label) > 50) {
        http_response_code(400);
        echo json_encode(['error' => 'Le nom est trop long (max 50 caractères)']);
        exit;
    }

    $slug = addSource($label);

    if (!$slug) {
        http_response_code(500);
        echo json_encode(['error' => 'Erreur lors de l\'ajout']);
        exit;
    }

    echo json_encode([
        'success' => true,
        'slug' => $slug,
        'label' => $label
    ]);
    exit;
}

if ($method === 'DELETE') {
    // Supprimer une source
    $input = json_decode(file_get_contents('php://input'), true);
    $slug = trim($input['slug'] ?? '');

    if (empty($slug)) {
        http_response_code(400);
        echo json_encode(['error' => 'Slug requis']);
        exit;
    }

    if (!deleteSource($slug)) {
        http_response_code(400);
        echo json_encode(['error' => 'Impossible de supprimer (utilisée ou par défaut)']);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

if ($method === 'GET') {
    // Liste des sources
    echo json_encode([
        'success' => true,
        'sources' => getAllSources()
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Méthode non autorisée']);
