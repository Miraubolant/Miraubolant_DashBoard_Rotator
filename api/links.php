<?php
/**
 * API CRUD pour les liens
 *
 * GET    /api/links.php       - Liste tous les liens
 * GET    /api/links.php?id=X  - Récupère un lien spécifique
 * POST   /api/links.php       - Crée un nouveau lien
 * PUT    /api/links.php?id=X  - Modifie un lien
 * DELETE /api/links.php?id=X  - Supprime un lien
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// Vérifier l'authentification (via session ou token)
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

$pdo = getDB();
$method = $_SERVER['REQUEST_METHOD'];
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

try {
    switch ($method) {
        case 'GET':
            if ($id) {
                // Récupérer un lien spécifique
                $link = getLinkById($id);
                if (!$link) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Lien non trouvé']);
                    exit;
                }
                $link['sites'] = getLinkSites($id);
                echo json_encode($link);
            } else {
                // Lister tous les liens
                $links = getAllLinks();
                foreach ($links as &$l) {
                    $l['sites'] = getLinkSites($l['id']);
                }
                echo json_encode($links);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);

            $name = trim($input['name'] ?? '');
            $originalUrl = trim($input['original_url'] ?? '');
            $whitenedUrl = trim($input['whitened_url'] ?? '');
            $source = $input['source'] ?? '';
            $siteIds = $input['site_ids'] ?? [];

            // Validation
            if (empty($name) || empty($originalUrl) || empty($whitenedUrl) || empty($source)) {
                http_response_code(400);
                echo json_encode(['error' => 'Champs requis manquants']);
                exit;
            }

            if (!isValidUrl($originalUrl) || !isValidUrl($whitenedUrl)) {
                http_response_code(400);
                echo json_encode(['error' => 'URL invalide']);
                exit;
            }

            $stmt = $pdo->prepare('INSERT INTO links (name, original_url, whitened_url, source) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $originalUrl, $whitenedUrl, $source]);
            $linkId = $pdo->lastInsertId();

            // Associer aux sites
            if (!empty($siteIds)) {
                $stmt = $pdo->prepare('INSERT INTO link_site (link_id, site_id) VALUES (?, ?)');
                foreach ($siteIds as $siteId) {
                    $stmt->execute([$linkId, (int) $siteId]);
                }
            }

            http_response_code(201);
            echo json_encode(['id' => $linkId, 'message' => 'Lien créé']);
            break;

        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requis']);
                exit;
            }

            $link = getLinkById($id);
            if (!$link) {
                http_response_code(404);
                echo json_encode(['error' => 'Lien non trouvé']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $name = trim($input['name'] ?? $link['name']);
            $originalUrl = trim($input['original_url'] ?? $link['original_url']);
            $whitenedUrl = trim($input['whitened_url'] ?? $link['whitened_url']);
            $source = $input['source'] ?? $link['source'];
            $siteIds = $input['site_ids'] ?? null;

            if (!isValidUrl($originalUrl) || !isValidUrl($whitenedUrl)) {
                http_response_code(400);
                echo json_encode(['error' => 'URL invalide']);
                exit;
            }

            $stmt = $pdo->prepare('UPDATE links SET name = ?, original_url = ?, whitened_url = ?, source = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$name, $originalUrl, $whitenedUrl, $source, $id]);

            // Mettre à jour les associations si fournies
            if ($siteIds !== null) {
                $stmt = $pdo->prepare('DELETE FROM link_site WHERE link_id = ?');
                $stmt->execute([$id]);

                if (!empty($siteIds)) {
                    $stmt = $pdo->prepare('INSERT INTO link_site (link_id, site_id) VALUES (?, ?)');
                    foreach ($siteIds as $siteId) {
                        $stmt->execute([$id, (int) $siteId]);
                    }
                }
            }

            echo json_encode(['message' => 'Lien modifié']);
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requis']);
                exit;
            }

            $link = getLinkById($id);
            if (!$link) {
                http_response_code(404);
                echo json_encode(['error' => 'Lien non trouvé']);
                exit;
            }

            $stmt = $pdo->prepare('DELETE FROM links WHERE id = ?');
            $stmt->execute([$id]);

            echo json_encode(['message' => 'Lien supprimé']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur', 'details' => DEBUG_MODE ? $e->getMessage() : null]);
}
