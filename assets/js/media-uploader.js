/**
 * Script per gestire l'upload multiplo di media (immagini/video/embed)
 */
document.addEventListener('DOMContentLoaded', function() {
    // Elementi principali
    const mediaInputsContainer = document.getElementById('mediaInputsContainer');
    const addMediaBtn = document.getElementById('addMediaBtn');
    const mediaCounter = document.querySelector('.media-counter');
    
    if (!mediaInputsContainer || !addMediaBtn) return;
    
    // Otteniamo il tipo di pacchetto dal container
    const mediaContainer = document.getElementById('mediaUploads');
    const packageType = mediaContainer ? mediaContainer.getAttribute('data-package') : 'foto';
    
    // Limite massimo di media basato sul tipo pacchetto
    // Token A (foto): massimo 3 media, solo immagini e link a immagini
    // Token B (foto_video): massimo 5 media, tutto consentito
    const MAX_MEDIA = packageType === 'foto_video' ? 5 : 3;
    
    // 🚨 DEBUG - Verifica valori reali (rimosso alert invasivo)
    console.log('🔍 MEDIA UPLOADER SYSTEM:');
    console.log('   packageType:', packageType);
    console.log('   MAX_MEDIA:', MAX_MEDIA);
    console.log('   Token A (foto): 3 media max, solo immagini/link immagini');
    console.log('   Token B (foto_video): 5 media max, tutto consentito');
    
    // Contatore iniziale
    updateMediaCounter();
    
    // Event listener per aggiungere un nuovo media input
    addMediaBtn.addEventListener('click', function() {
        const currentInputs = mediaInputsContainer.querySelectorAll('.media-input-group');
        
        // Verifica limite massimo
        if (currentInputs.length >= MAX_MEDIA) {
            alert(`Puoi caricare al massimo ${MAX_MEDIA} file multimediali`);
            return;
        }
        
        // Genera il template dinamicamente basato sul tipo di pacchetto
        const videoOption = packageType === 'foto_video' ? '<option value="video">Video</option>' : '';
        const acceptTypes = packageType === 'foto_video' ? 'image/*,video/*' : 'image/*';
        // Token A: solo link a immagini, Token B: anche YouTube/video
        const urlPlaceholder = packageType === 'foto_video' ? 'Incolla URL YouTube/Vimeo' : 'Link a immagine (.jpg, .png, .webp...)';
        
        const newMediaGroup = document.createElement('div');
        newMediaGroup.className = 'col-md-6 media-input-group';
        newMediaGroup.innerHTML = `
            <div class="input-group">
                <select class="form-select media-type-select" style="max-width: 100px;">
                    <option value="image" selected>Immagine</option>
                    ${videoOption}
                    <option value="youtube">Link</option>
                </select>
                <input type="file" class="form-control media-file-input" name="media_files[]" accept="${acceptTypes}">
                <input type="text" class="form-control media-url-input d-none" name="media_urls[]" placeholder="${urlPlaceholder}">
                <input type="hidden" class="media-type-hidden" name="media_types[]" value="image">
                <button type="button" class="btn btn-outline-secondary media-remove-btn">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        
        // Aggiungi il nuovo elemento
        mediaInputsContainer.appendChild(newMediaGroup);
        
        // Aggiungi gli event listeners al nuovo elemento
        initMediaInputGroup(newMediaGroup);
        
        // Aggiorna il contatore
        updateMediaCounter();
    });
    
    // Inizializza il primo gruppo di input
    initMediaInputGroup(mediaInputsContainer.querySelector('.media-input-group'));
    
    /**
     * Inizializza gli event listeners per un gruppo di input multimediale
     */
    function initMediaInputGroup(inputGroup) {
        const typeSelect = inputGroup.querySelector('.media-type-select');
        const fileInput = inputGroup.querySelector('.media-file-input');
        const urlInput = inputGroup.querySelector('.media-url-input');
        const typeHidden = inputGroup.querySelector('.media-type-hidden');
        const removeBtn = inputGroup.querySelector('.media-remove-btn');
        
        // Gestione cambio tipo di media
        typeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            typeHidden.value = selectedType;
            
            if (selectedType === 'youtube') {
                // Mostra input URL e nascondi input file
                fileInput.classList.add('d-none');
                urlInput.classList.remove('d-none');
                fileInput.value = ''; // Resetta il valore
                fileInput.removeAttribute('required');
                urlInput.setAttribute('required', 'required');
                
                // Aggiungi validazione in tempo reale per i link
                urlInput.addEventListener('input', function() {
                    validateMediaLink(this, packageType);
                });
            } else {
                // Mostra input file e nascondi URL
                fileInput.classList.remove('d-none');
                urlInput.classList.add('d-none');
                urlInput.value = ''; // Resetta il valore
                urlInput.removeAttribute('required');
                
                // Aggiorna l'attributo accept in base al tipo
                if (selectedType === 'video') {
                    fileInput.setAttribute('accept', 'video/*');
                } else {
                    fileInput.setAttribute('accept', 'image/*');
                }
            }
        });
        
        // Gestione rimozione elemento
        removeBtn.addEventListener('click', function() {
            // Non permettere di rimuovere l'ultimo elemento rimanente
            const currentInputs = mediaInputsContainer.querySelectorAll('.media-input-group');
            if (currentInputs.length > 1) {
                inputGroup.remove();
                updateMediaCounter();
            }
        });
    }
    
    /**
     * Aggiorna il contatore dei media
     */
    function updateMediaCounter() {
        const currentCount = mediaInputsContainer.querySelectorAll('.media-input-group').length;
        mediaCounter.textContent = `${currentCount}/${MAX_MEDIA}`;
        
        // Disabilita il pulsante "aggiungi" se raggiunto il limite
        addMediaBtn.disabled = currentCount >= MAX_MEDIA;
        
        // Abilita/disabilita i pulsanti rimuovi
        const removeButtons = mediaInputsContainer.querySelectorAll('.media-remove-btn');
        removeButtons.forEach(btn => {
            btn.disabled = removeButtons.length <= 1;
        });
    }
    
    /**
     * Valida un link media in base al tipo di pacchetto
     * Token A (foto): solo link a immagini valide
     * Token B (foto_video): anche YouTube e altri video
     */
    function validateMediaLink(urlInput, packageType) {
        const url = urlInput.value.trim();
        if (!url) {
            setLinkValidation(urlInput, true, '');
            return;
        }
        
        if (packageType === 'foto') {
            // Token A: solo link a immagini
            const validImageExtensions = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.svg', '.bmp'];
            const isValidImageLink = validImageExtensions.some(ext => 
                url.toLowerCase().includes(ext)
            );
            
            // Supporto per data URL delle immagini
            const isDataImageUrl = url.startsWith('data:image/');
            
            if (isValidImageLink || isDataImageUrl) {
                setLinkValidation(urlInput, true, 'Link immagine valido ✓');
            } else {
                setLinkValidation(urlInput, false, 'Solo link a immagini (.jpg, .png, .webp...) o data URL');
            }
        } else {
            // Token B: accetta tutto (immagini, YouTube, etc.)
            const isYoutube = url.includes('youtube.com') || url.includes('youtu.be');
            const isVimeo = url.includes('vimeo.com');
            const hasValidExtension = ['.jpg', '.jpeg', '.png', '.gif', '.webp', '.mp4', '.avi', '.mov'].some(ext => 
                url.toLowerCase().includes(ext)
            );
            
            if (isYoutube || isVimeo || hasValidExtension || url.startsWith('http')) {
                setLinkValidation(urlInput, true, 'Link valido ✓');
            } else {
                setLinkValidation(urlInput, false, 'Inserisci un link valido');
            }
        }
    }
    
    /**
     * Imposta lo stato di validazione visuale per un input link
     */
    function setLinkValidation(urlInput, isValid, message) {
        // Rimuovi classi esistenti
        urlInput.classList.remove('is-valid', 'is-invalid');
        
        // Rimuovi messaggi esistenti
        const existingFeedback = urlInput.parentNode.querySelector('.invalid-feedback, .valid-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }
        
        if (message) {
            // Aggiungi classe e messaggio
            urlInput.classList.add(isValid ? 'is-valid' : 'is-invalid');
            
            const feedbackDiv = document.createElement('div');
            feedbackDiv.className = isValid ? 'valid-feedback' : 'invalid-feedback';
            feedbackDiv.textContent = message;
            feedbackDiv.style.display = 'block';
            feedbackDiv.style.fontSize = '0.875rem';
            
            urlInput.parentNode.appendChild(feedbackDiv);
        }
    }
});
