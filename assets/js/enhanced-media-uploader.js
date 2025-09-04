/**
 * ENHANCED MEDIA UPLOADER - Sistema Upload Avanzato
 * Supporto formati estesi, URL universali, drag & drop, validazione asincrona
 */

class EnhancedMediaUploader {
    constructor() {
        // Configurazione formati supportati
        this.supportedFormats = [
            // Standard web
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'svg',
            // Desktop formats
            'bmp', 'tiff', 'tif', 'ico', 
            // Mobile/moderni
            'heic', 'heif',
            // Professionali
            'raw', 'psd', 'xcf'
        ];
        
        this.mediaContainer = null;
        this.addMediaBtn = null;
        this.newMediaCount = 0;
        this.existingMediaCount = 0;
        this.maxMedia = 3;
        this.isVideoAllowed = false;
        this.activeTab = 'file';
        
        this.init();
    }
    
    init() {
        console.log('🎬 EnhancedMediaUploader - Inizializzazione...');
        
        // 🚨 TIMEOUT CRITICO: Evita loading infiniti
        this.initTimeout = setTimeout(() => {
            console.error('⏰ Timeout inizializzazione - Attivazione fallback automatico');
            this.activateBasicFallback();
        }, 8000); // 8 secondi timeout
        
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.safeSetupUploader());
        } else {
            this.safeSetupUploader();
        }
    }
    
    /**
     * 🛡️ Setup sicuro con retry logic
     */
    safeSetupUploader() {
        try {
            console.log('🛡️ Avvio sicuro setup uploader...');
            this.setupUploader();
            
            // 🎯 STEP FINALE CRITICO: Cancella timeout dopo successo completo
            if (this.initTimeout) {
                console.log('⏰ Setup completato con successo - Cancellazione timeout...');
                clearTimeout(this.initTimeout);
                this.initTimeout = null;
                console.log('✅ Timeout cancellato - Enhanced Media Uploader operativo!');
            } else {
                console.log('⚠️ Attenzione: initTimeout già null o non impostato');
            }
        } catch (error) {
            console.error('❌ Errore durante setup:', error);
            this.handleSetupFailure();
        }
    }
    
    /**
     * 🔄 Gestisce il fallimento del setup con retry
     */
    handleSetupFailure() {
        console.warn('🔄 Setup fallito, tentativo retry in 2 secondi...');
        
        setTimeout(() => {
            try {
                this.setupUploader();
                if (this.initTimeout) {
                    clearTimeout(this.initTimeout);
                    this.initTimeout = null;
                }
            } catch (error) {
                console.error('❌ Retry fallito, attivazione fallback immediato');
                this.activateBasicFallback();
            }
        }, 2000);
    }
    
    setupUploader() {
        console.log('🔧 Setup Enhanced Media Uploader...');
        
        // 🚨 FIX CRITICO: Selettori DOM corretti per matching con template
        this.mediaContainer = document.getElementById('advanced-media-container') || 
                              document.getElementById('media-upload-container') || 
                              document.getElementById('media-container') || 
                              document.getElementById('media-list');
        
        this.addMediaBtn = document.getElementById('add-media-btn') || 
                           document.getElementById('addMediaBtn');
        
        // 🚨 FALLBACK ROBUSTO: Se container non trovato, cerca alternative
        if (!this.mediaContainer) {
            console.warn('⚠️ Container principale non trovato, cercando alternative...');
            this.mediaContainer = document.querySelector('.enhanced-media-container') ||
                                 document.querySelector('[data-package]') ||
                                 document.querySelector('.media-preview-grid');
        }
        
        if (!this.addMediaBtn) {
            console.warn('⚠️ Pulsante add-media non trovato, cercando alternative...');
            this.addMediaBtn = document.querySelector('button[onclick*="add"]') ||
                              document.querySelector('.btn-primary.d-none');
        }
        
        if (!this.mediaContainer) {
            console.error('❌ Nessun container media trovato - attivazione fallback');
            this.activateBasicFallback();
            return;
        }
        
        // ✅ VALIDAZIONE SICURA: Rilevamento tipo pacchetto
        try {
            const packageType = document.querySelector('[data-package]');
            if (packageType) {
                const planType = packageType.getAttribute('data-package');
                this.maxMedia = planType === 'foto_video' ? 5 : 3;
                this.isVideoAllowed = planType === 'foto_video';
                console.log(`📦 Piano rilevato: ${planType} (max ${this.maxMedia} media)`);
            } else {
                console.warn('⚠️ Tipo pacchetto non rilevato, usando default Piano Base');
                this.maxMedia = 3;
                this.isVideoAllowed = false;
            }
        } catch (error) {
            console.error('❌ Errore rilevamento pacchetto:', error);
            this.maxMedia = 3;
            this.isVideoAllowed = false;
        }
        
        // ✅ INIZIALIZZAZIONE SICURA: Sostituisce interfaccia loading
        try {
            console.log('🔄 Avvio replaceWithEnhancedInterface...');
            this.replaceWithEnhancedInterface();
            console.log('✅ Enhanced Media Uploader attivo - inizializzazione completata');
            

        } catch (error) {
            console.error('❌ Errore creazione interfaccia avanzata:', error);
            this.activateBasicFallback();
            return;
        }
    }
    
    replaceWithEnhancedInterface() {
        // 🛡️ FIX CRITICO: Gestione sicura quando addMediaBtn è null
        if (!this.addMediaBtn) {
            console.warn('⚠️ Pulsante add-media non trovato, usando container principale...');
            const container = this.mediaContainer;
            if (!container) {
                console.error('❌ Nessun container disponibile per interfaccia avanzata');
                this.activateBasicFallback();
                return;
            }
            this.createEnhancedInterfaceInContainer(container);
            return;
        }
        
        const container = this.addMediaBtn.parentNode;
        this.createEnhancedInterfaceInContainer(container);
    }
    
    /**
     * 🎨 Crea interfaccia Enhanced Media in un container specifico
     */
    createEnhancedInterfaceInContainer(container) {
        console.log('🎨 Creazione interfaccia Enhanced Media Uploader...');
        
        // 🛡️ RIMOZIONE ESPLICITA LOADING PLACEHOLDER
        const loadingPlaceholder = container.querySelector('.loading-placeholder');
        if (loadingPlaceholder) {
            console.log('🗑️ Rimozione loading placeholder...');
            loadingPlaceholder.remove();
        }
        
        container.innerHTML = `
            <!-- Enhanced Media Upload Interface -->
            <div class="enhanced-media-upload">
                <!-- Tab Navigation -->
                <div class="upload-tabs mb-3">
                    <div class="btn-group w-100" role="group">
                        <button type="button" class="btn btn-outline-primary active" data-tab="file" onclick="enhancedUploader.switchTab('file')">
                            📁 File Upload
                        </button>
                        <button type="button" class="btn btn-outline-secondary" data-tab="url" onclick="enhancedUploader.switchTab('url')">
                            🔗 URL/Link
                        </button>
                    </div>
                </div>
                
                <!-- File Upload Tab -->
                <div class="tab-content active" id="file-tab">
                    <div class="drag-drop-zone border-2 border-dashed rounded p-4 text-center" 
                         ondragover="enhancedUploader.handleDragOver(event)" 
                         ondragleave="enhancedUploader.handleDragLeave(event)"
                         ondrop="enhancedUploader.handleDrop(event)">
                        <div class="drag-content">
                            <i class="bi bi-cloud-upload fs-1 text-muted mb-2"></i>
                            <p class="mb-2">Trascina file qui o</p>
                            <button type="button" class="btn btn-primary" onclick="enhancedUploader.triggerFileSelect()">
                                <i class="bi bi-folder2-open"></i> Sfoglia File
                            </button>
                            <input type="file" id="enhanced-file-input" multiple accept="image/*,video/*" style="display: none;">
                        </div>
                        <div class="supported-formats mt-2">
                            <small class="text-muted">Supportati: ${this.supportedFormats.slice(0, 8).join(', ')}...</small>
                        </div>
                    </div>
                </div>
                
                <!-- URL Input Tab -->
                <div class="tab-content" id="url-tab" style="display: none;">
                    <div class="url-input-section">
                        <div class="input-group mb-3">
                            <input type="url" class="form-control" id="media-url-input" 
                                   placeholder="https://example.com/image.jpg o data:image/png;base64,...">
                            <button class="btn btn-success" type="button" onclick="enhancedUploader.addFromURL()">
                                <i class="bi bi-plus-lg"></i> Aggiungi
                            </button>
                        </div>
                        <div class="url-examples">
                            <small class="text-muted d-block">Esempi supportati:</small>
                            <small class="text-muted">• URL diretti: https://site.com/image.jpg</small>
                            <small class="text-muted">• Data URL: data:image/png;base64,...</small>
                            <small class="text-muted">• URL con parametri: https://api.com/img?id=123</small>
                        </div>
                    </div>
                </div>
                
                <!-- Progress & Status -->
                <div class="upload-status mt-3" id="upload-status" style="display: none;">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    <small class="text-muted mt-1" id="status-text">Elaborazione...</small>
                </div>
            </div>
        `;
        
        // Setup event listeners
        this.setupEventListeners();
    }
    
    setupEventListeners() {
        const fileInput = document.getElementById('enhanced-file-input');
        if (fileInput) {
            fileInput.addEventListener('change', (e) => this.handleFileSelect(e));
        }
    }
    
    switchTab(tabName) {
        this.activeTab = tabName;
        
        // Update tab buttons
        document.querySelectorAll('[data-tab]').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-tab') === tabName);
            btn.classList.toggle('btn-outline-primary', btn.getAttribute('data-tab') === tabName);
            btn.classList.toggle('btn-outline-secondary', btn.getAttribute('data-tab') !== tabName);
        });
        
        // Update content
        document.getElementById('file-tab').style.display = tabName === 'file' ? 'block' : 'none';
        document.getElementById('url-tab').style.display = tabName === 'url' ? 'block' : 'none';
    }
    
    handleDragOver(event) {
        event.preventDefault();
        event.stopPropagation();
        const zone = event.currentTarget;
        zone.classList.add('border-primary', 'bg-light');
    }
    
    handleDragLeave(event) {
        event.preventDefault();
        event.stopPropagation();
        const zone = event.currentTarget;
        zone.classList.remove('border-primary', 'bg-light');
    }
    
    handleDrop(event) {
        event.preventDefault();
        event.stopPropagation();
        const zone = event.currentTarget;
        zone.classList.remove('border-primary', 'bg-light');
        
        const files = Array.from(event.dataTransfer.files);
        this.processFiles(files);
    }
    
    triggerFileSelect() {
        document.getElementById('enhanced-file-input').click();
    }
    
    handleFileSelect(event) {
        const files = Array.from(event.target.files);
        this.processFiles(files);
    }
    
    async processFiles(files) {
        if (files.length === 0) return;
        
        // Check limits
        if (this.newMediaCount + files.length > this.maxMedia) {
            this.showMessage(`Limite massimo ${this.maxMedia} media raggiunto`, 'warning');
            return;
        }
        
        this.showStatus('Elaborazione file...');
        
        for (const file of files) {
            await this.processFile(file);
        }
        
        this.hideStatus();
    }
    
    async processFile(file) {
        // Validate file format
        if (!this.validateFileFormat(file)) {
            this.showMessage(`Formato ${file.name} non supportato`, 'danger');
            return;
        }
        
        // Validate file size
        if (!this.validateFileSize(file)) {
            this.showMessage(`File ${file.name} troppo grande (max 10MB)`, 'danger');
            return;
        }
        
        // Create media item
        this.createMediaItem(file, 'file');
    }
    
    async addFromURL() {
        const urlInput = document.getElementById('media-url-input');
        const url = urlInput.value.trim();
        
        if (!url) {
            this.showMessage('Inserisci un URL valido', 'warning');
            return;
        }
        
        // Check limits
        if (this.newMediaCount >= this.maxMedia) {
            this.showMessage(`Limite massimo ${this.maxMedia} media raggiunto`, 'warning');
            return;
        }
        
        this.showStatus('Validazione URL...');
        
        try {
            const isValid = await this.validateURL(url);
            if (isValid) {
                this.createMediaItem(url, 'url');
                urlInput.value = '';
                this.showMessage('Media aggiunto con successo', 'success');
            } else {
                this.showMessage('URL non valido o non accessibile', 'danger');
            }
        } catch (error) {
            this.showMessage('Errore nella validazione URL', 'danger');
            console.error('URL validation error:', error);
        }
        
        this.hideStatus();
    }
    
    validateFileFormat(file) {
        const extension = file.name.split('.').pop().toLowerCase();
        return this.supportedFormats.includes(extension);
    }
    
    validateFileSize(file) {
        const maxSize = 10 * 1024 * 1024; // 10MB
        return file.size <= maxSize;
    }
    
    async validateURL(url) {
        // Data URL validation
        if (url.startsWith('data:image/')) {
            return true;
        }
        
        // Image URL validation (enhanced from previous system)
        if (this.isImageURL(url)) {
            return true;
        }
        
        // YouTube URL validation
        if (this.isVideoAllowed && (url.includes('youtube.com') || url.includes('youtu.be'))) {
            return this.extractYouTubeId(url) !== null;
        }
        
        return false;
    }
    
    isImageURL(url) {
        // Enhanced validation from previous fix
        if (url.startsWith('data:image/')) {
            return true;
        }
        
        const hasImageExtension = new RegExp(`\\.(${this.supportedFormats.join('|')})(\\?.*)?$`, 'i').test(url);
        if (hasImageExtension) {
            return true;
        }
        
        const hasImageParam = /[?&](format|ext|type)=(jpg|jpeg|png|gif|webp)/i.test(url);
        return hasImageParam;
    }
    
    extractYouTubeId(url) {
        const regex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
        const match = url.match(regex);
        return match ? match[1] : null;
    }
    
    createMediaItem(source, type) {
        this.newMediaCount++;
        const mediaId = `media-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
        
        const mediaItem = document.createElement('div');
        mediaItem.className = 'col-md-4';
        mediaItem.innerHTML = `
            <div class="card border-secondary h-100" id="${mediaId}">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <small class="text-muted">#${this.newMediaCount}</small>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="enhancedUploader.removeMedia('${mediaId}')">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
                <div class="card-body p-2">
                    <div id="preview-${mediaId}" class="text-center">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    ${this.createMediaInput(source, type, mediaId)}
                </div>
            </div>
        `;
        
        this.mediaContainer.appendChild(mediaItem);
        
        // Generate preview
        setTimeout(() => this.generatePreview(source, type, mediaId), 100);
        
        this.updateMediaCounter();
    }
    
    createMediaInput(source, type, mediaId) {
        if (type === 'file') {
            return `<input type="file" name="media[]" style="display: none;" data-file="${mediaId}">`;
        } else {
            return `<input type="hidden" name="media_urls[]" value="${source}">`;
        }
    }
    
    generatePreview(source, type, mediaId) {
        const previewContainer = document.getElementById(`preview-${mediaId}`);
        
        if (type === 'file') {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewContainer.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" class="img-thumbnail" style="max-width: 100%; max-height: 120px;">
                `;
            };
            reader.readAsDataURL(source);
            
            // Set file to hidden input
            const fileInput = document.querySelector(`[data-file="${mediaId}"]`);
            const dt = new DataTransfer();
            dt.items.add(source);
            fileInput.files = dt.files;
        } else {
            // URL preview
            if (source.includes('youtube.com') || source.includes('youtu.be')) {
                const videoId = this.extractYouTubeId(source);
                previewContainer.innerHTML = `
                    <img src="https://img.youtube.com/vi/${videoId}/mqdefault.jpg" alt="YouTube Preview" class="img-thumbnail" style="max-width: 100%;">
                    <p class="small text-muted mt-1">Video YouTube</p>
                `;
            } else {
                previewContainer.innerHTML = `
                    <img src="${source}" alt="Preview" class="img-thumbnail" style="max-width: 100%; max-height: 120px;" 
                         onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjEyMCIgdmlld0JveD0iMCAwIDIwMCAxMjAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjIwMCIgaGVpZ2h0PSIxMjAiIGZpbGw9IiNmOGY5ZmEiLz48dGV4dCB4PSI1MCUiIHk9IjUwJSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZmlsbD0iIzZjNzU3ZCI+SW1tYWdpbmU8L3RleHQ+PC9zdmc+';">
                    <p class="small text-muted mt-1">Immagine da URL</p>
                `;
            }
        }
    }
    
    removeMedia(mediaId) {
        const mediaItem = document.getElementById(mediaId);
        if (mediaItem) {
            mediaItem.remove();
            this.newMediaCount--;
            this.updateMediaCounter();
        }
    }
    
    updateMediaCounter() {
        const totalCount = this.existingMediaCount + this.newMediaCount;
        const statusText = `${totalCount}/${this.maxMedia} media caricati`;
        
        // Update any counter elements if they exist
        const counters = document.querySelectorAll('[id*="media-count"]');
        counters.forEach(counter => {
            counter.textContent = statusText;
        });
    }
    
    showStatus(message) {
        const statusDiv = document.getElementById('upload-status');
        const statusText = document.getElementById('status-text');
        if (statusDiv && statusText) {
            statusText.textContent = message;
            statusDiv.style.display = 'block';
        }
    }
    
    hideStatus() {
        const statusDiv = document.getElementById('upload-status');
        if (statusDiv) {
            statusDiv.style.display = 'none';
        }
    }
    
    showMessage(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
    
    /**
     * 🚨 SISTEMA FALLBACK CRITICO - Attiva upload basic se sistema avanzato fallisce
     */
    activateBasicFallback() {
        console.warn('🔄 Attivazione sistema fallback upload basic...');
        
        // Trova o crea container per fallback
        let fallbackContainer = document.querySelector('.enhanced-media-container') || 
                               document.querySelector('#media-upload-container');
        
        if (!fallbackContainer) {
            // Crea container di emergenza
            fallbackContainer = document.createElement('div');
            fallbackContainer.className = 'fallback-upload-container alert alert-warning';
            const cardBody = document.querySelector('.card-body');
            if (cardBody) cardBody.appendChild(fallbackContainer);
        }
        
        // Sostituisce loading con interfaccia fallback
        fallbackContainer.innerHTML = `
            <div class="fallback-upload-system p-4">
                <!-- Alert informativo -->
                <div class="alert alert-info border-start border-4 border-info mb-3">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                        <div>
                            <h6 class="mb-1">📁 Sistema Upload Semplificato</h6>
                            <p class="mb-0 small">Upload avanzato non disponibile - Utilizzando modalità compatibilità</p>
                        </div>
                    </div>
                </div>
                
                <!-- Upload Interface Basic -->
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">📷 Seleziona Media</label>
                        <input type="file" id="fallback-file-input" class="form-control" 
                               multiple accept="image/*,video/*" onchange="enhancedUploader.handleFallbackUpload(this)">
                        <div class="form-text">Supportati: JPG, PNG, GIF, MP4, WebM | Max: 5MB per file</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">🔗 URL Diretto</label>
                        <div class="input-group">
                            <input type="url" id="fallback-url-input" class="form-control" placeholder="https://esempio.com/immagine.jpg">
                            <button type="button" class="btn btn-primary" onclick="enhancedUploader.addUrlMedia()">Aggiungi</button>
                        </div>
                    </div>
                </div>
                
                <!-- Progress -->
                <div id="fallback-progress" class="mt-3" style="display: none;">
                    <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"></div>
                    </div>
                    <small class="text-muted">Caricamento in corso...</small>
                </div>
                
                <!-- Media Preview -->
                <div id="fallback-media-preview" class="row g-3 mt-3"></div>
            </div>
        `;
        
        console.log('✅ Sistema fallback attivato');
    }
    
    /**
     * 🔄 Handler per upload fallback
     */
    handleFallbackUpload(input) {
        const files = Array.from(input.files);
        const progressBar = document.getElementById('fallback-progress');
        
        if (files.length === 0) return;
        
        // Mostra progress
        progressBar.style.display = 'block';
        
        files.forEach((file) => {
            // Validazione basic
            if (file.size > 5 * 1024 * 1024) {
                this.showFallbackError(`File ${file.name} troppo grande (max 5MB)`);
                return;
            }
            
            // Preview immediato
            const reader = new FileReader();
            reader.onload = (e) => {
                this.addFallbackMediaPreview({
                    url: e.target.result,
                    name: file.name,
                    type: file.type.startsWith('image/') ? 'image' : 'video'
                });
            };
            reader.readAsDataURL(file);
        });
        
        // Nascondi progress dopo 2 secondi
        setTimeout(() => {
            progressBar.style.display = 'none';
        }, 2000);
    }
    
    /**
     * 📷 Aggiunge preview media nel sistema fallback
     */
    addFallbackMediaPreview(media) {
        const previewContainer = document.getElementById('fallback-media-preview');
        const mediaId = 'fallback-media-' + Date.now();
        
        const mediaHtml = `
            <div class="col-md-4" id="${mediaId}">
                <div class="card border-success">
                    <div class="card-body p-2 text-center">
                        ${media.type === 'image' 
                            ? `<img src="${media.url}" class="img-fluid rounded" style="max-height: 100px;">` 
                            : `<video src="${media.url}" class="img-fluid rounded" style="max-height: 100px;" controls></video>`
                        }
                        <div class="mt-2">
                            <small class="text-muted d-block">${media.name}</small>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="document.getElementById('${mediaId}').remove()">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                        <!-- Hidden input per form submission -->
                        <input type="hidden" name="media_data[]" value="${media.url}">
                    </div>
                </div>
            </div>
        `;
        
        previewContainer.insertAdjacentHTML('beforeend', mediaHtml);
    }
    
    /**
     * 🔗 Aggiunge media da URL
     */
    addUrlMedia() {
        const urlInput = document.getElementById('fallback-url-input');
        const url = urlInput.value.trim();
        
        if (!url) {
            this.showFallbackError('Inserisci un URL valido');
            return;
        }
        
        // Validazione URL
        try {
            new URL(url);
        } catch {
            this.showFallbackError('URL non valido');
            return;
        }
        
        // Aggiungi preview
        this.addFallbackMediaPreview({
            url: url,
            name: url.split('/').pop() || 'Media da URL',
            type: 'image' // Assumi immagine per semplicità
        });
        
        urlInput.value = '';
    }
    
    /**
     * ⚠️ Mostra errore nel sistema fallback
     */
    showFallbackError(message) {
        const container = document.querySelector('.fallback-upload-system');
        if (!container) return;
        
        const existingAlert = container.querySelector('.alert-danger');
        
        // Rimuovi alert precedente
        if (existingAlert) existingAlert.remove();
        
        // Crea nuovo alert
        const alertHtml = `
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        container.insertAdjacentHTML('afterbegin', alertHtml);
    }
}

// Global instance
let enhancedUploader;

// Auto-initialization (solo se non già inizializzato manualmente)
document.addEventListener('DOMContentLoaded', function() {
    // Evita inizializzazioni multiple - controlla se già inizializzato
    if (!enhancedUploader && !window._enhancedMediaUploaderInitialized) {
        console.log('🎯 Auto-inizializzazione EnhancedMediaUploader...');
        enhancedUploader = new EnhancedMediaUploader();
        window._enhancedMediaUploaderInitialized = true;
    }
});

console.log('📦 EnhancedMediaUploader - Modulo caricato');
