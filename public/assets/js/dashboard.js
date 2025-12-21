/**
 * Dashboard JS - Logique principale et helpers
 */

// Variables globales initialisées par PHP dans le template
// window.linksData, window.sourceLabels, window.currentPeriod, window.countryData

/**
 * Recherche le nom d'un lien à partir de son URL
 */
function getLinkNameByUrl(url) {
    if (!url || !window.linksData) return null;
    for (const link of window.linksData) {
        if (url.includes(link.whitened_url) || link.whitened_url.includes(url) ||
            url.includes(link.original_url) || link.original_url.includes(url)) {
            return link.name;
        }
    }
    return null;
}

/**
 * Génère un nom de lien automatiquement selon la source
 */
function generateLinkName(sourceKey) {
    if (!sourceKey || !window.sourceLabels || !window.sourceLabels[sourceKey]) return '';

    const sourceLabel = window.sourceLabels[sourceKey];
    const baseName = `Lien ${sourceLabel}`;

    // Compter les liens existants avec ce pattern
    let maxNum = 0;
    const pattern = new RegExp(`^Lien ${sourceLabel.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')}\\s*(\\d+)?$`, 'i');

    for (const link of window.linksData) {
        if (link.source === sourceKey) {
            const match = link.name.match(pattern);
            if (match) {
                const num = match[1] ? parseInt(match[1]) : 1;
                if (num > maxNum) maxNum = num;
            }
        }
    }

    return `${baseName} ${maxNum + 1}`;
}

/**
 * Résolution de l'URL d'origine depuis l'URL blanchie
 */
let resolveTimeout = null;
let isResolving = false;

async function resolveOriginalUrl(whitenedUrl) {
    if (!whitenedUrl || isResolving) return;

    // Vérifier que c'est une URL valide
    try {
        new URL(whitenedUrl);
    } catch {
        return;
    }

    const originalUrlInput = document.getElementById('linkOriginalUrl');
    if (originalUrlInput.value) return; // Ne pas écraser si déjà rempli

    isResolving = true;
    originalUrlInput.placeholder = 'Résolution en cours...';

    try {
        const response = await fetch('api-resolve-url.php?url=' + encodeURIComponent(whitenedUrl), {
            credentials: 'same-origin'
        });
        const data = await response.json();

        if (data.success && data.original_url && !originalUrlInput.value) {
            originalUrlInput.value = data.original_url;
            originalUrlInput.classList.add('flash-update');
            setTimeout(() => originalUrlInput.classList.remove('flash-update'), 800);
        }
    } catch (error) {
        console.log('Résolution URL échouée:', error);
    } finally {
        isResolving = false;
        originalUrlInput.placeholder = 'https://votresite.com/page';
    }
}

/**
 * Filtrage des tableaux de stats
 */
function filterTable(containerId, query) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const rows = container.querySelectorAll('tbody tr[data-search]');
    const q = query.toLowerCase().trim();

    rows.forEach(row => {
        const searchText = row.getAttribute('data-search') || '';
        row.style.display = searchText.includes(q) ? '' : 'none';
    });
}

/**
 * Sections collapsables avec localStorage
 */
function toggleSection(sectionId) {
    const section = document.getElementById('section-' + sectionId);
    if (!section) return;

    section.classList.toggle('section-collapsed');

    // Sauvegarder l'état dans localStorage
    const collapsedSections = JSON.parse(localStorage.getItem('dashboard_collapsed') || '[]');
    const isCollapsed = section.classList.contains('section-collapsed');

    if (isCollapsed && !collapsedSections.includes(sectionId)) {
        collapsedSections.push(sectionId);
    } else if (!isCollapsed) {
        const index = collapsedSections.indexOf(sectionId);
        if (index > -1) collapsedSections.splice(index, 1);
    }

    localStorage.setItem('dashboard_collapsed', JSON.stringify(collapsedSections));
}

/**
 * Restaurer l'état des sections au chargement
 */
function restoreCollapsedSections() {
    const collapsedSections = JSON.parse(localStorage.getItem('dashboard_collapsed') || '[]');
    const protectedSections = ['links', 'rotators']; // Ces sections restent ouvertes par défaut

    collapsedSections.forEach(sectionId => {
        if (protectedSections.includes(sectionId)) return;
        const section = document.getElementById('section-' + sectionId);
        if (section) {
            section.classList.add('section-collapsed');
        }
    });
}

/**
 * Export des statistiques
 */
function exportStats(format) {
    const period = window.currentPeriod || '24h';
    const url = `api-export.php?format=${format}&period=${period}`;
    window.location.href = url;
}

/**
 * Convertir code pays en emoji flag
 */
function countryCodeToFlag(code) {
    if (!code || code.length !== 2) return '🌍';
    const codePoints = code.toUpperCase().split('').map(c => 127397 + c.charCodeAt(0));
    return String.fromCodePoint(...codePoints);
}

/**
 * Escape HTML helper
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

/**
 * Formater un nombre avec séparateurs
 */
function formatNumber(num) {
    return num.toLocaleString('fr-FR');
}

// Initialisation au chargement
document.addEventListener('DOMContentLoaded', function() {
    restoreCollapsedSections();
});
