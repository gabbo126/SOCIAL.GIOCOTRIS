/**
 * Script per la gestione del carosello aziendale
 * Risolve i problemi di navigazione del carosello multimediale
 */
document.addEventListener('DOMContentLoaded', function() {
    // Attendiamo che Bootstrap sia completamente caricato
    window.addEventListener('load', function() {
        initCarouselAndThumbnails();
    });
    
    /**
     * Inizializza il carousel e gestisce le miniature
     */
    function initCarouselAndThumbnails() {
        const carousel = document.getElementById('aziendaCarousel');
        if (!carousel) return;
        
        // Otteniamo l'istanza del carousel che è già inizializzata dal markup HTML
        // Nota: NON creiamo una nuova istanza per evitare conflitti
        let carouselInstance;
        
        try {
            // Usiamo l'istanza esistente se disponibile
            carouselInstance = bootstrap.Carousel.getInstance(carousel);
            
            // Se non esiste ancora, la creiamo
            if (!carouselInstance) {
                carouselInstance = new bootstrap.Carousel(carousel, {
                    interval: false,  // No rotazione automatica
                    wrap: true        // Permette ciclo continuo
                });
            }
        } catch(e) {
            console.error('Errore nell\'inizializzare il carousel:', e);
            return;
        }
        
        // Configurazione eventi per le miniature
        const thumbnails = document.querySelectorAll('.thumbnail-nav');
        thumbnails.forEach((thumbnail) => {
            thumbnail.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-slide-index'), 10);
                if (!isNaN(index)) {
                    // Uso diretto dell'API di Bootstrap
                    carousel.setAttribute('data-bs-slide-to', index);
                    bootstrap.Carousel.getInstance(carousel).to(index);
                }
            });
        });
        
        // Ascolta gli eventi del carosello
        carousel.addEventListener('slid.bs.carousel', function() {
            // Trova l'indice attivo
            const activeIndex = [...carousel.querySelectorAll('.carousel-item')].findIndex(item => 
                item.classList.contains('active')
            );
            
            // Aggiorna l'evidenziazione delle miniature
            updateThumbnailsActive(activeIndex);
            
            // Gestione dei video (pausa quando non sono attivi)
            pauseAllVideos();
        });
    }
    
    /**
     * Aggiorna lo stato visivo delle miniature
     */
    function updateThumbnailsActive(activeIndex) {
        const thumbnails = document.querySelectorAll('.thumbnail-nav');
        thumbnails.forEach((thumbnail, index) => {
            if (parseInt(thumbnail.getAttribute('data-slide-index'), 10) === activeIndex) {
                thumbnail.classList.add('border-primary');
                thumbnail.classList.remove('border-secondary');
            } else {
                thumbnail.classList.remove('border-primary');
                thumbnail.classList.add('border-secondary');
            }
        });
    }
    
    /**
     * Mette in pausa tutti i video non attivi
     */
    function pauseAllVideos() {
        const videos = document.querySelectorAll('.carousel-item:not(.active) video');
        videos.forEach(video => {
            if (!video.paused) video.pause();
        });
    }
});

