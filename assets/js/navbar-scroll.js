/**
 * NAVBAR SCROLL ANIMATION
 * Gestisce l'animazione dell'header durante lo scroll
 */

class NavbarScrollHandler {
    constructor() {
        this.navbar = document.querySelector('.navbar-modern');
        this.lastScrollTop = 0;
        this.scrollThreshold = 10;
        this.isScrolling = false;
        
        this.init();
    }
    
    init() {
        if (!this.navbar) return;
        
        // Throttled scroll event per performance
        window.addEventListener('scroll', this.throttleScroll.bind(this));
        
        // Imposta stato iniziale
        this.handleScroll();
    }
    
    throttleScroll() {
        if (!this.isScrolling) {
            window.requestAnimationFrame(() => {
                this.handleScroll();
                this.isScrolling = false;
            });
            this.isScrolling = true;
        }
    }
    
    handleScroll() {
        const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Aggiungi classe scrolled se siamo oltre il threshold
        if (currentScrollTop > this.scrollThreshold) {
            this.navbar.classList.add('navbar-scrolled');
        } else {
            this.navbar.classList.remove('navbar-scrolled');
        }
        
        // Nascondi/mostra navbar in base alla direzione scroll
        if (currentScrollTop > this.lastScrollTop && currentScrollTop > 100) {
            // Scrolling giù - nascondi navbar
            this.navbar.classList.add('navbar-hidden');
        } else {
            // Scrolling su - mostra navbar
            this.navbar.classList.remove('navbar-hidden');
        }
        
        this.lastScrollTop = currentScrollTop <= 0 ? 0 : currentScrollTop;
    }
}

// Inizializza quando DOM è pronto
document.addEventListener('DOMContentLoaded', () => {
    new NavbarScrollHandler();
});

// Inizializza anche se DOM è già caricato
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new NavbarScrollHandler();
    });
} else {
    new NavbarScrollHandler();
}
