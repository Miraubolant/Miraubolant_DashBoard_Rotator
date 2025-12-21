<?php
/**
 * Script d'initialisation de la base de données
 *
 * Ce script peut être exécuté en CLI pour initialiser ou réinitialiser la base.
 * Usage: php init-db.php [--reset]
 */

require_once __DIR__ . '/config.php';

echo "=== VintDress Link Manager - Initialisation DB ===\n\n";

// Vérifier si on demande une réinitialisation
$reset = in_array('--reset', $argv ?? []);

if ($reset && file_exists(DB_FILE)) {
    echo "Suppression de la base existante...\n";
    unlink(DB_FILE);
}

// Créer le dossier data si nécessaire
if (!is_dir(DATA_DIR)) {
    echo "Création du dossier data...\n";
    mkdir(DATA_DIR, 0755, true);
}

echo "Connexion à la base de données...\n";

// La connexion via getDB() créera automatiquement les tables
require_once __DIR__ . '/includes/db.php';
$pdo = getDB();

echo "Base de données initialisée avec succès !\n\n";

// Afficher les infos
$stmt = $pdo->query('SELECT COUNT(*) as count FROM users');
$users = $stmt->fetch();

$stmt = $pdo->query('SELECT COUNT(*) as count FROM links');
$links = $stmt->fetch();

$stmt = $pdo->query('SELECT COUNT(*) as count FROM sites');
$sites = $stmt->fetch();

echo "État de la base :\n";
echo "- Utilisateurs : {$users['count']}\n";
echo "- Liens : {$links['count']}\n";
echo "- Sites : {$sites['count']}\n";
echo "\n";

if ($users['count'] > 0) {
    echo "Compte admin créé :\n";
    echo "- Username : " . ADMIN_USERNAME . "\n";
    echo "- Password : [défini dans config.php ou variables d'environnement]\n";
}

echo "\nTerminé.\n";
