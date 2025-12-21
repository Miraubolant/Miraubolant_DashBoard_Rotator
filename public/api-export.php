<?php
/**
 * API endpoint pour exporter les statistiques en CSV ou JSON
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

$format = $_GET['format'] ?? 'json';
$period = $_GET['period'] ?? '24h';

// Récupérer les stats de tous les rotators
$activeSites = getActiveSites();
$stats = [
    'generated_at' => date('c'),
    'period' => $period,
    'total_clicks' => 0,
    'total_unique_ips' => 0,
    'devices' => [],
    'browsers' => [],
    'countries' => [],
    'urls' => []
];

foreach ($activeSites as $site) {
    $apiUrl = rtrim($site['base_url'], '/') . '/api-logs.php?period=' . urlencode($period);
    $result = httpRequest('GET', $apiUrl);

    if ($result['success'] && $result['body']) {
        $data = json_decode($result['body'], true);

        if ($data && isset($data['success']) && $data['success'] && isset($data['stats'])) {
            $stats['total_clicks'] += $data['stats']['total_clicks'] ?? 0;
            $stats['total_unique_ips'] += $data['stats']['unique_ips'] ?? 0;

            foreach (($data['stats']['devices'] ?? []) as $device => $count) {
                $stats['devices'][$device] = ($stats['devices'][$device] ?? 0) + $count;
            }
            foreach (($data['stats']['browsers'] ?? []) as $browser => $count) {
                $stats['browsers'][$browser] = ($stats['browsers'][$browser] ?? 0) + $count;
            }
            foreach (($data['stats']['top_countries'] ?? []) as $country => $count) {
                $stats['countries'][$country] = ($stats['countries'][$country] ?? 0) + $count;
            }
            foreach (($data['stats']['top_urls'] ?? []) as $url => $count) {
                $stats['urls'][$url] = ($stats['urls'][$url] ?? 0) + $count;
            }
        }
    }
}

// Trier les données
arsort($stats['devices']);
arsort($stats['browsers']);
arsort($stats['countries']);
arsort($stats['urls']);

if ($format === 'csv') {
    // Export CSV
    $filename = 'stats_' . $period . '_' . date('Y-m-d_His') . '.csv';
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // BOM UTF-8 pour Excel
    fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

    // En-tête
    fputcsv($output, ['Statistiques - Période: ' . $period, 'Généré le: ' . date('d/m/Y H:i')], ';');
    fputcsv($output, [], ';');

    // Résumé
    fputcsv($output, ['RÉSUMÉ'], ';');
    fputcsv($output, ['Total clics', $stats['total_clicks']], ';');
    fputcsv($output, ['IPs uniques', $stats['total_unique_ips']], ';');
    fputcsv($output, [], ';');

    // Appareils
    fputcsv($output, ['APPAREILS', 'Clics'], ';');
    foreach ($stats['devices'] as $device => $count) {
        fputcsv($output, [$device, $count], ';');
    }
    fputcsv($output, [], ';');

    // Navigateurs
    fputcsv($output, ['NAVIGATEURS', 'Clics'], ';');
    foreach ($stats['browsers'] as $browser => $count) {
        fputcsv($output, [$browser, $count], ';');
    }
    fputcsv($output, [], ';');

    // Pays
    fputcsv($output, ['PAYS', 'Clics'], ';');
    foreach ($stats['countries'] as $country => $count) {
        fputcsv($output, [$country, $count], ';');
    }
    fputcsv($output, [], ';');

    // URLs
    fputcsv($output, ['URLs', 'Clics'], ';');
    foreach ($stats['urls'] as $url => $count) {
        fputcsv($output, [$url, $count], ';');
    }

    fclose($output);
    exit;
}

// Export JSON (défaut)
$filename = 'stats_' . $period . '_' . date('Y-m-d_His') . '.json';
header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

echo json_encode($stats, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
