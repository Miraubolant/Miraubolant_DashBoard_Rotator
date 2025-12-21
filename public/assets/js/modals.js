/**
 * Modals JS - Gestion des modales
 */

let isEditMode = false;

// ============================================================
// Modal Lien
// ============================================================

function openLinkModal(data = null) {
    isEditMode = !!data;

    document.getElementById('linkName').value = data?.name || '';
    document.getElementById('linkOriginalUrl').value = data?.original_url || '';
    document.getElementById('linkWhitenedUrl').value = data?.whitened_url || '';
    document.getElementById('linkSource').value = data?.source || '';
    document.getElementById('linkId').value = data?.id || '';
    document.getElementById('linkAction').value = data ? 'edit_link' : 'add_link';
    document.getElementById('linkModalTitle').textContent = data ? 'Modifier le lien' : 'Nouveau lien';
    document.getElementById('linkModal').classList.add('active');
    document.getElementById('linkModalBackdrop').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeLinkModal() {
    document.getElementById('linkModal').classList.remove('active');
    document.getElementById('linkModalBackdrop').classList.remove('active');
    document.body.style.overflow = '';
    isEditMode = false;

    // Réinitialiser la section d'ajout de source
    const addSourceSection = document.getElementById('addSourceSection');
    if (addSourceSection) {
        addSourceSection.classList.add('hidden');
        document.getElementById('newSourceLabel').value = '';
    }
}

function deleteLink(id, name) {
    if (confirm(`Supprimer le lien "${name}" ?`)) {
        document.getElementById('deleteLinkId').value = id;
        document.getElementById('deleteLinkForm').submit();
    }
}

// ============================================================
// Gestion des Sources
// ============================================================

function toggleAddSource() {
    const section = document.getElementById('addSourceSection');
    section.classList.toggle('hidden');
    if (!section.classList.contains('hidden')) {
        document.getElementById('newSourceLabel').focus();
    }
}

async function addNewSource() {
    const input = document.getElementById('newSourceLabel');
    const label = input.value.trim();

    if (!label) {
        input.focus();
        return;
    }

    try {
        const response = await fetch('api-sources.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ label })
        });

        const data = await response.json();

        if (!response.ok) {
            alert(data.error || 'Erreur lors de l\'ajout');
            return;
        }

        // Ajouter la nouvelle option au select
        const select = document.getElementById('linkSource');
        const option = document.createElement('option');
        option.value = data.slug;
        option.textContent = data.label;
        select.appendChild(option);

        // Sélectionner la nouvelle source
        select.value = data.slug;

        // Réinitialiser et masquer le formulaire
        input.value = '';
        document.getElementById('addSourceSection').classList.add('hidden');

        // Animation de confirmation
        select.classList.add('flash-update');
        setTimeout(() => select.classList.remove('flash-update'), 800);

    } catch (error) {
        console.error('Erreur:', error);
        alert('Erreur de connexion');
    }
}

// ============================================================
// Modal Rotator
// ============================================================

function openRotatorModal(data = null) {
    document.getElementById('rotatorName').value = data?.name || '';
    document.getElementById('rotatorBaseUrl').value = data?.base_url || '';
    document.getElementById('rotatorApiToken').value = data?.api_token || '';
    document.getElementById('rotatorId').value = data?.id || '';
    document.getElementById('rotatorAction').value = data ? 'edit_rotator' : 'add_rotator';
    document.getElementById('rotatorModalTitle').textContent = data ? 'Modifier le rotator' : 'Nouveau rotator';
    document.getElementById('rotatorActiveContainer').style.display = data ? 'block' : 'none';
    document.getElementById('rotatorIsActive').checked = data?.is_active ?? true;
    document.getElementById('rotatorModal').classList.add('active');
    document.getElementById('rotatorModalBackdrop').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeRotatorModal() {
    document.getElementById('rotatorModal').classList.remove('active');
    document.getElementById('rotatorModalBackdrop').classList.remove('active');
    document.body.style.overflow = '';
}

function deleteRotator(id, name) {
    if (confirm(`Supprimer le rotator "${name}" ?`)) {
        document.getElementById('deleteRotatorId').value = id;
        document.getElementById('deleteRotatorForm').submit();
    }
}

// ============================================================
// Modal PopCash
// ============================================================

function openCampaignModal() {
    const modal = document.getElementById('campaignModal');
    const backdrop = document.getElementById('campaignModalBackdrop');
    if (!modal || !backdrop) return;

    // Reset campaign form si présent
    const campaignSelect = document.getElementById('campaignSelect');
    if (campaignSelect) {
        campaignSelect.value = '';
    }
    const statusInput = document.getElementById('campaignStatusInput');
    if (statusInput) {
        statusInput.value = '';
    }

    modal.classList.add('active');
    backdrop.classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeCampaignModal() {
    const modal = document.getElementById('campaignModal');
    const backdrop = document.getElementById('campaignModalBackdrop');
    if (!modal || !backdrop) return;

    modal.classList.remove('active');
    backdrop.classList.remove('active');
    document.body.style.overflow = '';
}

function toggleApiKeyVisibility() {
    const input = document.getElementById('popcashApiKey');
    const icon = document.getElementById('eyeIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />';
    } else {
        input.type = 'password';
        icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />';
    }
}

function setCampaignStatus(status) {
    const select = document.getElementById('campaignSelect');

    if (!select || !select.value) {
        alert('Veuillez sélectionner une campagne');
        return;
    }

    document.getElementById('campaignStatusInput').value = status;
    document.getElementById('campaignForm').submit();
}

// ============================================================
// Fermeture modales avec Escape et event listeners
// ============================================================

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeLinkModal();
        closeRotatorModal();
        closeCampaignModal();
    }
});

// Event listeners pour auto-génération
document.addEventListener('DOMContentLoaded', function() {
    const sourceSelect = document.getElementById('linkSource');
    const nameInput = document.getElementById('linkName');
    const whitenedUrlInput = document.getElementById('linkWhitenedUrl');

    if (sourceSelect && nameInput) {
        // Auto-génération du nom quand on sélectionne une source
        sourceSelect.addEventListener('change', function() {
            if (isEditMode) return;
            if (nameInput.value && !nameInput.value.startsWith('Lien ')) return;

            const generatedName = generateLinkName(this.value);
            if (generatedName) {
                nameInput.value = generatedName;
                nameInput.classList.add('flash-update');
                setTimeout(() => nameInput.classList.remove('flash-update'), 800);
            }
        });
    }

    if (whitenedUrlInput) {
        // Résolution de l'URL d'origine quand on entre l'URL blanchie
        whitenedUrlInput.addEventListener('input', function() {
            if (isEditMode) return;

            clearTimeout(resolveTimeout);
            resolveTimeout = setTimeout(() => {
                resolveOriginalUrl(this.value.trim());
            }, 800);
        });

        whitenedUrlInput.addEventListener('paste', function() {
            if (isEditMode) return;

            setTimeout(() => {
                resolveOriginalUrl(this.value.trim());
            }, 100);
        });
    }
});
