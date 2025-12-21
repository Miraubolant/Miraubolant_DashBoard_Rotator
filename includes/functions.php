<?php
/**
 * Fonctions utilitaires diverses
 */

/**
 * Sources disponibles pour les liens blanchis
 */
function getLinkSources(): array
{
    return [
        'twitter' => 'Twitter / X',
        'linkedin' => 'LinkedIn',
        'pinterest' => 'Pinterest',
        'medium' => 'Medium',
        'github' => 'GitHub',
        'youtube' => 'YouTube',
        'reddit' => 'Reddit',
        'tumblr' => 'Tumblr',
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'tiktok' => 'TikTok',
        'bitly' => 'Bitly',
        'rebrandly' => 'Rebrandly',
        'shorturl' => 'Short URL',
        'autre' => 'Autre'
    ];
}

/**
 * Valide une URL
 */
function isValidUrl(string $url): bool
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }

    $parsed = parse_url($url);
    return isset($parsed['scheme']) && in_array($parsed['scheme'], ['http', 'https']);
}

/**
 * Échapper une valeur pour l'affichage HTML
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Formater une date pour l'affichage
 */
function formatDate(?string $date, string $format = 'd/m/Y H:i'): string
{
    if (empty($date)) {
        return '-';
    }

    $timestamp = strtotime($date);
    return date($format, $timestamp);
}

/**
 * Formater une date relative (il y a X minutes, etc.)
 */
function formatRelativeDate(?string $date): string
{
    if (empty($date)) {
        return 'Jamais';
    }

    $timestamp = strtotime($date);
    $diff = time() - $timestamp;

    if ($diff < 60) {
        return 'À l\'instant';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return 'Il y a ' . $minutes . ' min';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return 'Il y a ' . $hours . 'h';
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return 'Il y a ' . $days . 'j';
    } else {
        return formatDate($date, 'd/m/Y');
    }
}

/**
 * Tronquer un texte
 */
function truncate(string $text, int $length = 50): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }

    return mb_substr($text, 0, $length) . '...';
}

/**
 * Envoie une requête HTTP
 */
function httpRequest(string $method, string $url, array $headers = [], ?array $data = null): array
{
    $ch = curl_init();

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_HTTPHEADER => $headers
    ];

    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        if ($data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
    }

    curl_setopt_array($ch, $options);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    $errno = curl_errno($ch);

    curl_close($ch);

    // Améliorer le message d'erreur
    if ($errno) {
        $error = "cURL error $errno: $error";
    }

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'body' => $response,
        'error' => $error
    ];
}

/**
 * Synchronise les liens vers un site rotator
 */
function syncLinksToSite(array $site, array $links): array
{
    $urls = array_map(fn($link) => $link['whitened_url'], $links);

    $apiUrl = rtrim($site['base_url'], '/') . '/api-update-urls.php';

    $headers = [
        'Authorization: Bearer ' . $site['api_token'],
        'Content-Type: application/json'
    ];

    $result = httpRequest('POST', $apiUrl, $headers, ['urls' => $urls]);

    // Construire le message de log
    if ($result['success']) {
        $message = 'Sync réussie - ' . count($urls) . ' URLs';
    } else {
        $message = 'Erreur: ';
        if ($result['error']) {
            $message .= $result['error'];
        } else {
            $message .= 'HTTP ' . $result['http_code'];
        }
        // Ajouter le body de la réponse si présent (peut contenir le message d'erreur du serveur)
        if ($result['body']) {
            $bodyData = json_decode($result['body'], true);
            if ($bodyData && isset($bodyData['error'])) {
                $message .= ' - ' . $bodyData['error'];
            }
        }
        $message .= ' (URL: ' . $apiUrl . ')';
    }

    // Logger le résultat
    $pdo = getDB();
    $stmt = $pdo->prepare('INSERT INTO sync_logs (site_id, status, message) VALUES (?, ?, ?)');
    $stmt->execute([
        $site['id'],
        $result['success'] ? 'success' : 'error',
        $message
    ]);

    return $result;
}

/**
 * Teste la connexion à un site rotator
 */
function testSiteConnection(array $site): array
{
    $apiUrl = rtrim($site['base_url'], '/') . '/api-health.php';

    $result = httpRequest('GET', $apiUrl);

    if (!$result['success']) {
        return [
            'success' => false,
            'message' => 'Connexion échouée : ' . ($result['error'] ?: 'HTTP ' . $result['http_code'])
        ];
    }

    $data = json_decode($result['body'], true);

    if (!$data) {
        return [
            'success' => false,
            'message' => 'Réponse invalide du serveur'
        ];
    }

    return [
        'success' => true,
        'message' => 'Connexion OK',
        'data' => $data
    ];
}

/**
 * Récupère les stats d'un site rotator
 */
function fetchSiteStats(array $site, string $period = '24h'): ?array
{
    $apiUrl = rtrim($site['base_url'], '/') . '/api-logs.php?period=' . urlencode($period);

    $result = httpRequest('GET', $apiUrl);

    if (!$result['success']) {
        return null;
    }

    return json_decode($result['body'], true);
}

/**
 * Affiche un message flash
 */
