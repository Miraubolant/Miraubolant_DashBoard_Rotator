<?php
/**
 * Composants de layout réutilisables
 *
 * Design moderne avec header
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/functions.php';

/**
 * Affiche le header HTML avec navigation
 */
function renderHeader(string $title, string $activePage = ''): void
{
    $username = getCurrentUsername();
    $flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title) ?> - <?= APP_NAME ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%3E%3Crect width='100' height='100' rx='20' fill='%23ffffff'/%3E%3Cpath d='M65 35a10 10 0 0 1 0 14.14l-7.07 7.07a10 10 0 0 1-14.14 0 5 5 0 0 0-7.07 7.07 20 20 0 0 0 28.28 0l7.07-7.07a20 20 0 0 0-28.28-28.28l-3.54 3.54a5 5 0 0 0 7.07 7.07l3.54-3.54A10 10 0 0 1 65 35zM35 65a10 10 0 0 1 0-14.14l7.07-7.07a10 10 0 0 1 14.14 0 5 5 0 0 0 7.07-7.07 20 20 0 0 0-28.28 0l-7.07 7.07a20 20 0 0 0 28.28 28.28l3.54-3.54a5 5 0 0 0-7.07-7.07l-3.54 3.54A10 10 0 0 1 35 65z' fill='%23000000'/%3E%3C/svg%3E">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bg-primary': '#0a0a0a',
                        'bg-secondary': '#141414',
                        'bg-tertiary': '#1f1f1f',
                        'border': '#2a2a2a',
                        'text-primary': '#ffffff',
                        'text-secondary': '#888888',
                        'accent': '#ffffff',
                        'accent-hover': '#e0e0e0',
                        'success': '#22c55e',
                        'warning': '#eab308',
                        'danger': '#ef4444'
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0a0a0a;
            color: #ffffff;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .header {
            background-color: #0a0a0a;
            border-bottom: 1px solid #1f1f1f;
            position: sticky;
            top: 0;
            z-index: 30;
            backdrop-filter: blur(12px);
            background-color: rgba(10, 10, 10, 0.9);
        }
        .card {
            background-color: #111111;
            border: 1px solid #1f1f1f;
            border-radius: 12px;
            transition: all 0.2s ease;
        }
        .card:hover {
            border-color: #2a2a2a;
        }
        .stat-card {
            background-color: #111111;
            border: 1px solid #1f1f1f;
            border-radius: 12px;
            padding: 1.25rem;
            position: relative;
            overflow: hidden;
        }
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background-color: #ffffff;
            opacity: 0;
            transition: opacity 0.3s;
        }
        .stat-card:hover::before {
            opacity: 1;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .btn-primary {
            background-color: #ffffff;
            color: #000000;
            border: none;
        }
        .btn-primary:hover {
            background-color: #e0e0e0;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background-color: #1a1a1a;
            color: #ffffff;
            border: 1px solid #2a2a2a;
        }
        .btn-secondary:hover {
            background-color: #2a2a2a;
        }
        .btn-danger {
            background-color: transparent;
            color: #ef4444;
            border: 1px solid #ef4444;
        }
        .btn-danger:hover {
            background-color: #ef4444;
            color: #ffffff;
        }
        .btn-success {
            background-color: #22c55e;
            color: #ffffff;
            border: none;
        }
        .btn-success:hover {
            background-color: #16a34a;
        }
        .btn-warning {
            background-color: #eab308;
            color: #000000;
            border: none;
        }
        .btn-warning:hover {
            background-color: #ca8a04;
        }
        .input {
            background-color: #0a0a0a;
            border: 1px solid #2a2a2a;
            border-radius: 8px;
            padding: 0.625rem 0.875rem;
            color: #ffffff;
            font-size: 0.875rem;
            width: 100%;
            transition: all 0.2s ease;
        }
        .input:focus {
            outline: none;
            border-color: #ffffff;
            box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.1);
        }
        .input::placeholder {
            color: #555555;
        }
        select.input {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23888888' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        .table-row {
            transition: background-color 0.15s;
        }
        .table-row:hover {
            background-color: #1a1a1a;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 6px;
            letter-spacing: 0.025em;
        }
        .badge-success {
            background-color: rgba(34, 197, 94, 0.15);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }
        .badge-warning {
            background-color: rgba(234, 179, 8, 0.15);
            color: #facc15;
            border: 1px solid rgba(234, 179, 8, 0.3);
        }
        .badge-error, .badge-danger {
            background-color: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .badge-neutral {
            background-color: rgba(136, 136, 136, 0.15);
            color: #888888;
            border: 1px solid rgba(136, 136, 136, 0.3);
        }
        .badge-accent {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .toast {
            position: fixed;
            top: 5rem;
            right: 1.5rem;
            z-index: 50;
            padding: 1rem 1.25rem;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            animation: slideIn 0.3s ease-out;
            backdrop-filter: blur(8px);
        }
        .toast-success {
            background-color: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.5);
            color: #4ade80;
        }
        .toast-error {
            background-color: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #f87171;
        }
        .toast-warning {
            background-color: rgba(234, 179, 8, 0.15);
            border: 1px solid rgba(234, 179, 8, 0.5);
            color: #facc15;
        }
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(100%);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        .progress-bar {
            height: 6px;
            background-color: #1f1f1f;
            border-radius: 3px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background-color: #ffffff;
            border-radius: 3px;
            transition: width 0.5s ease;
        }
        .icon-box {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-box-purple { background-color: rgba(255, 255, 255, 0.1); }
        .icon-box-green { background-color: rgba(34, 197, 94, 0.15); }
        .icon-box-yellow { background-color: rgba(234, 179, 8, 0.15); }
        .icon-box-red { background-color: rgba(239, 68, 68, 0.15); }
        .icon-box-blue { background-color: rgba(59, 130, 246, 0.15); }
        .icon-box-cyan { background-color: rgba(6, 182, 212, 0.15); }
        .flag-icon {
            font-size: 1.25rem;
            line-height: 1;
        }
    </style>
</head>
<body class="min-h-screen">
    <!-- Header -->
    <header class="header">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <!-- Logo + Nav -->
                <div class="flex items-center gap-6">
                    <a href="dashboard.php" class="flex items-center">
                        <div class="w-9 h-9 bg-white rounded-lg flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-black" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </a>
                    <nav class="flex items-center gap-1">
                        <a href="dashboard.php" class="px-3 py-1.5 rounded-lg text-sm transition-colors <?= $activePage === 'dashboard' ? 'bg-white/10 text-white' : 'text-text-secondary hover:text-white hover:bg-white/5' ?>">
                            <span class="flex items-center gap-2">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" /></svg>
                                <span class="hidden sm:inline">Dashboard</span>
                            </span>
                        </a>
                    </nav>
                </div>

                <!-- User menu -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white text-sm font-medium">
                            <?= strtoupper(substr($username ?? 'U', 0, 1)) ?>
                        </div>
                        <span class="text-sm text-text-secondary hidden sm:block"><?= e($username ?? '') ?></span>
                    </div>
                    <a href="logout.php" class="flex items-center gap-2 text-text-secondary hover:text-white transition-colors text-sm" title="Déconnexion">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="hidden sm:block">Déconnexion</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if ($flash): ?>
            <div class="toast toast-<?= $flash['type'] === 'success' ? 'success' : ($flash['type'] === 'warning' ? 'warning' : 'error') ?>" id="toast">
                <?= e($flash['message']) ?>
            </div>
            <script>
                setTimeout(() => {
                    document.getElementById('toast').style.opacity = '0';
                    document.getElementById('toast').style.transform = 'translateX(100%)';
                    setTimeout(() => document.getElementById('toast').remove(), 300);
                }, 4000);
            </script>
        <?php endif; ?>
<?php
}

/**
 * Affiche le footer HTML
 */
function renderFooter(): void
{
?>
    </main>

    <!-- Footer -->
    <footer class="border-t border-border mt-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <p class="text-center text-text-secondary text-xs">
                <?= APP_NAME ?> v<?= APP_VERSION ?>
            </p>
        </div>
    </footer>

    <script>
        // Confirmation avant suppression
        document.querySelectorAll('[data-confirm]').forEach(el => {
            el.addEventListener('click', function(e) {
                if (!confirm(this.dataset.confirm)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
<?php
}

/**
 * Convertit un code pays en emoji drapeau
 */
function countryCodeToFlag(string $code): string
{
    $code = strtoupper($code);
    if (strlen($code) !== 2) {
        return '';
    }
    $flagOffset = 0x1F1E6;
    $asciiOffset = ord('A');
    $firstChar = mb_chr($flagOffset + (ord($code[0]) - $asciiOffset));
    $secondChar = mb_chr($flagOffset + (ord($code[1]) - $asciiOffset));
    return $firstChar . $secondChar;
}

/**
 * Noms des pays en français
 */
function getCountryName(string $code): string
{
    $countries = [
        'FR' => 'France',
        'US' => 'États-Unis',
        'GB' => 'Royaume-Uni',
        'DE' => 'Allemagne',
        'ES' => 'Espagne',
        'IT' => 'Italie',
        'BE' => 'Belgique',
        'CH' => 'Suisse',
        'CA' => 'Canada',
        'MA' => 'Maroc',
        'DZ' => 'Algérie',
        'TN' => 'Tunisie',
        'SN' => 'Sénégal',
        'CI' => 'Côte d\'Ivoire',
        'PT' => 'Portugal',
        'NL' => 'Pays-Bas',
        'PL' => 'Pologne',
        'BR' => 'Brésil',
        'MX' => 'Mexique',
        'AR' => 'Argentine',
        'JP' => 'Japon',
        'CN' => 'Chine',
        'IN' => 'Inde',
        'AU' => 'Australie',
        'RU' => 'Russie',
        'XX' => 'Inconnu'
    ];
    return $countries[strtoupper($code)] ?? $code;
}
