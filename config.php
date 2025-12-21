<?php
/**
 * Configuration du Dashboard VintDress Link Manager
 *
 * Ce fichier contient les paramètres de configuration du dashboard.
 * Les variables d'environnement ont la priorité sur les valeurs par défaut.
 */

// Credentials admin (utilisés pour créer le compte initial)
define('ADMIN_USERNAME', getenv('ADMIN_USERNAME') ?: 'admin');
define('ADMIN_PASSWORD', getenv('ADMIN_PASSWORD') ?: 'changeme123');

// Clé secrète pour les sessions
define('APP_SECRET', getenv('APP_SECRET') ?: 'change_this_secret_key_in_production');

// Chemins
define('DATA_DIR', __DIR__ . '/data');
define('DB_FILE', DATA_DIR . '/database.sqlite');

// Configuration de session
define('SESSION_NAME', 'vintdress_session');
define('SESSION_LIFETIME', 86400); // 24 heures

// Timezone
date_default_timezone_set('Europe/Paris');

// Mode debug (désactiver en production)
define('DEBUG_MODE', getenv('DEBUG_MODE') === 'true');

// Version de l'application
define('APP_VERSION', '2.0.0');
define('APP_NAME', 'Miraubolant Dashboard');

// PopCash API
define('POPCASH_API_KEY', getenv('POPCASH_API_KEY') ?: '');
define('POPCASH_API_URL', 'https://api.popcash.net');
