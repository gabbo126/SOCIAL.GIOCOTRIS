/**
 * UNIFIED MEDIA UPLOADER SYSTEM
 * Sistema unificato per upload media su pagine creazione e modifica azienda
 * Supporta pacchetti: Token A (foto): 3 media, Token B (foto_video): 5 media
 * VIMEO RIMOSSO - Solo YouTube supportato per video link
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('UNIFIED MEDIA UPLOADER - Sistema caricato');
    
    // Elementi principali
    const mediaUploads = document.getElementById('mediaUploads');
    const mediaInputsContainer = document.getElementById('mediaInputsContainer');
    const addMediaBtn = document.getElementById('addMediaBtn');
    const mediaCounter = document.querySelector('.media-counter');
    
    if (!mediaUploads || !mediaInputsContainer || !addMediaBtn) {
        console.warn('⚠️ Elementi media upload non trovati - sistema disabilitato');
        return;
    }
    
    // Configurazione pacchetti
    const packageType = mediaUploads.getAttribute('data-package') || 'foto';
    const formMode = mediaUploads.getAttribute('data-mode') || 'create';
    
    const PACKAGE_CONFIG = {
        'foto': {
            maxMedia: 3,
            allowedTypes: ['image', 'link'],
            title: 'Pacchetto Base',
            description: 'Solo immagini e link a immagini'
        },
        'foto_video': {
            maxMedia: 5,
            allowedTypes: ['image', 'video', 'youtube'],
            title: 'Pacchetto Completo', 
            description: 'Immagini, video e link YouTube'
        }
    };
    
    const currentPackage = PACKAGE_CONFIG[packageType];
    let mediaCount = 0;
    
    console.log('📦 Configurazione pacchetto:', {
        type: packageType,
        mode: formMode,
        maxMedia: currentPackage.maxMedia,
        allowedTypes: currentPackage.allowedTypes
    });
    
    /**
     * Aggiorna il contatore media
     */
    function updateMediaCounter() {
        const currentInputs = mediaInputsContainer.querySelectorAll('.media-input-group');
        mediaCount = currentInputs.length;
        
        if (mediaCounter) {
            mediaCounter.textContent = `${mediaCount}/${currentPackage.maxMedia}`;
        }
        
        // Disabilita pulsante se limite raggiunto
        addMediaBtn.disabled = mediaCount >= currentPackage.maxMedia;
        
        // Gestisci pulsanti rimozione
        const removeButtons = mediaInputsContainer.querySelectorAll('.media-remove-btn');
        removeButtons.forEach(btn => {
            btn.disabled = mediaCount <= 1;
        });
        
        console.log(`Media count aggiornato: ${mediaCount}/${currentPackage.maxMedia}`);
    }
    
    /**
     * Crea un nuovo gruppo input media
     */
    function createMediaInputGroup() {
        const mediaId = Date.now() + '-' + Math.random().toString(36).substr(2, 9);
        const newGroup = document.createElement('div');
        newGroup.className = 'media-input-group';
        newGroup.setAttribute('data-media-id', mediaId);
        
        // Opzioni dropdown basate su pacchetto
        let typeOptions = '<option value="image" selected>Immagine</option>';
        
        if (currentPackage.allowedTypes.includes('video')) {
            typeOptions += '<option value="video">Video</option>';
        }
        
        if (currentPackage.allowedTypes.includes('youtube')) {
            typeOptions += '<option value="youtube">🔗 Link YouTube</option>';
        } else if (currentPackage.allowedTypes.includes('link')) {
            typeOptions += '<option value="link">🔗 Link Immagine</option>';
        }
        
        // Template HTML unificato
        newGroup.innerHTML = `
            <div class="media-input-header">
                <select class="media-type-select" style="max-width: 150px; padding: 5px; border-radius: 5px;">
                    ${typeOptions}
                </select>
                <button type="button" class="media-remove-btn btn-danger" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 5px; margin-left: 10px;">
                    🗑️ Rimuovi
                </button>
            </div>
            
            <div class="media-input-container" style="margin-top: 10px;">
                <!-- File input (default visible) -->
                <div class="file-input-section" id="file-section-${mediaId}">
                    <label class="form-label">📁 Carica File:</label>
                    <input type="file" class="form-control media-file-input" name="media_files[]" accept="image/*" style="border: 2px solid #dee2e6; border-radius: 5px; padding: 8px;">
                    <small class="form-text text-muted">Max 10MB. Formati: JPG, PNG, GIF, WEBP</small>
                    <div class="file-preview" style="display: none; margin-top: 10px;">
                        <img class="preview-image" style="max-width: 100px; max-height: 100px; border-radius: 5px;" alt="Preview">
                    </div>
                </div>
                
                <!-- URL input (hidden by default) -->
                <div class="url-input-section" id="url-section-${mediaId}" style="display: none;">
                    <label class="form-label url-label">🔗 Link:</label>
                    <input type="url" class="form-control media-url-input" name="media_urls[]" placeholder="https://..." style="border: 2px solid #dee2e6; border-radius: 5px; padding: 8px;">
                    <small class="form-text text-muted url-help">Inserisci link valido</small>
                    <div class="url-validation" style="margin-top: 5px;">
                        <small class="validation-message"></small>
                    </div>
                    <div class="url-preview" style="display: none; margin-top: 10px;">
                        <img class="preview-image" style="max-width: 100px; max-height: 100px; border-radius: 5px;" alt="Preview">
                    </div>
                </div>
                
                <!-- Hidden input per tipo media -->
                <input type="hidden" class="media-type-hidden" name="media_types[]" value="image">
            </div>
        `;
        
        // Aggiungi al container
        mediaInputsContainer.appendChild(newGroup);
        
        // Inizializza event listeners
        initializeMediaInputGroup(newGroup);
        
        return newGroup;
    }
    
    /**
     * Inizializza event listeners per un gruppo input
     */
    function initializeMediaInputGroup(inputGroup) {
        const mediaId = inputGroup.getAttribute('data-media-id');
        const typeSelect = inputGroup.querySelector('.media-type-select');
        const fileInput = inputGroup.querySelector('.media-file-input');
        const urlInput = inputGroup.querySelector('.media-url-input');
        const typeHidden = inputGroup.querySelector('.media-type-hidden');
        const removeBtn = inputGroup.querySelector('.media-remove-btn');
        
        const fileSection = inputGroup.querySelector(`#file-section-${mediaId}`);
        const urlSection = inputGroup.querySelector(`#url-section-${mediaId}`);
        const urlLabel = inputGroup.querySelector('.url-label');
        const urlHelp = inputGroup.querySelector('.url-help');
        
        // Event listener cambio tipo media
        typeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            typeHidden.value = selectedType;
            
            console.log(`🔄 Cambio tipo media: ${selectedType}`);
            
            // Mostra/nascondi sezioni appropriate
            if (selectedType === 'image' || selectedType === 'video') {
                fileSection.style.display = 'block';
                urlSection.style.display = 'none';
                
                // Aggiorna attributi file input
                if (selectedType === 'video') {
                    fileInput.setAttribute('accept', 'video/*');
                    fileSection.querySelector('.form-text').textContent = 'Max 100MB. Formati: MP4, AVI, MOV, WEBM';
                } else {
                    fileInput.setAttribute('accept', 'image/*');
                    fileSection.querySelector('.form-text').textContent = 'Max 10MB. Formati: JPG, PNG, GIF, WEBP';
                }
            } else {
                // Link mode
                fileSection.style.display = 'none';
                urlSection.style.display = 'block';
                
                // Configura per tipo link
                if (selectedType === 'youtube') {
                    urlLabel.textContent = '🔗 Link YouTube:';
                    urlInput.placeholder = 'https://youtube.com/watch?v=... o https://youtu.be/...';
                    urlHelp.textContent = 'Inserisci qualsiasi URL YouTube (verrà normalizzato automaticamente)';
                } else {
                    urlLabel.textContent = '🔗 Link Immagine:';
                    urlInput.placeholder = 'https://esempio.com/immagine.jpg';
                    urlHelp.textContent = 'Inserisci link diretto a immagine (.jpg, .png, .webp, ecc.)';
                }
            }
        });
        
        // Event listener preview file
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            const preview = fileSection.querySelector('.file-preview');
            const previewImg = fileSection.querySelector('.preview-image');
            
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImg.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                preview.style.display = 'none';
            }
        });
        
        // Event listener validazione URL
        urlInput.addEventListener('input', function() {
            const url = this.value.trim();
            const validationDiv = urlSection.querySelector('.url-validation');
            const validationMsg = validationDiv.querySelector('.validation-message');
            const selectedType = typeSelect.value;
            
            if (!url) {
                validationMsg.textContent = '';
                validationMsg.className = 'validation-message';
                return;
            }
            
            // Validazione in base al tipo
            if (selectedType === 'youtube') {
                validateYouTubeLink(url, validationMsg);
            } else if (selectedType === 'link') {
                validateImageLink(url, validationMsg, urlSection);
            }
        });
        
        // Event listener rimozione
        removeBtn.addEventListener('click', function() {
            if (mediaCount > 1) {
                inputGroup.remove();
                updateMediaCounter();
                console.log('🗑️ Media group rimosso');
            }
        });
    }
    
    /**
     * Valida link YouTube (VIMEO RIMOSSO)
     */
    function validateYouTubeLink(url, messageElement) {
        const isYoutube = url.includes('youtube.com') || url.includes('youtu.be');
        
        if (isYoutube) {
            messageElement.textContent = 'Link YouTube valido ✅';
            messageElement.className = 'validation-message text-success';
            console.log('✅ YouTube link valido:', url);
        } else {
            messageElement.textContent = 'Inserisci un link YouTube valido';
            messageElement.className = 'validation-message text-danger';
        }
    }
    
    /**
     * Valida link immagine
     */
    function validateImageLink(url, messageElement, urlSection) {
        const validImageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.bmp'];
        const isValidImageLink = validImageExtensions.some(ext => 
            url.toLowerCase().includes(ext)
        );
        
        // Supporto data URL (base64)
        const isDataImageUrl = url.startsWith('data:image/');
        
        if (isValidImageLink || isDataImageUrl) {
            messageElement.textContent = 'Link immagine valido ✅';
            messageElement.className = 'validation-message text-success';
            
            // Prova a mostrare preview
            const preview = urlSection.querySelector('.url-preview');
            const previewImg = urlSection.querySelector('.preview-image');
            
            if (preview && previewImg && !isDataImageUrl) {
                previewImg.src = url;
                previewImg.onload = () => preview.style.display = 'block';
                previewImg.onerror = () => preview.style.display = 'none';
            }
            
            console.log('✅ Image link valido:', url);
        } else {
            messageElement.textContent = 'Inserisci un link immagine valido (.jpg, .png, .webp, ecc.) o data URL';
            messageElement.className = 'validation-message text-danger';
            
            // Nascondi preview
            const preview = urlSection.querySelector('.url-preview');
            if (preview) preview.style.display = 'none';
        }
    }
    
    /**
     * Event listener pulsante aggiungi media
     */
    addMediaBtn.addEventListener('click', function() {
        if (mediaCount >= currentPackage.maxMedia) {
            alert(`Puoi caricare al massimo ${currentPackage.maxMedia} file multimediali`);
            return;
        }
        
        createMediaInputGroup();
        updateMediaCounter();
        
        console.log('➕ Nuovo media group aggiunto');
    });
    
    /**
     * Inizializzazione
     */
    function initialize() {
        // Crea il primo input se container vuoto
        if (mediaInputsContainer.children.length === 0) {
            createMediaInputGroup();
        } else {
            // Inizializza input esistenti
            const existingGroups = mediaInputsContainer.querySelectorAll('.media-input-group');
            existingGroups.forEach(group => {
                initializeMediaInputGroup(group);
            });
        }
        
        updateMediaCounter();
        console.log('Sistema media upload inizializzato completamente');
    }
    
    // Avvia sistema
    initialize();
    
    // Esporta funzioni per debugging
    window.UnifiedMediaUploader = {
        config: currentPackage,
        packageType: packageType,
        formMode: formMode,
        updateCounter: updateMediaCounter,
        addMedia: () => addMediaBtn.click()
    };
});

/**
 * CSS UTILITY CLASSES
 */
const mediaUploaderStyles = `
<style>
.text-success { color: #28a745 !important; }
.text-danger { color: #dc3545 !important; }
.btn-danger { background: #dc3545; color: white; border: none; }
.btn-danger:hover { background: #c82333; }

.media-input-group {
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 15px;
    background: white;
    transition: all 0.3s ease;
}

.media-input-group:hover {
    border-color: #007bff;
    box-shadow: 0 2px 4px rgba(0,123,255,0.1);
}

.media-input-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 10px;
    padding-bottom: 10px;
    border-bottom: 1px solid #f1f3f4;
}

.file-preview, .url-preview {
    padding: 10px;
    background: #f8f9fa;
    border-radius: 5px;
    border: 1px solid #e9ecef;
}

.validation-message {
    font-weight: 500;
}

@media (max-width: 768px) {
    .media-input-header {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }
    
    .media-type-select {
        max-width: 100% !important;
    }
}
</style>
`;

// Inject styles
document.head.insertAdjacentHTML('beforeend', mediaUploaderStyles);
