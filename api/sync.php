<?php
/**
 * API de synchronisation des liens vers les rotators
 *
 * POST /api/sync.php
 * Body JSON :
 * - site_ids: array - IDs des sites à synchroniser
 * - link_ids: array (optionnel) - IDs des liens spécifiques à sync (sinon tous)
 *
 * Pour chaque site, envoie toutes les URLs blanchies des liens associés
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

// Accepter uniquement POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée']);
    exit;
}

$pdo = getDB();
$input = json_decode(file_get_contents('php://input'), true);

$siteIds = $input['site_ids'] ?? [];
$linkIds = $input['link_ids'] ?? null;

if (empty($siteIds)) {
    http_response_code(400);
    echo json_encode(['error' => 'site_ids requis']);
    exit;
}

$results = [];
$successCount = 0;
$errorCount = 0;

foreach ($siteIds as $siteId) {
    $site = getSiteById((int) $siteId);

    if (!$site) {
        $results[] = [
            'site_id' => $siteId,
            'success' => false,
            'error' => 'Site non trouvé'
        ];
        $errorCount++;
        continue;
    }

    if (!$site['is_active']) {
        $results[] = [
            'site_id' => $siteId,
            'site_name' => $site['name'],
            'success' => false,
            'error' => 'Site inactif'
        ];
        $errorCount++;
        continue;
    }

    // Récupérer les liens à synchroniser pour ce site
    $links = getSiteLinks($site['id']);

    // Filtrer par link_ids si spécifié
    if ($linkIds !== null) {
        $links = array_filter($links, fn($l) => in_array($l['id'], $linkIds));
    }

    if (empty($links)) {
        $results[] = [
            'site_id' => $siteId,
            'site_name' => $site['name'],
            'success' => false,
            'error' => 'Aucun lien associé à ce site'
        ];
        $errorCount++;
        continue;
    }

    // Synchroniser
    $syncResult = syncLinksToSite($site, $links);

    if ($syncResult['success']) {
        // Marquer les liens comme synchronisés
        $stmt = $pdo->prepare('UPDATE link_site SET is_synced = 1, synced_at = CURRENT_TIMESTAMP WHERE site_id = ?');
        $stmt->execute([$siteId]);

        $results[] = [
            'site_id' => $siteId,
            'site_name' => $site['name'],
            'success' => true,
            'urls_synced' => count($links)
        ];
        $successCount++;
    } else {
        $results[] = [
            'site_id' => $siteId,
            'site_name' => $site['name'],
            'success' => false,
            'error' => $syncResult['error'] ?: 'HTTP ' . $syncResult['http_code']
        ];
        $errorCount++;
    }
}

$response = [
    'success' => $errorCount === 0,
    'summary' => [
        'total' => count($siteIds),
        'success' => $successCount,
        'errors' => $errorCount
    ],
    'results' => $results
];

http_response_code($errorCount === 0 ? 200 : ($successCount > 0 ? 207 : 500));
echo json_encode($response, JSON_PRETTY_PRINT);
