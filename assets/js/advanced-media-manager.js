/**
 * 🎯 ADVANCED MEDIA MANAGER - Frontend UI/UX v2.0
 * Sistema gestione media con drag&drop, preview, limiti piani
 */

class AdvancedMediaManager {
    constructor(config = {}) {
        this.azienda_id = config.azienda_id || 0;
        this.api_base = config.api_base || 'api/media_manager.php';
        this.container = null;
        this.limits = null;
        this.media_list = [];
        
        // Config UX
        this.max_preview_size = '150px';
        this.animation_duration = 300;
        this.upload_timeout = 30000; // 30 sec
        
        this.init();
    }
    
    /**
     * 🎬 INIZIALIZZAZIONE SISTEMA
     */
    async init() {
        console.log('🎬 Inizializzazione Advanced Media Manager...');
        
        try {
            await this.loadMediaLimits();
            this.setupUI();
            this.bindEvents();
            await this.loadExistingMedia();
            
            console.log('✅ Advanced Media Manager pronto!');
        } catch (error) {
            console.error('❌ Errore inizializzazione:', error);
            this.showError('Errore durante inizializzazione sistema media');
        }
    }
    
    /**
     * 📊 CARICA LIMITI PIANO DAL BACKEND
     */
    async loadMediaLimits() {
        console.log('📊 Caricamento limiti piano media...');
        
        try {
            // Per registrazione (azienda_id = 0), usa limiti default
            if (this.azienda_id === 0) {
                this.limits = {
                    max_media: 3, // Piano Base default per registrazione
                    piano_type: 'base',
                    allowed_types: ['image'],
                    max_file_size: 5 * 1024 * 1024 // 5MB
                };
                console.log('✅ Limiti default per registrazione:', this.limits);
                return;
            }
            
            // Per modifica (azienda_id > 0), carica limiti dal backend
            const response = await fetch(`${this.api_base}?action=get_limits&azienda_id=${this.azienda_id}`);
            
            if (!response.ok) {
                throw new Error(`Errore HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success) {
                this.limits = data.limits;
                console.log('✅ Limiti piano caricati dal backend:', this.limits);
            } else {
                throw new Error(data.message || 'Errore caricamento limiti');
            }
            
        } catch (error) {
            console.warn('⚠️ Errore caricamento limiti piano, uso fallback:', error);
            
            // Fallback: limiti sicuri di default
            this.limits = {
                max_media: 3,
                piano_type: 'base',
                allowed_types: ['image'],
                max_file_size: 5 * 1024 * 1024
            };
        }
    }
    
    /**
     * 🎨 SETUP UI COMPLETA
     */
    setupUI() {
        this.container = document.getElementById('advanced-media-container');
        
        if (!this.container) {
            throw new Error('Container #advanced-media-container non trovato');
        }
        
        this.container.innerHTML = this.generateUI();
        this.setupDragDrop();
    }
    
    /**
     * 🖼️ GENERA UI COMPLETA
     */
    generateUI() {
        return `
        <div class="advanced-media-manager">
            <!-- Header con limiti piano -->
            <div class="media-header mb-4">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-0">
                            <i class="bi bi-images"></i> Gestione Media Aziendali
                        </h5>
                        <small class="text-muted">Carica immagini e video per la tua azienda</small>
                    </div>
                    <div class="col-md-4 text-end">
                        <div id="limits-badge" class="badge bg-info fs-6">
                            <i class="bi bi-info-circle"></i> Caricamento limiti...
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- SEZIONE LOGO -->
            <div class="media-section logo-section mb-5">
                <div class="section-header d-flex align-items-center mb-3">
                    <div class="section-icon me-3">
                        <i class="bi bi-badge-cc fs-3 text-primary"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-primary">Logo Aziendale</h6>
                        <small class="text-muted">Il volto della tua azienda - sempre al primo posto</small>
                    </div>
                </div>
                
                <div class="logo-container">
                    <div id="logo-dropzone" class="media-dropzone logo-dropzone">
                        <div class="dropzone-content">
                            <i class="bi bi-cloud-upload fs-1 text-primary mb-3"></i>
                            <h6>Trascina qui il tuo logo</h6>
                            <p class="mb-3 text-muted">oppure clicca per selezionare</p>
                            <div class="upload-options">
                                <button type="button" class="btn btn-outline-primary btn-sm me-2" 
                                        onclick="document.getElementById('logo-file-input').click()">
                                    <i class="bi bi-upload"></i> Scegli File
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" 
                                        onclick="this.parentElement.parentElement.parentElement.querySelector('.url-input').style.display='block'">
                                    <i class="bi bi-link-45deg"></i> URL
                                </button>
                            </div>
                            <div class="url-input mt-3" style="display: none;">
                                <div class="input-group input-group-sm">
                                    <input type="url" class="form-control" id="logo-url-input" 
                                           placeholder="https://example.com/logo.jpg">
                                    <button class="btn btn-outline-success" type="button" 
                                            onclick="advancedMediaManager.addUrlMedia('logo', this.previousElementSibling.value)">
                                        <i class="bi bi-check"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="file" id="logo-file-input" style="display: none;" 
                               accept="image/*" onchange="advancedMediaManager.handleFileUpload('logo', this.files[0])">
                    </div>
                    
                    <div id="logo-preview" class="media-preview-container mt-3" style="display: none;">
                        <!-- Logo preview will be injected here -->
                    </div>
                </div>
            </div>
            
            <!-- SEZIONE GALLERIA -->
            <div class="media-section galleria-section">
                <div class="section-header d-flex align-items-center mb-3">
                    <div class="section-icon me-3">
                        <i class="bi bi-collection fs-3 text-success"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 text-success">Galleria Media</h6>
                        <small class="text-muted">Mostra i tuoi spazi, prodotti e servizi</small>
                    </div>
                </div>
                
                <div class="galleria-container">
                    <div id="galleria-dropzone" class="media-dropzone galleria-dropzone">
                        <div class="dropzone-content">
                            <i class="bi bi-images fs-1 text-success mb-3"></i>
                            <h6>Aggiungi foto alla galleria</h6>
                            <p class="mb-3 text-muted">Trascina più file insieme o clicca per selezionare</p>
                            <div class="upload-options">
                                <button type="button" class="btn btn-outline-success btn-sm me-2" 
                                        onclick="document.getElementById('galleria-file-input').click()">
                                    <i class="bi bi-upload"></i> Scegli File
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" 
                                        onclick="this.parentElement.parentElement.parentElement.querySelector('.url-input').style.display='block'">
                                    <i class="bi bi-link-45deg"></i> URL
                                </button>
                            </div>
                            <div class="url-input mt-3" style="display: none;">
                                <div class="input-group input-group-sm">
                                    <input type="url" class="form-control" id="galleria-url-input" 
                                           placeholder="https://example.com/foto.jpg">
                                    <button class="btn btn-outline-success" type="button" 
                                            onclick="advancedMediaManager.addUrlMedia('galleria', this.previousElementSibling.value)">
                                        <i class="bi bi-check"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <input type="file" id="galleria-file-input" style="display: none;" 
                               accept="image/*,video/*" multiple 
                               onchange="advancedMediaManager.handleMultipleFileUpload('galleria', this.files)">
                    </div>
                    
                    <div id="galleria-preview" class="media-grid mt-4">
                        <!-- Galleria media will be injected here -->
                    </div>
                </div>
            </div>
            
            <!-- Loading Overlay -->
            <div id="upload-overlay" class="upload-overlay" style="display: none;">
                <div class="upload-progress">
                    <div class="spinner-border text-primary mb-3"></div>
                    <h6 id="upload-status">Caricamento in corso...</h6>
                    <div class="progress mt-2">
                        <div id="upload-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated" 
                             style="width: 0%"></div>
                    </div>
                </div>
            </div>
        </div>`;
    }
    
    /**
     * 🖱️ SETUP DRAG & DROP
     */
    setupDragDrop() {
        const dropzones = document.querySelectorAll('.media-dropzone');
        
        dropzones.forEach(dropzone => {
            const tipo = dropzone.id.includes('logo') ? 'logo' : 'galleria';
            
            // Drag events
            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                dropzone.classList.add('dragover');
            });
            
            dropzone.addEventListener('dragleave', (e) => {
                if (!dropzone.contains(e.relatedTarget)) {
                    dropzone.classList.remove('dragover');
                }
            });
            
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                dropzone.classList.remove('dragover');
                
                const files = Array.from(e.dataTransfer.files);
                
                if (files.length === 0) return;
                
                if (tipo === 'logo' && files.length > 1) {
                    this.showWarning('Il logo può essere solo uno. Sarà caricato il primo file.');
                    this.handleFileUpload('logo', files[0]);
                } else if (tipo === 'galleria') {
                    this.handleMultipleFileUpload('galleria', files);
                } else {
                    this.handleFileUpload(tipo, files[0]);
                }
            });
        });
    }
    
    /**
     * 📡 CARICA LIMITI PIANO
     */
    async loadMediaLimits() {
        try {
            const response = await fetch(`${this.api_base}?action=limits&azienda_id=${this.azienda_id}`);
            const data = await response.json();
            
            if (data.success) {
                this.limits = data.data;
                this.updateLimitsBadge();
            } else {
                throw new Error(data.error || 'Errore caricamento limiti');
            }
        } catch (error) {
            console.error('Errore caricamento limiti:', error);
            this.showError('Impossibile caricare i limiti del piano');
        }
    }
    
    /**
     * 🏷️ AGGIORNA BADGE LIMITI
     */
    updateLimitsBadge() {
        const badge = document.getElementById('limits-badge');
        if (!badge || !this.limits) return;
        
        const { piano, current_total, max_totali, current_galleria, max_galleria, can_add_galleria } = this.limits;
        
        const color = can_add_galleria ? 'bg-success' : 'bg-warning';
        const icon = can_add_galleria ? 'bi-check-circle' : 'bi-exclamation-triangle';
        
        badge.className = `badge ${color} fs-6`;
        badge.innerHTML = `
            <i class="bi ${icon}"></i> 
            Piano ${piano.toUpperCase()}: ${current_total}/${max_totali} media 
            (Galleria: ${current_galleria}/${max_galleria})
        `;
    }
    
    /**
     * 📤 UPLOAD SINGOLO FILE
     */
    async handleFileUpload(tipo, file) {
        if (!file) return;
        
        // Verifica limiti
        if (!this.checkLimitsBeforeUpload(tipo)) return;
        
        this.showUploadProgress(`Caricamento ${tipo}...`);
        
        try {
            const formData = new FormData();
            formData.append('media_file', file);
            formData.append('azienda_id', this.azienda_id);
            formData.append('tipo_media', tipo);
            
            const response = await fetch(`${this.api_base}?action=upload`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`${tipo === 'logo' ? 'Logo' : 'Media'} caricato con successo!`);
                await this.refreshMedia();
            } else {
                throw new Error(data.error || 'Errore durante upload');
            }
        } catch (error) {
            console.error('Errore upload:', error);
            this.showError(`Errore caricamento ${tipo}: ${error.message}`);
        } finally {
            this.hideUploadProgress();
        }
    }
    
    /**
     * 📤 UPLOAD MULTIPLI FILE
     */
    async handleMultipleFileUpload(tipo, files) {
        const fileArray = Array.from(files);
        
        if (fileArray.length === 0) return;
        
        // Verifica limiti per ciascun file
        const remainingSlots = this.limits.max_galleria - this.limits.current_galleria;
        
        if (fileArray.length > remainingSlots) {
            this.showWarning(`Puoi caricare solo ${remainingSlots} file aggiuntivi. Saranno caricati i primi ${remainingSlots}.`);
            fileArray.splice(remainingSlots);
        }
        
        this.showUploadProgress(`Caricamento ${fileArray.length} file...`);
        
        let completed = 0;
        let errors = 0;
        
        for (const file of fileArray) {
            try {
                await this.uploadSingleFileFromBatch(tipo, file);
                completed++;
            } catch (error) {
                errors++;
                console.error('Errore upload batch:', error);
            }
            
            // Aggiorna progress
            const progress = Math.round((completed + errors) / fileArray.length * 100);
            this.updateUploadProgress(progress);
        }
        
        this.hideUploadProgress();
        
        if (completed > 0) {
            this.showSuccess(`${completed} file caricati con successo!`);
            await this.refreshMedia();
        }
        
        if (errors > 0) {
            this.showWarning(`${errors} file non sono stati caricati a causa di errori.`);
        }
    }
    
    /**
     * 🔗 AGGIUNGI URL MEDIA
     */
    async addUrlMedia(tipo, url) {
        if (!url || !url.trim()) {
            this.showWarning('Inserisci un URL valido');
            return;
        }
        
        if (!this.checkLimitsBeforeUpload(tipo)) return;
        
        this.showUploadProgress(`Validazione URL ${tipo}...`);
        
        try {
            const formData = new FormData();
            formData.append('media_url', url.trim());
            formData.append('azienda_id', this.azienda_id);
            formData.append('tipo_media', tipo);
            
            const response = await fetch(`${this.api_base}?action=add_url`, {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess(`URL ${tipo} aggiunto con successo!`);
                
                // Pulisci input
                const input = document.getElementById(`${tipo}-url-input`);
                if (input) {
                    input.value = '';
                    input.closest('.url-input').style.display = 'none';
                }
                
                await this.refreshMedia();
            } else {
                throw new Error(data.error || 'Errore durante aggiunta URL');
            }
        } catch (error) {
            console.error('Errore aggiunta URL:', error);
            this.showError(`Errore URL ${tipo}: ${error.message}`);
        } finally {
            this.hideUploadProgress();
        }
    }
    
    /**
     * ✅ VERIFICA LIMITI PRIMA UPLOAD
     */
    checkLimitsBeforeUpload(tipo) {
        if (!this.limits) {
            this.showError('Limiti piano non caricati');
            return false;
        }
        
        if (tipo === 'galleria' && !this.limits.can_add_galleria) {
            this.showWarning(`Limite raggiunto! Piano ${this.limits.piano}: massimo ${this.limits.max_galleria} foto in galleria.`);
            return false;
        }
        
        return true;
    }
    
    /**
     * 🔄 REFRESH MEDIA ESISTENTI
     */
    async refreshMedia() {
        await this.loadExistingMedia();
        await this.loadMediaLimits(); // Aggiorna anche i limiti
    }
    
    /**
     * 📋 CARICA MEDIA ESISTENTI
     */
    async loadExistingMedia() {
        try {
            const response = await fetch(`${this.api_base}?action=list&azienda_id=${this.azienda_id}`);
            const data = await response.json();
            
            if (data.success) {
                this.media_list = data.data.media;
                this.renderExistingMedia();
            } else {
                throw new Error(data.error || 'Errore caricamento media');
            }
        } catch (error) {
            console.error('Errore caricamento media:', error);
            this.showError('Impossibile caricare i media esistenti');
        }
    }
    
    /**
     * 🖼️ RENDER MEDIA ESISTENTI
     */
    renderExistingMedia() {
        const logoPreview = document.getElementById('logo-preview');
        const galleriaPreview = document.getElementById('galleria-preview');
        
        // Reset containers
        if (logoPreview) logoPreview.innerHTML = '';
        if (galleriaPreview) galleriaPreview.innerHTML = '';
        
        this.media_list.forEach(media => {
            const mediaElement = this.createMediaElement(media);
            
            if (media.tipo === 'logo' && logoPreview) {
                logoPreview.style.display = 'block';
                logoPreview.appendChild(mediaElement);
            } else if (media.tipo === 'galleria' && galleriaPreview) {
                galleriaPreview.appendChild(mediaElement);
            }
        });
    }
    
    /**
     * 🎨 CREA ELEMENTO MEDIA
     */
    createMediaElement(media) {
        const div = document.createElement('div');
        div.className = media.tipo === 'logo' ? 'logo-item' : 'media-item';
        div.dataset.mediaId = media.id;
        
        const isImage = /\.(jpg|jpeg|png|gif|webp|avif)$/i.test(media.url);
        const mediaTag = isImage ? 
            `<img src="${media.url}" alt="${media.nome}" class="media-preview">` :
            `<video src="${media.url}" class="media-preview" controls></video>`;
        
        div.innerHTML = `
            <div class="media-card">
                <div class="media-preview-wrapper">
                    ${mediaTag}
                    <div class="media-overlay">
                        <div class="media-actions">
                            <button class="btn btn-sm btn-outline-light" onclick="advancedMediaManager.removeMedia(${media.id})" 
                                    title="Rimuovi">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="media-info">
                    <small class="text-muted d-block">${media.nome}</small>
                    <small class="text-muted">${media.sorgente === 'upload' ? '📁' : '🔗'} ${media.formato || 'Auto'}</small>
                    ${media.dimensioni ? `<small class="text-muted">${media.dimensioni}</small>` : ''}
                </div>
            </div>
        `;
        
        return div;
    }
    
    /**
     * 🗑️ RIMUOVI MEDIA
     */
    async removeMedia(mediaId) {
        if (!confirm('Sei sicuro di voler rimuovere questo media?')) return;
        
        try {
            const response = await fetch(`${this.api_base}?action=remove&media_id=${mediaId}`, {
                method: 'DELETE'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showSuccess('Media rimosso con successo');
                await this.refreshMedia();
            } else {
                throw new Error(data.error || 'Errore rimozione media');
            }
        } catch (error) {
            console.error('Errore rimozione:', error);
            this.showError(`Errore rimozione: ${error.message}`);
        }
    }
    
    /**
     * 🎭 UI FEEDBACK METHODS
     */
    showUploadProgress(message) {
        const overlay = document.getElementById('upload-overlay');
        const status = document.getElementById('upload-status');
        const progressBar = document.getElementById('upload-progress-bar');
        
        if (overlay && status) {
            status.textContent = message;
            progressBar.style.width = '0%';
            overlay.style.display = 'flex';
        }
    }
    
    updateUploadProgress(percent) {
        const progressBar = document.getElementById('upload-progress-bar');
        if (progressBar) {
            progressBar.style.width = percent + '%';
        }
    }
    
    hideUploadProgress() {
        const overlay = document.getElementById('upload-overlay');
        if (overlay) {
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 500);
        }
    }
    
    showSuccess(message) {
        this.showNotification(message, 'success');
    }
    
    showError(message) {
        this.showNotification(message, 'danger');
    }
    
    showWarning(message) {
        this.showNotification(message, 'warning');
    }
    
    showNotification(message, type = 'info') {
        // Crea toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove dopo 5 secondi
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }
    
    /**
     * 🔧 UTILITY UPLOAD BATCH
     */
    async uploadSingleFileFromBatch(tipo, file) {
        const formData = new FormData();
        formData.append('media_file', file);
        formData.append('azienda_id', this.azienda_id);
        formData.append('tipo_media', tipo);
        
        const response = await fetch(`${this.api_base}?action=upload`, {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error || 'Errore upload');
        }
        
        return data;
    }
    
    /**
     * 🎪 BIND EVENTS
     */
    bindEvents() {
        // Global reference per onclick handlers
        window.advancedMediaManager = this;
        
        // Gestione resize window per responsive
        window.addEventListener('resize', () => {
            this.handleResize();
        });
    }
    
    handleResize() {
        // Adatta UI per mobile/desktop
        const isMobile = window.innerWidth < 768;
        const mediaCards = document.querySelectorAll('.media-card');
        
        mediaCards.forEach(card => {
            if (isMobile) {
                card.classList.add('mobile-layout');
            } else {
                card.classList.remove('mobile-layout');
            }
        });
    }
}

// 🚀 AUTO-INIT se container presente
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('advanced-media-container');
    
    if (container && window.currentAziendaId) {
        window.advancedMediaManager = new AdvancedMediaManager({
            azienda_id: window.currentAziendaId
        });
    }
});
