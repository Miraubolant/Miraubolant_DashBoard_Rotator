<?php
/**
 * API endpoint pour récupérer les requêtes entrantes en temps réel
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
header('Cache-Control: no-cache, no-store, must-revalidate');

$limit = min(50, max(5, (int) ($_GET['limit'] ?? 20)));

$response = [
    'success' => true,
    'timestamp' => date('c'),
    'logs' => []
];

// Récupérer les requêtes récentes de tous les rotators
$sites = getActiveSites();
foreach ($sites as $site) {
    $perSiteLimit = ceil($limit / max(1, count($sites)));
    $apiUrl = rtrim($site['base_url'], '/') . '/api-logs.php?format=logs&limit=' . $perSiteLimit;

    $result = httpRequest('GET', $apiUrl);

    if ($result['success'] && $result['body']) {
        $data = json_decode($result['body'], true);

        if ($data && isset($data['logs']) && is_array($data['logs'])) {
            foreach ($data['logs'] as $log) {
                $response['logs'][] = [
                    'type' => 'request',
                    'site' => $site['name'],
                    'country' => $log['country'] ?? 'XX',
                    'city' => $log['city'] ?? '',
                    'url' => $log['url'] ?? '',
                    'referer' => $log['referer'] ?? '',
                    'device' => $log['device'] ?? 'desktop',
                    'browser' => $log['browser'] ?? '',
                    'os' => $log['os'] ?? '',
                    'ip' => isset($log['ip']) ? substr($log['ip'], 0, -3) . '***' : '',
                    'timestamp' => $log['timestamp'] ?? '',
                    'relative' => formatRelativeDate($log['timestamp'] ?? '')
                ];
            }
        }
    }
}

// Trier par timestamp décroissant
usort($response['logs'], function($a, $b) {
    return strtotime($b['timestamp'] ?? '0') - strtotime($a['timestamp'] ?? '0');
});

// Limiter le nombre de logs
$response['logs'] = array_slice($response['logs'], 0, $limit);

echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
