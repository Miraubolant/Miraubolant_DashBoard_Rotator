<?php
/**
 * API endpoint pour résoudre l'URL finale d'un lien raccourci
 * Suit les redirections et retourne l'URL de destination
 */

require_once __DIR__ . '/../includes/auth.php';

startSecureSession();

if (!isLoggedIn()) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non authentifié']);
    exit;
}

header('Content-Type: application/json');
header('Cache-Control: no-cache');

$url = $_GET['url'] ?? '';

if (empty($url)) {
    echo json_encode(['success' => false, 'error' => 'URL manquante']);
    exit;
}

// Valider l'URL
if (!filter_var($url, FILTER_VALIDATE_URL)) {
    echo json_encode(['success' => false, 'error' => 'URL invalide']);
    exit;
}

// Suivre les redirections pour obtenir l'URL finale
function resolveUrl(string $url, int $maxRedirects = 10): ?string
{
    $currentUrl = $url;
    $redirectCount = 0;

    while ($redirectCount < $maxRedirects) {
        $ch = curl_init($currentUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            CURLOPT_SSL_VERIFYPEER => false,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        // Si pas de redirection (2xx ou 4xx/5xx), on a l'URL finale
        if ($httpCode < 300 || $httpCode >= 400) {
            return $effectiveUrl ?: $currentUrl;
        }

        // Chercher le header Location pour la redirection
        if (preg_match('/^Location:\s*(.+)$/mi', $response, $matches)) {
            $location = trim($matches[1]);

            // Gérer les URLs relatives
            if (strpos($location, 'http') !== 0) {
                $parsed = parse_url($currentUrl);
                $base = $parsed['scheme'] . '://' . $parsed['host'];
                if (strpos($location, '/') === 0) {
                    $location = $base . $location;
                } else {
                    $location = $base . '/' . $location;
                }
            }

            $currentUrl = $location;
            $redirectCount++;
        } else {
            // Pas de header Location, on retourne l'URL actuelle
            return $currentUrl;
        }
    }

    return $currentUrl;
}

try {
    $originalUrl = resolveUrl($url);

    if ($originalUrl && $originalUrl !== $url) {
        echo json_encode([
            'success' => true,
            'original_url' => $originalUrl,
            'whitened_url' => $url
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Impossible de résoudre l\'URL'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erreur lors de la résolution'
    ]);
}
