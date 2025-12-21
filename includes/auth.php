<?php
/**
 * Fonctions d'authentification et gestion des sessions
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/db.php';

/**
 * Démarre la session de manière sécurisée
 */
function startSecureSession(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);

        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        session_start();
    }
}

/**
 * Vérifie si l'utilisateur est connecté
 */
function isLoggedIn(): bool
{
    startSecureSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

/**
 * Vérifie l'authentification et redirige si non connecté
 */
function requireAuth(): void
{
    if (!isLoggedIn()) {
        header('Location: index.php');
        exit;
    }
}

/**
 * Tente de connecter un utilisateur
 */
function login(string $username, string $password): bool
{
    $pdo = getDB();
    $stmt = $pdo->prepare('SELECT id, password FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        return false;
    }

    if (!password_verify($password, $user['password'])) {
        return false;
    }

    startSecureSession();

    // Régénérer l'ID de session pour prévenir la fixation de session
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $username;
    $_SESSION['logged_in'] = true;
    $_SESSION['login_time'] = time();

    return true;
}

/**
 * Déconnecte l'utilisateur
 */
function logout(): void
{
    startSecureSession();

    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
}

/**
 * Récupère le nom d'utilisateur connecté
 */
function getCurrentUsername(): ?string
{
    startSecureSession();
    return $_SESSION['username'] ?? null;
}

/**
 * Récupère l'ID de l'utilisateur connecté
 */
function getCurrentUserId(): ?int
{
    startSecureSession();
    return $_SESSION['user_id'] ?? null;
}

/**
 * Génère un token CSRF
 */
function generateCsrfToken(): string
{
    startSecureSession();

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Vérifie un token CSRF
 */
function verifyCsrfToken(string $token): bool
{
    startSecureSession();
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Affiche un champ hidden avec le token CSRF
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}
