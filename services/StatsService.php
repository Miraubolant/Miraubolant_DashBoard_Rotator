<?php
/**
 * Service pour la récupération et l'agrégation des statistiques
 */

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

/**
 * Récupère toutes les données nécessaires au dashboard
 */
function fetchDashboardData(int $linksPage = 1, int $linksPerPage = 10): array
{
    $linksTotal = getLinksCount();
    $totalPages = max(1, (int) ceil($linksTotal / $linksPerPage));
    // S'assurer que la page demandée est valide
    $linksPage = max(1, min($linksPage, $totalPages));

    // Récupérer la distribution des sources
    $pdo = getDB();
    $sourcesDistrib = [];
    $stmt = $pdo->query('SELECT source, COUNT(*) as count FROM links GROUP BY source ORDER BY count DESC');
    while ($row = $stmt->fetch()) {
        $sourcesDistrib[$row['source']] = (int) $row['count'];
    }

    // Dernière synchronisation réussie
    $lastSyncStmt = $pdo->query("SELECT created_at, message FROM sync_logs WHERE status = 'success' ORDER BY created_at DESC LIMIT 1");
    $lastSync = $lastSyncStmt->fetch();

    return [
        'links' => getLinksPaginated($linksPage, $linksPerPage),
        'linksTotal' => $linksTotal,
        'linksPage' => $linksPage,
        'linksPerPage' => $linksPerPage,
        'linksTotalPages' => $totalPages,
        'sites' => getAllSites(),
        'activeSites' => getActiveSites(),
        'recentLogs' => getRecentSyncLogs(5),
        'sources' => getLinkSources(),
        'sourcesDistrib' => $sourcesDistrib,
        'lastSync' => $lastSync
    ];
}

/**
 * Récupère et agrège les statistiques de tous les rotators actifs
 */
function fetchRotatorStats(array $activeSites, string $period = '24h'): array
{
    $totalClicks = 0;
    $totalUniqueIps = 0;
    $allCountries = [];
    $allCities = [];
    $allBrowsers = [];
    $allDevices = [];
    $allUrls = [];
    $allOS = [];

    foreach ($activeSites as $site) {
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
            foreach (($stats['stats']['browsers'] ?? []) as $browser => $count) {
                $allBrowsers[$browser] = ($allBrowsers[$browser] ?? 0) + $count;
            }
            foreach (($stats['stats']['devices'] ?? []) as $device => $count) {
                $allDevices[$device] = ($allDevices[$device] ?? 0) + $count;
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
    arsort($allBrowsers);
    arsort($allDevices);
    arsort($allUrls);
    arsort($allOS);

    return [
        'totalClicks' => $totalClicks,
        'totalUniqueIps' => $totalUniqueIps,
        'countries' => $allCountries,
        'cities' => $allCities,
        'browsers' => $allBrowsers,
        'devices' => $allDevices,
        'urls' => $allUrls,
        'os' => $allOS
    ];
}

/**
 * Récupère les données PopCash
 */
function fetchPopcashData(): array
{
    $apiKey = getPopcashApiKey();

    // Toujours retourner un tableau pour permettre l'affichage de la modale
    $result = [
        'configured' => !empty($apiKey),
        'account' => null,
        'campaigns' => ['items' => []]
    ];

    if (empty($apiKey)) {
        return $result;
    }

    $account = getPopcashAccount();
    $campaigns = getPopcashCampaigns();

    if (!$account) {
        return $result;
    }

    // Normaliser la structure des campagnes
    // L'API peut retourner { items: [...] } ou directement un tableau
    if ($campaigns && !isset($campaigns['items'])) {
        // Si c'est un tableau indexé (liste de campagnes)
        if (is_array($campaigns) && (empty($campaigns) || isset($campaigns[0]))) {
            $campaigns = ['items' => $campaigns];
        }
    }

    return [
        'configured' => true,
        'account' => $account,
        'campaigns' => $campaigns ?? ['items' => []]
    ];
}
