<?php
/**
 * API endpoint pour récupérer les stats en temps réel
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

$period = $_GET['period'] ?? '24h';
$validPeriods = ['1h', '6h', '24h', '48h', '7d', '30d', '90d', '1y', 'all'];
if (!in_array($period, $validPeriods)) {
    $period = '24h';
}

$sites = getActiveSites();

$totalClicks = 0;
$totalUniqueIps = 0;
$allCountries = [];
$allCities = [];
$allUrls = [];
$allDevices = [];
$allBrowsers = [];
$allOS = [];

foreach ($sites as $site) {
    $stats = fetchSiteStats($site, $period);

    if ($stats && isset($stats['success']) && $stats['success'] && isset($stats['stats'])) {
        $totalClicks += $stats['stats']['total_clicks'] ?? 0;
        $totalUniqueIps += $stats['stats']['unique_ips'] ?? 0;

        foreach (($stats['stats']['top_countries'] ?? []) as $country => $count) {
            $allCountries[$country] = ($allCountries[$country] ?? 0) + $count;
        }
        foreach (($stats['stats']['top_cities'] ?? []) as $city => $count) {
            if (!empty($city)) {
                $allCities[$city] = ($allCities[$city] ?? 0) + $count;
            }
        }
        foreach (($stats['stats']['top_urls'] ?? []) as $url => $count) {
            $allUrls[$url] = ($allUrls[$url] ?? 0) + $count;
        }
        foreach (($stats['stats']['devices'] ?? []) as $device => $count) {
            $allDevices[$device] = ($allDevices[$device] ?? 0) + $count;
        }
        foreach (($stats['stats']['browsers'] ?? []) as $browser => $count) {
            $allBrowsers[$browser] = ($allBrowsers[$browser] ?? 0) + $count;
        }
        foreach (($stats['stats']['os'] ?? []) as $os => $count) {
            if (!empty($os)) {
                $allOS[$os] = ($allOS[$os] ?? 0) + $count;
            }
        }
    }
}

arsort($allCountries);
arsort($allCities);
arsort($allUrls);
arsort($allDevices);
arsort($allBrowsers);
arsort($allOS);

echo json_encode([
    'success' => true,
    'timestamp' => date('c'),
    'period' => $period,
    'stats' => [
        'totalClicks' => $totalClicks,
        'totalUniqueIps' => $totalUniqueIps,
        'linksCount' => getLinksCount(),
        'rotatorsCount' => count($sites)
    ],
    'countries' => $allCountries,
    'cities' => $allCities,
    'urls' => $allUrls,
    'devices' => $allDevices,
    'browsers' => $allBrowsers,
    'os' => $allOS
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
