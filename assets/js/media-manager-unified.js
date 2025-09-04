/**
 * MEDIA MANAGER UNIFICATO
 * Sistema unificato per gestione media su pagine creazione e modifica azienda
 * Supporta Piano Base (3 media) e Piano Pro (5 media)
 * 
 * FUNZIONALITÀ:
 * - Event listener automatico per pulsanti "Aggiungi Media"
 * - Gestione dinamica slot media
 * - Validazioni formati e dimensioni file
 * - Controllo limiti per Piano Base/Pro
 * - Preview immagini e gestione URL
 * - Interfaccia responsive e user-friendly
 */

class MediaManagerUnified {
    constructor() {
        this.mediaContainer = null;
        this.addMediaBtn = null;
        this.totalMediaCountSpan = null;
        this.mediaLimitSpan = null;
        
        this.newMediaCount = 0;
        this.existingMediaCount = 0;
        this.maxMedia = 3; // Default Piano Base
        this.isVideoAllowed = false; // Default Piano Base
        
        this.init();
    }
    
    init() {
        console.log('🎬 MediaManagerUnified - Inizializzazione sistema...');
        
        // Attendi DOM ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupMediaManager());
        } else {
            this.setupMediaManager();
        }
    }
    
    setupMediaManager() {
        console.log('🔧 Setup Media Manager...');
        
        // Cerca container media (supporta entrambi gli ID possibili)
        this.mediaContainer = document.getElementById('media-container') || 
                              document.getElementById('media-list');
        
        // Cerca pulsante aggiungi media
        this.addMediaBtn = document.getElementById('add-media-btn') || 
                           document.getElementById('addMediaBtn');
        
        // Cerca contatori
        this.totalMediaCountSpan = document.getElementById('total-media-count');
        this.mediaLimitSpan = document.getElementById('media-limit');
        
        if (!this.mediaContainer || !this.addMediaBtn) {
            console.warn('⚠️ Elementi media non trovati - sistema disabilitato');
            console.log('Container:', this.mediaContainer, 'Button:', this.addMediaBtn);
            return;
        }
        
        console.log('✅ Elementi trovati:', {
            container: this.mediaContainer.id,
            button: this.addMediaBtn.id
        });
        
        // Determina configurazione pacchetto
        this.detectPackageConfig();
        
        // Conta media esistenti
        this.countExistingMedia();
        
        // Setup event listener
        this.setupEventListeners();
        
        // Aggiorna UI iniziale
        this.updateUI();
        
        console.log('🚀 MediaManagerUnified configurato:', {
            maxMedia: this.maxMedia,
            videoAllowed: this.isVideoAllowed,
            existingCount: this.existingMediaCount
        });
    }
    
    detectPackageConfig() {
        // Cerca configurazione dal container media
        const uploadContainer = document.getElementById('media-upload-container');
        if (uploadContainer) {
            const packageType = uploadContainer.dataset.package;
            if (packageType === 'foto_video') {
                this.maxMedia = 5;
                this.isVideoAllowed = true;
                console.log('📦 Rilevato Piano Pro (foto_video)');
            } else {
                this.maxMedia = 3;
                this.isVideoAllowed = false;
                console.log('📦 Rilevato Piano Base (foto)');
            }
            return;
        }
        
        // Fallback: cerca indicatori nel DOM
        if (document.querySelector('[data-package="foto_video"]') || 
            document.querySelector('.badge:contains("Piano Pro")') ||
            this.addMediaBtn?.textContent?.includes('max 5')) {
            this.maxMedia = 5;
            this.isVideoAllowed = true;
            console.log('📦 Rilevato Piano Pro (fallback)');
        } else {
            this.maxMedia = 3;
            this.isVideoAllowed = false;
            console.log('📦 Rilevato Piano Base (fallback)');
        }
    }
    
    countExistingMedia() {
        // Conta media esistenti (per pagina modifica)
        const existingMediaItems = document.querySelectorAll('[id*="existing-media"], .existing-media-item');
        this.existingMediaCount = existingMediaItems.length;
        console.log(`📊 Media esistenti: ${this.existingMediaCount}`);
    }
    
    setupEventListeners() {
        // Event listener principale
        this.addMediaBtn.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('🖱️ Click su Aggiungi Media');
            this.addMediaSlot();
        });
        
        console.log('✅ Event listener configurati');
    }
    
    addMediaSlot() {
        const totalCount = this.existingMediaCount + this.newMediaCount;
        
        if (totalCount >= this.maxMedia) {
            this.showMessage(`Limite massimo raggiunto (${this.maxMedia} media)`, 'warning');
            return;
        }
        
        console.log(`➕ Aggiunta nuovo slot media (${totalCount + 1}/${this.maxMedia})`);
        
        const mediaId = 'new_media_' + Date.now();
        const mediaItem = this.createMediaSlot(mediaId);
        
        this.mediaContainer.appendChild(mediaItem);
        this.newMediaCount++;
        
        this.updateUI();
        this.setupMediaSlotEvents(mediaItem, mediaId);
    }
    
    createMediaSlot(mediaId) {
        const mediaItem = document.createElement('div');
        mediaItem.className = 'media-item card mb-3';
        mediaItem.dataset.mediaId = mediaId;
        
        const videoOptions = this.isVideoAllowed ? `
            <option value="video">🎥 Video</option>
            <option value="youtube">📺 YouTube</option>
        ` : '';
        
        mediaItem.innerHTML = `
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tipo Media:</label>
                        <select class="form-control media-type-select" name="new_media_types[]" data-media-id="${mediaId}">
                            <option value="image">📷 Foto</option>
                            <option value="link">🔗 Foto Link</option>
                            ${videoOptions}
                        </select>
                    </div>
                    <div class="col-md-8">
                        <div class="media-input-container">
                            <div class="file-input" id="file-input-${mediaId}">
                                <label class="form-label fw-semibold">Carica File:</label>
                                <input type="file" class="form-control" name="new_media_files[]" accept="image/*">
                                <small class="form-text text-muted">Max 10MB. Formati: JPG, PNG, GIF</small>
                            </div>
                            <div class="url-input" id="url-input-${mediaId}" style="display: none;">
                                <label class="form-label fw-semibold">URL:</label>
                                <input type="url" class="form-control" name="new_media_urls[]" placeholder="https://...">
                                <small class="form-text text-muted">Link diretto all'immagine o video YouTube</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-media" data-media-id="${mediaId}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="media-preview mt-3" id="preview-${mediaId}"></div>
            </div>
        `;
        
        return mediaItem;
    }
    
    setupMediaSlotEvents(mediaItem, mediaId) {
        // Event listener cambio tipo media
        const typeSelect = mediaItem.querySelector('.media-type-select');
        typeSelect.addEventListener('change', (e) => {
            this.toggleMediaInput(e.target.value, mediaId);
        });
        
        // Event listener rimozione media
        const removeBtn = mediaItem.querySelector('.remove-media');
        removeBtn.addEventListener('click', () => {
            this.removeMediaSlot(mediaId);
        });
        
        // Event listener preview file
        const fileInput = mediaItem.querySelector('input[type="file"]');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => {
                this.previewFile(e.target, mediaId);
            });
        }
        
        // Event listener preview URL
        const urlInput = mediaItem.querySelector('input[type="url"]');
        if (urlInput) {
            urlInput.addEventListener('blur', (e) => {
                this.previewURL(e.target.value, mediaId);
            });
        }
    }
    
    toggleMediaInput(type, mediaId) {
        const fileInput = document.getElementById(`file-input-${mediaId}`);
        const urlInput = document.getElementById(`url-input-${mediaId}`);
        const fileInputField = fileInput.querySelector('input[type="file"]');
        const urlInputField = urlInput.querySelector('input[type="url"]');
        
        // Reset previous values
        fileInputField.value = '';
        urlInputField.value = '';
        
        if (type === 'image') {
            fileInput.style.display = 'block';
            urlInput.style.display = 'none';
            fileInputField.accept = 'image/*';
            fileInputField.name = 'new_media_files[]';
        } else if (type === 'video') {
            fileInput.style.display = 'block';
            urlInput.style.display = 'none';
            fileInputField.accept = 'video/*';
            fileInputField.name = 'new_media_files[]';
        } else if (type === 'link' || type === 'youtube') {
            fileInput.style.display = 'none';
            urlInput.style.display = 'block';
            urlInputField.name = 'new_media_urls[]';
            urlInputField.placeholder = type === 'youtube' ? 
                'https://www.youtube.com/watch?v=...' : 
                'https://esempio.com/immagine.jpg';
        }
        
        this.clearPreview(mediaId);
    }
    
    removeMediaSlot(mediaId) {
        const mediaItem = document.querySelector(`[data-media-id="${mediaId}"]`);
        if (mediaItem) {
            mediaItem.remove();
            this.newMediaCount--;
            this.updateUI();
            console.log(`🗑️ Rimosso slot media: ${mediaId}`);
        }
    }
    
    previewFile(fileInput, mediaId) {
        const file = fileInput.files[0];
        const previewContainer = document.getElementById(`preview-${mediaId}`);
        
        if (!file) {
            this.clearPreview(mediaId);
            return;
        }
        
        // Validazione file
        if (!this.validateFile(file)) {
            fileInput.value = '';
            this.clearPreview(mediaId);
            return;
        }
        
        const reader = new FileReader();
        reader.onload = (e) => {
            if (file.type.startsWith('image/')) {
                previewContainer.innerHTML = `
                    <div class="text-center">
                        <img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 150px;">
                        <p class="small text-muted mt-1">${file.name} (${(file.size/1024/1024).toFixed(1)}MB)</p>
                    </div>
                `;
            } else if (file.type.startsWith('video/')) {
                previewContainer.innerHTML = `
                    <div class="text-center">
                        <video controls style="max-width: 200px; max-height: 150px;">
                            <source src="${e.target.result}" type="${file.type}">
                        </video>
                        <p class="small text-muted mt-1">${file.name} (${(file.size/1024/1024).toFixed(1)}MB)</p>
                    </div>
                `;
            }
        };
        reader.readAsDataURL(file);
    }
    
    previewURL(url, mediaId) {
        const previewContainer = document.getElementById(`preview-${mediaId}`);
        
        if (!url) {
            this.clearPreview(mediaId);
            return;
        }
        
        // YouTube URL
        if (url.includes('youtube.com') || url.includes('youtu.be')) {
            const videoId = this.extractYouTubeId(url);
            if (videoId) {
                previewContainer.innerHTML = `
                    <div class="text-center">
                        <img src="https://img.youtube.com/vi/${videoId}/mqdefault.jpg" alt="YouTube Preview" class="img-thumbnail" style="max-width: 200px;">
                        <p class="small text-muted mt-1">Video YouTube</p>
                    </div>
                `;
            } else {
                this.showMessage('URL YouTube non valido', 'danger');
                this.clearPreview(mediaId);
            }
        }
        // Image URL
        else if (this.isImageUrl(url)) {
            previewContainer.innerHTML = `
                <div class="text-center">
                    <img src="${url}" alt="Preview" class="img-thumbnail" style="max-width: 200px; max-height: 150px;" 
                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgdmlld0JveD0iMCAwIDIwMCAxNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIxNTAiIGZpbGw9IiNmOGY5ZmEiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzZjNzU3ZCI+RXJyb3JlPC90ZXh0Pjwvc3ZnPgo=';">
                    <p class="small text-muted mt-1">Immagine da URL</p>
                </div>
            `;
        } else {
            this.showMessage('URL non riconosciuto come immagine o video', 'warning');
            this.clearPreview(mediaId);
        }
    }
    
    clearPreview(mediaId) {
        const previewContainer = document.getElementById(`preview-${mediaId}`);
        if (previewContainer) {
            previewContainer.innerHTML = '';
        }
    }
    
    validateFile(file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        
        if (file.size > maxSize) {
            this.showMessage('File troppo grande (max 10MB)', 'danger');
            return false;
        }
        
        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (this.isVideoAllowed) {
            allowedTypes.push('video/mp4', 'video/avi', 'video/mov');
        }
        
        if (!allowedTypes.includes(file.type)) {
            this.showMessage('Formato file non supportato', 'danger');
            return false;
        }
        
        return true;
    }
    
    updateUI() {
        const totalCount = this.existingMediaCount + this.newMediaCount;
        
        // Aggiorna contatori se presenti
        if (this.totalMediaCountSpan) {
            this.totalMediaCountSpan.textContent = totalCount;
        }
        if (this.mediaLimitSpan) {
            this.mediaLimitSpan.textContent = this.maxMedia;
        }
        
        // Mostra/nascondi pulsante aggiungi
        if (this.addMediaBtn) {
            if (totalCount >= this.maxMedia) {
                this.addMediaBtn.style.display = 'none';
            } else {
                this.addMediaBtn.style.display = 'inline-block';
                this.addMediaBtn.innerHTML = `
                    <i class="bi bi-plus-lg"></i> 
                    Aggiungi Media (${totalCount}/${this.maxMedia})
                `;
            }
        }
    }
    
    // Utility functions
    extractYouTubeId(url) {
        const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
        const match = url.match(regex);
        return match ? match[1] : null;
    }
    
    isImageUrl(url) {
        // SUPPORTO DATA URL BASE64 + ESTENSIONI TRADIZIONALI
        
        // 1. Supporto data URL base64 per immagini
        if (url.startsWith('data:image/')) {
            console.log('🖼️ Riconosciuto data URL base64 come immagine valida');
            return true;
        }
        
        // 2. Supporto estensioni file tradizionali
        const hasImageExtension = /\.(jpg|jpeg|png|gif|webp)(\?.*)?$/i.test(url);
        if (hasImageExtension) {
            console.log('🖼️ Riconosciuta immagine da estensione file');
            return true;
        }
        
        // 3. Supporto URL senza estensione ma con parametri immagine
        const hasImageParam = /[?&](format|ext|type)=(jpg|jpeg|png|gif|webp)/i.test(url);
        if (hasImageParam) {
            console.log('🖼️ Riconosciuta immagine da parametri URL');
            return true;
        }
        
        console.log('❌ URL non riconosciuto come immagine:', url.substring(0, 100) + '...');
        return false;
    }
    
    showMessage(message, type = 'info') {
        // Crea notifica temporanea
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto-remove dopo 5 secondi
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
        
        console.log(`📢 ${type.toUpperCase()}: ${message}`);
    }
}

// Auto-inizializzazione globale
window.MediaManagerUnified = MediaManagerUnified;

// Inizializza automaticamente quando il DOM è ready
document.addEventListener('DOMContentLoaded', function() {
    new MediaManagerUnified();
});

console.log('📦 MediaManagerUnified - Modulo caricato');