function setFlashMessage(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Récupère et supprime le message flash
 */
function getFlashMessage(): ?array
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

/**
 * ===== PopCash API Functions =====
 */

/**
 * Effectue une requête vers l'API PopCash
 */
function popcashRequest(string $method, string $endpoint, ?array $data = null): array
{
    $apiKey = getPopcashApiKey();
    if (empty($apiKey)) {
        return ['success' => false, 'error' => 'Clé API PopCash non configurée'];
    }

    $url = POPCASH_API_URL . $endpoint;

    $headers = [
        'X-Api-Key: ' . $apiKey,
        'Content-Type: application/json',
        'Accept: application/json'
    ];

    $ch = curl_init();
    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_SSL_VERIFYPEER => true
    ];

    if ($method === 'POST') {
        $options[CURLOPT_POST] = true;
        if ($data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
    } elseif ($method === 'PUT') {
        $options[CURLOPT_CUSTOMREQUEST] = 'PUT';
        if ($data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
    } elseif ($method === 'PATCH') {
        $options[CURLOPT_CUSTOMREQUEST] = 'PATCH';
        if ($data !== null) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
    } elseif ($method === 'DELETE') {
        $options[CURLOPT_CUSTOMREQUEST] = 'DELETE';
    }

    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        return ['success' => false, 'error' => $error];
    }

    $body = json_decode($response, true);

    return [
        'success' => $httpCode >= 200 && $httpCode < 300,
        'http_code' => $httpCode,
        'data' => $body,
        'error' => $httpCode >= 400 ? ($body['message'] ?? 'Erreur API') : null
    ];
}

/**
 * Récupère les infos du compte PopCash
 */
function getPopcashAccount(): ?array
{
    $result = popcashRequest('GET', '/me');
    return $result['success'] ? $result['data'] : null;
}

/**
 * Récupère les campagnes PopCash
 */
function getPopcashCampaigns(int $page = 1, int $perPage = 50): ?array
{
    $result = popcashRequest('GET', "/campaigns?page=$page&perPage=$perPage");
    return $result['success'] ? $result['data'] : null;
}

/**
 * Récupère le rapport advertiser PopCash
 */
function getPopcashAdvertiserReport(string $startDate, string $endDate, int $type = 0): ?array
{
    $result = popcashRequest('POST', '/reports/advertiser', [
        'startDate' => $startDate,
        'endDate' => $endDate,
        'type' => $type // 0=Day, 1=Country, 2=Website, 3=Campaign
    ]);
    return $result['success'] ? $result['data'] : null;
}

/**
 * Met à jour le statut d'une campagne PopCash
 * @param int $campaignId ID de la campagne
 * @param string $status Nouveau statut: 'active' (1=RUNNING), 'paused' (3=PAUSED)
 *
 * Statuts API PopCash:
 * 0=PENDING, 1=RUNNING, 2=OUT OF FUNDS, 3=PAUSED, 4=BLOCKED,
 * 6=REJECTED, 7=OUT OF DAILY FUNDS, 8=SCHEDULED PAUSE,
 * 9=SYSTEM BLOCKED, 10=HELD, 11=SYSTEM FREEZED
 *
 * Seuls 1 (RUNNING) et 3 (PAUSED) peuvent être définis via l'API
 */
function updatePopcashCampaignStatus(int $campaignId, string $status): array
{
    // Mapping des statuts string vers les valeurs numériques de l'API
    $statusMap = [
        'active' => 1,   // RUNNING
        'paused' => 3,   // PAUSED
        'stopped' => 3   // Pas de stopped dans l'API, utiliser PAUSED
    ];

    if (!isset($statusMap[$status])) {
        return ['success' => false, 'error' => 'Statut invalide. Utilisez: active ou paused'];
    }

    $result = popcashRequest('PUT', "/campaigns/{$campaignId}", [
        'status' => $statusMap[$status]
    ]);

    return $result;
}

/**
 * Récupère les détails d'une campagne PopCash
 */
function getPopcashCampaign(int $campaignId): ?array
{
    $result = popcashRequest('GET', "/campaigns/{$campaignId}");
    return $result['success'] ? $result['data'] : null;
}

/**
 * Synchronise tous les liens vers tous les rotators actifs
 */
function syncAllLinksToAllSites(): array
{
    $links = getAllLinks();
    $sites = getActiveSites();

    if (empty($links)) {
        return ['success' => false, 'message' => 'Aucun lien à synchroniser'];
    }

    if (empty($sites)) {
        return ['success' => false, 'message' => 'Aucun rotator actif'];
    }

    $successCount = 0;
    $errorCount = 0;
    $results = [];

    foreach ($sites as $site) {
        $result = syncLinksToSite($site, $links);

        if ($result['success']) {
            $successCount++;
            $results[] = ['site' => $site['name'], 'success' => true, 'urls' => count($links)];
        } else {
            $errorCount++;
            $results[] = ['site' => $site['name'], 'success' => false, 'error' => $result['error'] ?? 'Erreur inconnue'];
        }
    }

    return [
        'success' => $errorCount === 0,
        'message' => "$successCount/" . count($sites) . " rotators synchronisés (" . count($links) . " liens)",
        'details' => $results
    ];
}
