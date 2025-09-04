/**
 * Search Bar Integrated Animation System
 * Gestisce l'apertura/chiusura animata della barra ricerca integrata sotto header
 * Solo per pagine diverse dalla home
 */

document.addEventListener('DOMContentLoaded', function() {
    // Elementi DOM
    const searchToggleBtn = document.getElementById('searchToggleBtn');
    const searchBarIntegrated = document.getElementById('searchBarIntegrated');
    const searchInput = document.getElementById('searchInputIntegrated');
    const searchClearBtn = document.getElementById('searchClearBtn');
    const searchForm = document.querySelector('.search-form-integrated');

    // Verifica che gli elementi esistano
    if (!searchToggleBtn || !searchBarIntegrated || !searchInput) {
        console.log('Search elements not found (probably on homepage)');
        return;
    }

    // Stato iniziale
    let isSearchOpen = false;
    let typingTimer = null;

    // Funzione per aprire barra ricerca
    function openSearch() {
        if (isSearchOpen) return;
        
        isSearchOpen = true;
        searchBarIntegrated.classList.add('active');
        searchToggleBtn.classList.add('active');
        document.body.classList.add('search-active');
        
        // Focus sull'input dopo l'animazione
        setTimeout(() => {
            searchInput.focus();
            startTypingEffect();
        }, 400);
    }

    // Funzione per chiudere barra ricerca
    function closeSearch() {
        if (!isSearchOpen) return;
        
        isSearchOpen = false;
        searchBarIntegrated.classList.remove('active');
        searchToggleBtn.classList.remove('active');
        document.body.classList.remove('search-active');
        
        // Reset input e nascondi clear button
        searchInput.value = '';
        searchClearBtn.style.display = 'none';
        
        // Stop typing effect
        if (typingTimer) {
            clearTimeout(typingTimer);
            typingTimer = null;
        }
        
        // Ripristina placeholder originale
        searchInput.placeholder = 'Cerca aziende, categorie, località...';
    }

    // Toggle barra ricerca
    function toggleSearch() {
        if (isSearchOpen) {
            closeSearch();
        } else {
            openSearch();
        }
    }

    // Effetto typing sul placeholder (più veloce e fluido)
    function startTypingEffect() {
        const phrases = [
            'Cerca aziende...',
            'Cerca per categoria...',
            'Cerca per città...',
            'Cerca servizi...',
            'Trova quello che cerchi...'
        ];
        
        let currentPhrase = 0;
        
        function cyclePlaceholder() {
            if (!isSearchOpen || searchInput.value.length > 0) return;
            
            const phrase = phrases[currentPhrase];
            let currentChar = 0;
            
            // Cancella placeholder attuale con animazione veloce
            function clearText() {
                if (searchInput.placeholder.length > 0) {
                    searchInput.placeholder = searchInput.placeholder.slice(0, -1);
                    typingTimer = setTimeout(clearText, 25);
                } else {
                    typeText();
                }
            }
            
            // Digita nuovo testo
            function typeText() {
                if (currentChar < phrase.length && isSearchOpen && searchInput.value.length === 0) {
                    searchInput.placeholder += phrase[currentChar];
                    currentChar++;
                    typingTimer = setTimeout(typeText, 40);
                } else {
                    currentPhrase = (currentPhrase + 1) % phrases.length;
                    typingTimer = setTimeout(cyclePlaceholder, 2500);
                }
            }
            
            clearText();
        }
        
        typingTimer = setTimeout(cyclePlaceholder, 800);
    }

    // Gestione input e clear button
    function handleInputChange() {
        const hasValue = searchInput.value.trim().length > 0;
        searchClearBtn.style.display = hasValue ? 'flex' : 'none';
        
        // Stop typing effect quando l'utente digita
        if (hasValue && typingTimer) {
            clearTimeout(typingTimer);
            typingTimer = null;
        } else if (!hasValue && isSearchOpen) {
            startTypingEffect();
        }
    }

    // Funzione per cancellare input
    function clearInput() {
        searchInput.value = '';
        searchClearBtn.style.display = 'none';
        searchInput.focus();
        startTypingEffect();
    }

    // Event Listeners
    searchToggleBtn.addEventListener('click', toggleSearch);
    
    if (searchClearBtn) {
        searchClearBtn.addEventListener('click', clearInput);
    }

    // Gestione input
    searchInput.addEventListener('input', handleInputChange);
    searchInput.addEventListener('focus', () => {
        if (typingTimer) {
            clearTimeout(typingTimer);
            typingTimer = null;
        }
    });

    // Chiudi con ESC
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && isSearchOpen) {
            closeSearch();
        }
    });

    // Submit con Enter
    searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && searchInput.value.trim()) {
            searchForm.submit();
        }
    });

    // Chiudi se si clicca fuori dalla barra quando è aperta
    document.addEventListener('click', (e) => {
        if (isSearchOpen && !searchBarIntegrated.contains(e.target) && !searchToggleBtn.contains(e.target)) {
            closeSearch();
        }
    });

    console.log('✅ Search Bar Integrated System initialized');
});
