/**
 * HAMBURGER MENU SOBRIO - MOBILE INTERACTION MANAGER
 * 
 * Gestisce le interazioni per hamburger menu minimalista che sostituisce
 * il quadrato brand-icon con design pulito in linea col sito.
 * 
 * Features:
 * - Menu overlay sobrio con navigazione essenziale
 * - Animazioni fluide e discrete
 * - Gestione focus e accessibility
 * - Responsive behavior automatico
 */

(function() {
    'use strict';
    
    // Elementi DOM
    let hamburgerBtn = null;
    let hamburgerOverlay = null;
    let hamburgerCloseBtn = null;
    
    // State management
    let isMenuOpen = false;
    let isAnimating = false;
    
    /**
     * INIZIALIZZAZIONE ELEMENTI
     */
    function initElements() {
        hamburgerBtn = document.querySelector('.brand-hamburger-btn');
        hamburgerOverlay = document.getElementById('hamburgerMenuOverlay');
        hamburgerCloseBtn = document.getElementById('hamburgerCloseBtn');
        
        if (hamburgerBtn) {
            console.log('🍔 Hamburger button found');
        } else {
            console.warn('🍔 Hamburger button not found');
        }
        
        if (hamburgerOverlay) {
            console.log('🍔 Menu overlay found');
        } else {
            console.warn('🍔 Menu overlay not found');
        }
    }
    
    /**
     * HAMBURGER MENU MANAGEMENT
     */
    
    // Apri hamburger menu
    function openHamburgerMenu() {
        if (isMenuOpen || !hamburgerOverlay) return;
        
        isMenuOpen = true;
        document.body.classList.add('menu-open');
        if (hamburgerBtn) hamburgerBtn.classList.add('active');
        hamburgerOverlay.classList.add('active');
        
        // Animazioni discrete per menu items
        const menuItems = hamburgerOverlay.querySelectorAll('.hamburger-nav-item, .category-item');
        menuItems.forEach((item, index) => {
            item.style.opacity = '0';
            item.style.transform = 'translateY(10px)';
            
            setTimeout(() => {
                item.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                item.style.opacity = '1';
                item.style.transform = 'translateY(0)';
            }, index * 30 + 100);
        });
        
        // Focus management
        setTimeout(() => {
            if (hamburgerCloseBtn) hamburgerCloseBtn.focus();
        }, 200);
        
        console.log('🍔 Menu opened');
    }
    
    // Chiudi hamburger menu
    function closeHamburgerMenu() {
        if (!isMenuOpen || !hamburgerOverlay) return;
        
        isMenuOpen = false;
        document.body.classList.remove('menu-open');
        if (hamburgerBtn) hamburgerBtn.classList.remove('active');
        hamburgerOverlay.classList.remove('active');
        
        // Reset animations
        const menuItems = hamburgerOverlay.querySelectorAll('.hamburger-nav-item, .category-item');
        menuItems.forEach(item => {
            item.style.opacity = '';
            item.style.transform = '';
            item.style.transition = '';
        });
        
        // Return focus
        if (hamburgerBtn) hamburgerBtn.focus();
        
        console.log('🍔 Menu closed');
    }
    
    // Toggle hamburger menu
    function toggleHamburgerMenu(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        
        if (isMenuOpen) {
            closeHamburgerMenu();
        } else {
            openHamburgerMenu();
        }
    }
    
    // Funzione per aprire il menu con feedback tattile
    function openMenu() {
        if (isAnimating) return;
        isAnimating = true;
        
        // Vibrazione tactile su dispositivi supportati
        if (navigator.vibrate) {
            navigator.vibrate(50); // 50ms di vibrazione leggera
        }
        
        hamburgerOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        
        // Animazione personalizzata container
        const menuContainer = hamburgerOverlay.querySelector('.hamburger-menu-container');
        menuContainer.style.transform = 'translateY(-100%)';
        requestAnimationFrame(() => {
            menuContainer.style.transition = 'transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1)';
            menuContainer.style.transform = 'translateY(0)';
        });
        
        // Focus management per accessibilità
        setTimeout(() => {
            if (hamburgerCloseBtn) {
                hamburgerCloseBtn.focus();
            }
            isAnimating = false;
        }, 400);
        
        console.log('🍔 Menu aperto con feedback tattile');
    }
    
    /**
     * EVENT LISTENERS
     */
    function bindEvents() {
        // Hamburger button click
        if (hamburgerBtn) {
            hamburgerBtn.addEventListener('click', toggleHamburgerMenu);
            console.log('🍔 Hamburger button listener attached');
        }
        
        // Close button click
        if (hamburgerCloseBtn) {
            hamburgerCloseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                closeHamburgerMenu();
            });
        }
        
        // ESC key to close
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isMenuOpen) {
                closeHamburgerMenu();
            }
        });
        
        // Click outside overlay to close
        if (hamburgerOverlay) {
            hamburgerOverlay.addEventListener('click', function(e) {
                if (e.target === hamburgerOverlay) {
                    closeHamburgerMenu();
                }
            });
            
            // Prevent clicks inside container from closing
            const menuContainer = hamburgerOverlay.querySelector('.hamburger-menu-container');
            if (menuContainer) {
                menuContainer.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
            }
        }
        
        // Responsive cleanup - close menu on desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992 && isMenuOpen) { // Bootstrap lg breakpoint
                closeHamburgerMenu();
            }
        });
        
        // Navigation links - close menu on click (mobile)
        const navLinks = document.querySelectorAll('.hamburger-nav-item, .category-item');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                // Add small delay for better UX
                setTimeout(() => {
                    closeHamburgerMenu();
                }, 150);
            });
        });
        
        // TOUCH EVENT LISTENERS PER MOBILE
        let touchStartY = 0;
        let touchMoved = false;
        
        hamburgerBtn.addEventListener('touchstart', function(e) {
            touchStartY = e.touches[0].clientY;
            touchMoved = false;
        });
        
        hamburgerBtn.addEventListener('touchmove', function(e) {
            if (Math.abs(e.touches[0].clientY - touchStartY) > 10) {
                touchMoved = true;
            }
        });
        
        hamburgerBtn.addEventListener('touchend', function(e) {
            if (!touchMoved) {
                toggleHamburgerMenu();
            }
        });
    }
    
    /**
     * PREVENT BODY SCROLL STYLES
     */
    function addStyles() {
        const style = document.createElement('style');
        style.textContent = `
            body.menu-open {
                overflow: hidden !important;
                position: fixed;
                width: 100%;
            }
            
            /* Smooth transitions */
            .brand-hamburger-btn .hamburger-line {
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            }
            
            .hamburger-menu-overlay {
                transition: all 0.4s ease;
            }
        `;
        document.head.appendChild(style);
    }
    
    /**
     * INITIALIZATION
     */
    function init() {
        initElements();
        bindEvents();
        addStyles();
        
        console.log('🍔 Hamburger Menu Sobrio initialized successfully');
    }
    
    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
    
})();

// HAMBURGER MENU JAVASCRIPT - MOBILE OPTIMIZED V2.0
// Gestione completa menu hamburger con touch, animazioni, accessibilità e feedback tattile
// Ottimizzato per dispositivi mobile con statistiche reali dal database

document.addEventListener('DOMContentLoaded', function() {
    console.log('🍔 Hamburger Menu V2.0: Inizializzazione con touch optimization...');
});
