<?php
/**
 * Page de connexion
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Si déjà connecté, rediriger vers le dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Traitement du formulaire de connexion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (login($username, $password)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Identifiants incorrects.';
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?= APP_NAME ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'bg-primary': '#0d1117',
                        'bg-secondary': '#161b22',
                        'border': '#30363d',
                        'text-primary': '#c9d1d9',
                        'text-secondary': '#8b949e',
                        'accent': '#58a6ff',
                        'danger': '#f85149'
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #0d1117;
            color: #c9d1d9;
        }
        .input {
            background-color: #0d1117;
            border: 1px solid #30363d;
            border-radius: 6px;
            padding: 0.625rem 0.75rem;
            color: #c9d1d9;
            font-size: 0.875rem;
            width: 100%;
        }
        .input:focus {
            outline: none;
            border-color: #58a6ff;
            box-shadow: 0 0 0 3px rgba(88, 166, 255, 0.3);
        }
        .input::placeholder {
            color: #6e7681;
        }
        .btn-primary {
            background-color: #238636;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 500;
            width: 100%;
            cursor: pointer;
            transition: background-color 0.15s;
        }
        .btn-primary:hover {
            background-color: #2ea043;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-accent rounded-xl mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-white" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
                </svg>
            </div>
            <h1 class="text-xl font-semibold text-white"><?= APP_NAME ?></h1>
            <p class="text-text-secondary text-sm mt-1">Connectez-vous pour continuer</p>
        </div>

        <div class="bg-bg-secondary border border-border rounded-lg p-6">
            <?php if ($error): ?>
                <div class="mb-4 p-3 bg-danger/10 border border-danger/30 rounded text-danger text-sm">
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium mb-2">Nom d'utilisateur</label>
                    <input type="text" id="username" name="username" class="input"
                           placeholder="admin" autocomplete="username" required
                           value="<?= e($_POST['username'] ?? '') ?>">
                </div>

                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium mb-2">Mot de passe</label>
                    <input type="password" id="password" name="password" class="input"
                           placeholder="••••••••" autocomplete="current-password" required>
                </div>

                <button type="submit" class="btn-primary">
                    Se connecter
                </button>
            </form>
        </div>

        <p class="text-center text-text-secondary text-xs mt-6">
            <?= APP_NAME ?> v<?= APP_VERSION ?>
        </p>
    </div>
</body>
</html>
