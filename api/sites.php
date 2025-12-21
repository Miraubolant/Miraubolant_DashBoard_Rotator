<?php
/**
 * API CRUD pour les sites
 *
 * GET    /api/sites.php       - Liste tous les sites
 * GET    /api/sites.php?id=X  - Récupère un site spécifique
 * POST   /api/sites.php       - Crée un nouveau site
 * PUT    /api/sites.php?id=X  - Modifie un site
 * DELETE /api/sites.php?id=X  - Supprime un site
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

// Vérifier l'authentification
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
                $site = getSiteById($id);
                if (!$site) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Site non trouvé']);
                    exit;
                }
                // Ne pas exposer le token complet
                $site['api_token'] = substr($site['api_token'], 0, 8) . '...';
                $site['links'] = getSiteLinks($id);
                echo json_encode($site);
            } else {
                $sites = getAllSites();
                // Masquer les tokens
                foreach ($sites as &$s) {
                    $s['api_token'] = substr($s['api_token'], 0, 8) . '...';
                }
                echo json_encode($sites);
            }
            break;

        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);

            $name = trim($input['name'] ?? '');
            $baseUrl = rtrim(trim($input['base_url'] ?? ''), '/');
            $apiToken = trim($input['api_token'] ?? '');
            $isActive = isset($input['is_active']) ? (int) $input['is_active'] : 1;

            if (empty($name) || empty($baseUrl) || empty($apiToken)) {
                http_response_code(400);
                echo json_encode(['error' => 'Champs requis manquants']);
                exit;
            }

            if (!isValidUrl($baseUrl)) {
                http_response_code(400);
                echo json_encode(['error' => 'URL invalide']);
                exit;
            }

            $stmt = $pdo->prepare('INSERT INTO sites (name, base_url, api_token, is_active) VALUES (?, ?, ?, ?)');
            $stmt->execute([$name, $baseUrl, $apiToken, $isActive]);

            http_response_code(201);
            echo json_encode(['id' => $pdo->lastInsertId(), 'message' => 'Site créé']);
            break;

        case 'PUT':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requis']);
                exit;
            }

            $site = getSiteById($id);
            if (!$site) {
                http_response_code(404);
                echo json_encode(['error' => 'Site non trouvé']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);

            $name = trim($input['name'] ?? $site['name']);
            $baseUrl = rtrim(trim($input['base_url'] ?? $site['base_url']), '/');
            $apiToken = isset($input['api_token']) && !empty($input['api_token']) ? trim($input['api_token']) : $site['api_token'];
            $isActive = isset($input['is_active']) ? (int) $input['is_active'] : $site['is_active'];

            if (!isValidUrl($baseUrl)) {
                http_response_code(400);
                echo json_encode(['error' => 'URL invalide']);
                exit;
            }

            $stmt = $pdo->prepare('UPDATE sites SET name = ?, base_url = ?, api_token = ?, is_active = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$name, $baseUrl, $apiToken, $isActive, $id]);

            echo json_encode(['message' => 'Site modifié']);
            break;

        case 'DELETE':
            if (!$id) {
                http_response_code(400);
                echo json_encode(['error' => 'ID requis']);
                exit;
            }

            $site = getSiteById($id);
            if (!$site) {
                http_response_code(404);
                echo json_encode(['error' => 'Site non trouvé']);
                exit;
            }

            $stmt = $pdo->prepare('DELETE FROM sites WHERE id = ?');
            $stmt->execute([$id]);

            echo json_encode(['message' => 'Site supprimé']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Méthode non autorisée']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erreur serveur', 'details' => DEBUG_MODE ? $e->getMessage() : null]);
}
