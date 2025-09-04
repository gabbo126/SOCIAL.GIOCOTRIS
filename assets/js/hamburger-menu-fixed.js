/**
 * HAMBURGER MENU JAVASCRIPT - COMPLETAMENTE RISCRITTO
 * Fix per tutti i bug mobile: single tap, apertura completa, no glitch
 * Versione pulita senza quadratini, solo testo
 */

(function() {
    'use strict';
    
    console.log('🍔 Hamburger Menu Fixed: Inizializzazione...');
    
    // State management
    let isMenuOpen = false;
    let isAnimating = false;
    
    // DOM elements
    let hamburgerBtn = null;
    let hamburgerOverlay = null;
    let hamburgerCloseBtn = null;
    let menuContainer = null;
    
    /**
     * INIZIALIZZAZIONE COMPLETA
     */
    function initHamburgerMenu() {
        // Get DOM elements
        hamburgerBtn = document.getElementById('hamburger-button');
        hamburgerOverlay = document.getElementById('hamburgerMenuOverlay');
        hamburgerCloseBtn = document.getElementById('hamburgerCloseBtn');
        menuContainer = document.querySelector('.hamburger-menu-container');
        
        if (!hamburgerBtn || !hamburgerOverlay || !menuContainer) {
            console.warn('⚠️ Hamburger Menu: Elementi DOM mancanti');
            return;
        }
        
        console.log('✅ Elementi DOM trovati, setup eventi...');
        
        // Setup event listeners
        setupEventListeners();
        
        // Assicurati che il menu sia chiuso all'inizio
        closeMenu();
        
        console.log('✅ Hamburger Menu Fixed: Inizializzazione completata');
    }
    
    /**
     * SETUP EVENT LISTENERS - SINGLE TAP
     */
    function setupEventListeners() {
        
        // HAMBURGER BUTTON - SINGLE TAP (no long press)
        hamburgerBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🍔 Hamburger click - single tap');
            toggleMenu();
        });
        
        // Prevenire event bubbling su touch
        hamburgerBtn.addEventListener('touchstart', function(e) {
            e.stopPropagation();
        });
        
        hamburgerBtn.addEventListener('touchend', function(e) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🍔 Hamburger touch end - single tap');
            toggleMenu();
        });
        
        // CLOSE BUTTON
        if (hamburgerCloseBtn) {
            hamburgerCloseBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('❌ Close button clicked');
                closeMenu();
            });
            
            hamburgerCloseBtn.addEventListener('touchend', function(e) {
                e.preventDefault();
                e.stopPropagation();
                console.log('❌ Close button touched');
                closeMenu();
            });
        }
        
        // OVERLAY BACKGROUND CLICK
        hamburgerOverlay.addEventListener('click', function(e) {
            if (e.target === hamburgerOverlay) {
                console.log('🍔 Overlay background clicked');
                closeMenu();
            }
        });
        
        // ESC KEY
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && isMenuOpen) {
                console.log('⌨️ ESC key pressed');
                closeMenu();
            }
        });
        
        // WINDOW RESIZE - chiudi menu su desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768 && isMenuOpen) {
                console.log('📱 Window resized to desktop, closing menu');
                closeMenu();
            }
        });
    }
    
    /**
     * TOGGLE MENU
     */
    function toggleMenu() {
        if (isAnimating) {
            console.log('⚠️ Menu animating, ignoring toggle');
            return;
        }
        
        if (isMenuOpen) {
            closeMenu();
        } else {
            openMenu();
        }
    }
    
    /**
     * OPEN MENU - SMOOTH ANIMATION
     */
    function openMenu() {
        if (isMenuOpen || isAnimating) return;
        
        console.log('🍔 Opening menu...');
        isAnimating = true;
        isMenuOpen = true;
        
        // Prevent body scroll
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.top = `-${window.scrollY}px`;
        document.body.style.width = '100%';
        
        // Show overlay with animation
        hamburgerOverlay.style.display = 'block';
        hamburgerOverlay.offsetHeight; // Force reflow
        hamburgerOverlay.classList.add('active');
        
        // Animation completed
        setTimeout(() => {
            isAnimating = false;
            
            // Focus su close button per accessibilità
            if (hamburgerCloseBtn) {
                hamburgerCloseBtn.focus();
            }
            
            console.log('✅ Menu opened successfully');
        }, 300);
    }
    
    /**
     * CLOSE MENU - SMOOTH ANIMATION
     */
    function closeMenu() {
        if (!isMenuOpen || isAnimating) return;
        
        console.log('❌ Closing menu...');
        isAnimating = true;
        isMenuOpen = false;
        
        // Remove active class
        hamburgerOverlay.classList.remove('active');
        
        // Wait for animation, then hide
        setTimeout(() => {
            hamburgerOverlay.style.display = 'none';
            
            // Restore body scroll
            const scrollY = document.body.style.top;
            document.body.style.position = '';
            document.body.style.top = '';
            document.body.style.overflow = '';
            document.body.style.width = '';
            
            if (scrollY) {
                window.scrollTo(0, parseInt(scrollY || '0') * -1);
            }
            
            isAnimating = false;
            
            // Return focus to hamburger button
            if (hamburgerBtn) {
                hamburgerBtn.focus();
            }
            
            console.log('✅ Menu closed successfully');
        }, 300);
    }
    
    /**
     * INITIALIZE ON DOM READY
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initHamburgerMenu);
    } else {
        initHamburgerMenu();
    }
    
    // Export functions for debugging
    window.HamburgerMenuFixed = {
        open: openMenu,
        close: closeMenu,
        toggle: toggleMenu,
        isOpen: () => isMenuOpen
    };
    
})();
