<?php
/**
 * Connexion SQLite et initialisation des tables
 *
 * Ce fichier gère la connexion à la base de données SQLite
 * et crée les tables si elles n'existent pas.
 */

require_once __DIR__ . '/../config.php';

/**
 * Obtient une connexion PDO à la base SQLite
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        // Créer le dossier data si nécessaire
        if (!is_dir(DATA_DIR)) {
            mkdir(DATA_DIR, 0755, true);
        }

        $pdo = new PDO('sqlite:' . DB_FILE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        // Activer les clés étrangères
        $pdo->exec('PRAGMA foreign_keys = ON');

        // Initialiser les tables si nécessaire
        initTables($pdo);
    }

    return $pdo;
}

/**
 * Crée les tables si elles n'existent pas
 */
function initTables(PDO $pdo): void
{
    // Table des utilisateurs
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT NOT NULL UNIQUE,
            password TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');

    // Table des liens blanchis
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS links (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            original_url TEXT NOT NULL,
            whitened_url TEXT NOT NULL,
            source TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');

    // Table des sites/rotators
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS sites (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            base_url TEXT NOT NULL,
            api_token TEXT NOT NULL,
            is_active INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');

    // Table de liaison liens <-> sites
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS link_site (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            link_id INTEGER NOT NULL,
            site_id INTEGER NOT NULL,
            is_synced INTEGER DEFAULT 0,
            synced_at DATETIME,
            FOREIGN KEY (link_id) REFERENCES links(id) ON DELETE CASCADE,
            FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE,
            UNIQUE(link_id, site_id)
        )
    ');

    // Table des logs de synchronisation
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS sync_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            site_id INTEGER NOT NULL,
            status TEXT NOT NULL,
            message TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (site_id) REFERENCES sites(id) ON DELETE CASCADE
        )
    ');

    // Table des paramètres (clés API, etc.)
    $pdo->exec('
        CREATE TABLE IF NOT EXISTS settings (
            key TEXT PRIMARY KEY,
            value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ');

    // Créer l'admin par défaut si aucun utilisateur n'existe
    $stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
    $result = $stmt->fetch();

    if ($result['count'] === 0) {
        $hash = password_hash(ADMIN_PASSWORD, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (username, password) VALUES (?, ?)');
        $stmt->execute([ADMIN_USERNAME, $hash]);
    }
}

/**
 * Helpers pour les opérations courantes
 */

function getAllLinks(): array
{
    $pdo = getDB();
    $stmt = $pdo->query('SELECT * FROM links ORDER BY created_at DESC');
    return $stmt->fetchAll();
}

function getLinksCount(): int
{
    $pdo = getDB();
    return (int) $pdo->query('SELECT COUNT(*) FROM links')->fetchColumn();
}

function getLinksPaginated(int $page = 1, int $perPage = 10): array
{
    $pdo = getDB();
    $offset = ($page - 1) * $perPage;
    $stmt = $pdo->prepare('SELECT * FROM links ORDER BY created_at DESC LIMIT ? OFFSET ?');
    $stmt->execute([$perPage, $offset]);
    return $stmt->fetchAll();
}

function getLinkById(int $id): ?array
{
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM links WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getAllSites(): array
{
    $pdo = getDB();
    $stmt = $pdo->query('SELECT * FROM sites ORDER BY name ASC');
    return $stmt->fetchAll();
}

function getActiveSites(): array
{
    $pdo = getDB();
    $stmt = $pdo->query('SELECT * FROM sites WHERE is_active = 1 ORDER BY name ASC');
    return $stmt->fetchAll();
}

function getSiteById(int $id): ?array
{
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT * FROM sites WHERE id = ?');
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

function getLinkSites(int $linkId): array
{
    $pdo = getDB();
    $stmt = $pdo->prepare('
        SELECT s.*, ls.is_synced, ls.synced_at
        FROM sites s
        LEFT JOIN link_site ls ON s.id = ls.site_id AND ls.link_id = ?
        ORDER BY s.name
    ');
    $stmt->execute([$linkId]);
    return $stmt->fetchAll();
}

function getSiteLinks(int $siteId): array
{
    $pdo = getDB();
    $stmt = $pdo->prepare('
        SELECT l.*, ls.is_synced, ls.synced_at
        FROM links l
        INNER JOIN link_site ls ON l.id = ls.link_id
        WHERE ls.site_id = ?
        ORDER BY l.created_at DESC
    ');
    $stmt->execute([$siteId]);
    return $stmt->fetchAll();
}

function getRecentSyncLogs(int $limit = 20): array
{
    $pdo = getDB();
    $stmt = $pdo->prepare('
        SELECT sl.*, s.name as site_name
        FROM sync_logs sl
        INNER JOIN sites s ON sl.site_id = s.id
        ORDER BY sl.created_at DESC
        LIMIT ?
    ');
    $stmt->execute([$limit]);
    return $stmt->fetchAll();
}

function getStats(): array
{
    $pdo = getDB();

    $linksCount = $pdo->query('SELECT COUNT(*) FROM links')->fetchColumn();
    $sitesCount = $pdo->query('SELECT COUNT(*) FROM sites WHERE is_active = 1')->fetchColumn();
    $syncedCount = $pdo->query('SELECT COUNT(*) FROM link_site WHERE is_synced = 1')->fetchColumn();
    $lastSync = $pdo->query('SELECT MAX(synced_at) FROM link_site WHERE is_synced = 1')->fetchColumn();

    return [
        'links_count' => (int) $linksCount,
        'sites_count' => (int) $sitesCount,
        'synced_count' => (int) $syncedCount,
        'last_sync' => $lastSync
    ];
}

/**
 * Récupère un paramètre depuis la base de données
 */
function getSetting(string $key, ?string $default = null): ?string
{
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE key = ?');
    $stmt->execute([$key]);
    $result = $stmt->fetchColumn();
    return $result !== false ? $result : $default;
}

/**
 * Enregistre un paramètre dans la base de données
 */
function setSetting(string $key, ?string $value): void
{
    $pdo = getDB();
    $stmt = $pdo->prepare('
        INSERT INTO settings (key, value, updated_at) VALUES (?, ?, CURRENT_TIMESTAMP)
        ON CONFLICT(key) DO UPDATE SET value = excluded.value, updated_at = CURRENT_TIMESTAMP
    ');
    $stmt->execute([$key, $value]);
}

/**
 * Récupère la clé API PopCash (DB prioritaire, puis env, puis vide)
 */
function getPopcashApiKey(): string
{
    $dbKey = getSetting('popcash_api_key');
    if (!empty($dbKey)) {
        return $dbKey;
    }
    return defined('POPCASH_API_KEY') ? POPCASH_API_KEY : '';
}

/**
 * Récupère les IDs de campagnes PopCash sauvegardées
 */
function getSavedCampaignIds(): array
{
    $json = getSetting('popcash_campaign_ids', '[]');
    $ids = json_decode($json, true);
    return is_array($ids) ? $ids : [];
}

/**
 * Ajoute un ID de campagne PopCash
 */
function addCampaignId(int $id): void
{
    $ids = getSavedCampaignIds();
    if (!in_array($id, $ids)) {
        $ids[] = $id;
        setSetting('popcash_campaign_ids', json_encode($ids));
    }
}

/**
 * Supprime un ID de campagne PopCash
 */
function removeCampaignId(int $id): void
{
    $ids = getSavedCampaignIds();
    $ids = array_values(array_filter($ids, fn($i) => $i !== $id));
    setSetting('popcash_campaign_ids', json_encode($ids));
}
