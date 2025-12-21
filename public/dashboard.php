<?php
/**
 * Dashboard Unifié - Miraubolant
 *
 * Page unique regroupant:
 * - Stats des rotators (clics, pays, devices)
 * - Widget PopCash (compte, campagnes)
 * - Gestion des liens (CRUD via modales)
 * - Gestion des rotators (CRUD via modales)
 * - Synchronisation manuelle
 *
 * Code refactorisé en composants modulaires
 */

// Includes
require_once __DIR__ . '/../includes/layout.php';
require_once __DIR__ . '/../includes/db.php';

// Handlers
require_once __DIR__ . '/../handlers/LinkHandler.php';
require_once __DIR__ . '/../handlers/RotatorHandler.php';
require_once __DIR__ . '/../handlers/SyncHandler.php';
require_once __DIR__ . '/../handlers/PopcashHandler.php';

// Services
require_once __DIR__ . '/../services/StatsService.php';

// Components
require_once __DIR__ . '/../components/stats/StatsCards.php';
require_once __DIR__ . '/../components/stats/DetailedStats.php';
require_once __DIR__ . '/../components/stats/WorldMap.php';
require_once __DIR__ . '/../components/stats/LiveLogs.php';
require_once __DIR__ . '/../components/links/LinksSection.php';
require_once __DIR__ . '/../components/links/LinkModal.php';
require_once __DIR__ . '/../components/rotators/RotatorsSection.php';
require_once __DIR__ . '/../components/rotators/RotatorModal.php';
require_once __DIR__ . '/../components/popcash/PopcashWidget.php';
require_once __DIR__ . '/../components/popcash/PopcashModal.php';

requireAuth();

// ============================================================
// Gestion des actions POST
// ============================================================

function handlePostActions(): ?string
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return null;
    }

    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        return 'Token CSRF invalide.';
    }

    $action = $_POST['action'] ?? '';
    $pdo = getDB();

    switch ($action) {
        case 'add_link':
            return handleAddLink($pdo);
        case 'edit_link':
            return handleEditLink($pdo);
        case 'delete_link':
            return handleDeleteLink($pdo);
        case 'add_rotator':
            return handleAddRotator($pdo);
        case 'edit_rotator':
            return handleEditRotator($pdo);
        case 'delete_rotator':
            return handleDeleteRotator($pdo);
        case 'sync_all':
            return handleSyncAll();
        case 'campaign_status':
            return handleCampaignStatus();
        case 'save_popcash_key':
            return handleSavePopcashKey();
        case 'add_campaign':
            return handleAddCampaign();
        case 'remove_campaign':
            return handleRemoveCampaign();
        default:
            return null;
    }
}

// ============================================================
// Récupération des données
// ============================================================

$error = handlePostActions();
$period = $_GET['period'] ?? '24h';
$linksPage = max(1, (int) ($_GET['links_page'] ?? 1));
$data = fetchDashboardData($linksPage);
$rotatorStats = fetchRotatorStats($data['activeSites'], $period);
$popcashData = fetchPopcashData();

// ============================================================
// Rendu HTML
// ============================================================

renderHeader('Dashboard', 'dashboard');
?>

<!-- CSS du dashboard -->
<link rel="stylesheet" href="assets/css/dashboard.css">

<!-- Header avec actions -->
<div class="flex items-center justify-between mb-4">
    <div class="flex items-center gap-3">
        <h1 class="text-xl font-semibold text-white">Dashboard</h1>
        <span id="statsLastUpdate" class="text-text-secondary text-xs hidden sm:block"></span>
    </div>
    <div class="flex items-center gap-2">
        <select id="periodSelect" onchange="changePeriod(this.value)" class="input text-xs py-1.5 w-auto">
            <option value="1h" <?= $period === '1h' ? 'selected' : '' ?>>1h</option>
            <option value="6h" <?= $period === '6h' ? 'selected' : '' ?>>6h</option>
            <option value="24h" <?= $period === '24h' ? 'selected' : '' ?>>24h</option>
            <option value="48h" <?= $period === '48h' ? 'selected' : '' ?>>48h</option>
            <option value="7d" <?= $period === '7d' ? 'selected' : '' ?>>7j</option>
            <option value="30d" <?= $period === '30d' ? 'selected' : '' ?>>30j</option>
            <option value="90d" <?= $period === '90d' ? 'selected' : '' ?>>90j</option>
            <option value="1y" <?= $period === '1y' ? 'selected' : '' ?>>1 an</option>
            <option value="all" <?= $period === 'all' ? 'selected' : '' ?>>Tout</option>
        </select>
        <button type="button" id="refreshStatsBtn" onclick="manualRefreshStats()" class="btn btn-secondary py-1.5 px-2" title="Rafraîchir">
            <svg id="refreshIcon" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
        </button>
        <?php if (!empty($data['links']) && !empty($data['activeSites'])): ?>
        <form method="POST" class="inline">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="sync_all">
            <button type="submit" class="btn btn-primary py-1.5 px-3 text-xs" onclick="return confirm('Synchroniser tous les liens vers tous les rotators ?')">
                Sync
            </button>
        </form>
        <?php endif; ?>
        <button type="button" onclick="openCampaignModal()" class="btn btn-secondary py-1.5 px-3 text-xs" title="<?= $popcashData['configured'] ? 'Gérer les campagnes PopCash' : 'Configurer PopCash' ?>">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            PopCash
        </button>
    </div>
</div>

<?php if ($error): ?>
<div class="mb-4 p-3 bg-danger/10 border border-danger/30 rounded text-danger text-sm"><?= e($error) ?></div>
<?php endif; ?>

<!-- Stats Cards -->
<?php renderStatsCards($rotatorStats, $data); ?>

<!-- PopCash Widget -->
<?php renderPopcashWidget($popcashData); ?>

<!-- Carte du monde + Logs en direct -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
    <?php renderWorldMap(); ?>
    <?php renderLiveLogs(); ?>
</div>

<!-- Stats détaillées -->
<?php renderDetailedStats($rotatorStats, $data, $period); ?>

<!-- Liens -->
<?php renderLinksSection($data, $period); ?>

<!-- Rotators -->
<?php renderRotatorsSection($data['sites']); ?>

<!-- Modales -->
<?php renderLinkModal($data['sources']); ?>
<?php renderRotatorModal(); ?>
<?php renderPopcashModal($popcashData); ?>

<!-- Scripts externes -->
<script src="https://unpkg.com/jsvectormap@1.5.3/dist/js/jsvectormap.min.js"></script>
<script src="https://unpkg.com/jsvectormap@1.5.3/dist/maps/world.js"></script>
<link rel="stylesheet" href="https://unpkg.com/jsvectormap@1.5.3/dist/css/jsvectormap.min.css">

<!-- Variables globales pour les scripts -->
<script>
window.linksData = <?= json_encode(array_map(function($l) {
    return ['name' => $l['name'], 'whitened_url' => $l['whitened_url'], 'original_url' => $l['original_url'], 'source' => $l['source']];
}, getAllLinks())) ?>;
window.sourceLabels = <?= json_encode($data['sources']) ?>;
window.currentPeriod = '<?= e($period) ?>';
window.countryData = <?= json_encode($rotatorStats['countries']) ?>;
</script>

<!-- Scripts du dashboard -->
<script src="assets/js/dashboard.js"></script>
<script src="assets/js/modals.js"></script>
<script src="assets/js/stats.js"></script>
<script src="assets/js/logs.js"></script>
<script src="assets/js/map.js"></script>

<?php renderFooter(); ?>
