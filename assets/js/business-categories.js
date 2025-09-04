/**
 * BUSINESS CATEGORIES SELECTOR - SISTEMA AVANZATO
 * Multi-selezione, ricerca, tag, UX ottimizzata
 */

class BusinessCategoriesSelector {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.container = document.getElementById(containerId);
        
        if (!this.container) {
            console.error(`BusinessCategoriesSelector: Container "${containerId}" non trovato`);
            return;
        }
        
        // Configurazione
        this.options = {
            placeholder: options.placeholder || 'Scegli categoria/e...',
            maxSelections: options.maxSelections || 3,
            allowCustom: options.allowCustom !== false,
            required: options.required || false,
            preselected: options.preselected || [],
            ...options
        };
        
        // State
        this.categories = {};
        this.selectedCategories = new Set(this.options.preselected);
        this.isOpen = false;
        this.filteredCategories = {};
        
        // Inizializzazione
        this.init();
    }
    
    /**
     * INIZIALIZZAZIONE
     */
    async init() {
        console.log('🏗️ BusinessCategoriesSelector: Inizializzazione...');
        
        try {
            // Carica categorie
            await this.loadCategories();
            
            // Crea interfaccia
            this.createInterface();
            
            // Setup eventi
            this.setupEventListeners();
            
            // Applica preselezionate
            this.applyPreselected();
            
            console.log('✅ BusinessCategoriesSelector: Inizializzazione completata');
        } catch (error) {
            console.error('❌ Errore inizializzazione:', error);
            this.showError('Errore caricamento categorie. Riprova più tardi.');
        }
    }
    
    /**
     * CARICA CATEGORIE DA JSON
     */
    async loadCategories() {
        try {
            const response = await fetch('assets/data/business-categories.json');
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const data = await response.json();
            this.categories = data.categories;
            this.filteredCategories = { ...this.categories };
            
            console.log(`📦 Caricate ${Object.keys(this.categories).length} macro-categorie`);
        } catch (error) {
            console.error('❌ Errore caricamento categorie:', error);
            throw error;
        }
    }
    
    /**
     * CREA INTERFACCIA HTML
     */
    createInterface() {
        const html = `
            <div class="business-categories-container">
                <label class="business-categories-label">
                    Categorie Attività
                    ${this.options.required ? '<span class="business-categories-required">*</span>' : ''}
                </label>
                
                <div class="categories-dropdown-container" id="${this.containerId}-dropdown">
                    <div class="categories-dropdown-trigger" id="${this.containerId}-trigger">
                        <span class="categories-dropdown-placeholder">${this.options.placeholder}</span>
                        <span class="categories-dropdown-arrow">▼</span>
                    </div>
                    
                    <div class="categories-dropdown-menu" id="${this.containerId}-menu">
                        <div class="categories-search-container">
                            <input type="text" class="categories-search-input" 
                                   placeholder="Cerca categoria..." 
                                   id="${this.containerId}-search">
                        </div>
                        
                        <div class="categories-list" id="${this.containerId}-list">
                            ${this.renderCategoriesList()}
                        </div>
                        
                        ${this.options.allowCustom ? `
                        <div class="categories-custom-container">
                            <input type="text" class="categories-custom-input" 
                                   placeholder="Aggiungi categoria personalizzata..." 
                                   id="${this.containerId}-custom">
                            <button type="button" class="categories-custom-add-btn" 
                                    id="${this.containerId}-custom-add">
                                + Aggiungi
                            </button>
                        </div>
                        ` : ''}
                    </div>
                </div>
                
                <div class="selected-categories-container">
                    <div class="selected-categories-title">Categorie Selezionate:</div>
                    <div class="selected-categories-tags" id="${this.containerId}-tags">
                        <div class="selected-categories-empty">Nessuna categoria selezionata</div>
                    </div>
                    
                    <div class="categories-actions">
                        <button type="button" class="categories-clear-btn" id="${this.containerId}-clear">
                            Cancella Tutto
                        </button>
                        <span class="categories-count" id="${this.containerId}-count">0 selezionate</span>
                    </div>
                </div>
                
                <!-- Input nascosto per form -->
                <input type="hidden" name="business_categories" id="${this.containerId}-input" value="">
            </div>
        `;
        
        this.container.innerHTML = html;
        
        // Cache elementi DOM
        this.elements = {
            dropdown: document.getElementById(`${this.containerId}-dropdown`),
            trigger: document.getElementById(`${this.containerId}-trigger`),
            menu: document.getElementById(`${this.containerId}-menu`),
            search: document.getElementById(`${this.containerId}-search`),
            list: document.getElementById(`${this.containerId}-list`),
            tags: document.getElementById(`${this.containerId}-tags`),
            count: document.getElementById(`${this.containerId}-count`),
            clear: document.getElementById(`${this.containerId}-clear`),
            input: document.getElementById(`${this.containerId}-input`),
            custom: document.getElementById(`${this.containerId}-custom`),
            customAdd: document.getElementById(`${this.containerId}-custom-add`)
        };
    }
    
    /**
     * RENDER LISTA CATEGORIE
     */
    renderCategoriesList() {
        let html = '';
        
        Object.entries(this.filteredCategories).forEach(([groupKey, group]) => {
            html += `
                <div class="categories-group" data-group="${groupKey}">
                    <div class="categories-group-header" data-group="${groupKey}">
                        <span class="categories-group-icon">${group.icon}</span>
                        <span class="categories-group-name">${group.name}</span>
                        <span class="categories-group-toggle">▼</span>
                    </div>
                    <div class="categories-group-items">
                        ${group.items.map(item => `
                            <div class="category-item ${this.selectedCategories.has(item) ? 'selected' : ''}" 
                                 data-category="${item}" data-group="${groupKey}">
                                <input type="checkbox" class="category-item-checkbox" 
                                       ${this.selectedCategories.has(item) ? 'checked' : ''}>
                                <span class="category-item-text">${item}</span>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        });
        
        return html;
    }
    
    /**
     * SETUP EVENT LISTENERS
     */
    setupEventListeners() {
        // Toggle dropdown
        this.elements.trigger.addEventListener('click', (e) => {
            e.preventDefault();
            this.toggleDropdown();
        });
        
        // Ricerca
        this.elements.search.addEventListener('input', (e) => {
            this.filterCategories(e.target.value);
        });
        
        // Chiudi dropdown cliccando fuori
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.closeDropdown();
            }
        });
        
        // Toggle gruppi
        this.elements.list.addEventListener('click', (e) => {
            if (e.target.closest('.categories-group-header')) {
                const group = e.target.closest('.categories-group');
                group.classList.toggle('collapsed');
            }
        });
        
        // Selezione categorie
        this.elements.list.addEventListener('change', (e) => {
            if (e.target.type === 'checkbox') {
                const item = e.target.closest('.category-item');
                const category = item.dataset.category;
                
                if (e.target.checked) {
                    this.selectCategory(category);
                } else {
                    this.deselectCategory(category);
                }
            }
        });
        
        // Categoria personalizzata
        if (this.options.allowCustom) {
            this.elements.custom.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.addCustomCategory();
                }
            });
            
            this.elements.customAdd.addEventListener('click', () => {
                this.addCustomCategory();
            });
            
            this.elements.custom.addEventListener('input', (e) => {
                this.elements.customAdd.disabled = !e.target.value.trim();
            });
        }
        
        // Cancella tutto
        this.elements.clear.addEventListener('click', () => {
            this.clearAllSelections();
        });
        
        // ESC per chiudere
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closeDropdown();
            }
        });
    }
    
    /**
     * TOGGLE DROPDOWN
     */
    toggleDropdown() {
        if (this.isOpen) {
            this.closeDropdown();
        } else {
            this.openDropdown();
        }
    }
    
    /**
     * APRI DROPDOWN
     */
    openDropdown() {
        this.isOpen = true;
        this.elements.dropdown.classList.add('active');
        this.elements.search.focus();
    }
    
    /**
     * CHIUDI DROPDOWN
     */
    closeDropdown() {
        this.isOpen = false;
        this.elements.dropdown.classList.remove('active');
        this.elements.search.value = '';
        this.filterCategories('');
    }
    
    /**
     * FILTRA CATEGORIE
     */
    filterCategories(query) {
        const searchQuery = query.toLowerCase().trim();
        
        if (!searchQuery) {
            this.filteredCategories = { ...this.categories };
        } else {
            this.filteredCategories = {};
            
            Object.entries(this.categories).forEach(([groupKey, group]) => {
                const filteredItems = group.items.filter(item => 
                    item.toLowerCase().includes(searchQuery)
                );
                
                if (filteredItems.length > 0 || group.name.toLowerCase().includes(searchQuery)) {
                    this.filteredCategories[groupKey] = {
                        ...group,
                        items: filteredItems.length > 0 ? filteredItems : group.items
                    };
                }
            });
        }
        
        this.elements.list.innerHTML = this.renderCategoriesList();
    }
    
    /**
     * SELEZIONA CATEGORIA
     */
    selectCategory(category) {
        if (this.selectedCategories.size >= this.options.maxSelections) {
            this.showLimitMessage();
            return false;
        }
        
        this.selectedCategories.add(category);
        this.updateInterface();
        this.updateHiddenInput();
        
        // Vibrazione su mobile
        if ('vibrate' in navigator) {
            navigator.vibrate(50);
        }
        
        return true;
    }
    
    /**
     * DESELEZIONA CATEGORIA
     */
    deselectCategory(category) {
        this.selectedCategories.delete(category);
        this.updateInterface();
        this.updateHiddenInput();
    }
    
    /**
     * AGGIUNGI CATEGORIA PERSONALIZZATA
     */
    addCustomCategory() {
        const customValue = this.elements.custom.value.trim();
        
        if (!customValue) return;
        
        if (this.selectedCategories.has(customValue)) {
            alert('Categoria già selezionata');
            return;
        }
        
        if (this.selectCategory(customValue)) {
            this.elements.custom.value = '';
            this.elements.customAdd.disabled = true;
        }
    }
    
    /**
     * CANCELLA TUTTE LE SELEZIONI
     */
    clearAllSelections() {
        if (this.selectedCategories.size === 0) return;
        
        if (confirm('Sei sicuro di voler cancellare tutte le categorie selezionate?')) {
            this.selectedCategories.clear();
            this.updateInterface();
            this.updateHiddenInput();
        }
    }
    
    /**
     * AGGIORNA INTERFACCIA
     */
    updateInterface() {
        // Aggiorna checkbox
        const checkboxes = this.elements.list.querySelectorAll('.category-item-checkbox');
        checkboxes.forEach(checkbox => {
            const item = checkbox.closest('.category-item');
            const category = item.dataset.category;
            const isSelected = this.selectedCategories.has(category);
            
            checkbox.checked = isSelected;
            item.classList.toggle('selected', isSelected);
        });
        
        // Aggiorna tag
        this.updateSelectedTags();
        
        // Aggiorna contatore
        this.updateCounter();
        
        // Aggiorna placeholder
        this.updatePlaceholder();
        
        // Aggiorna stato limite
        this.updateLimitState();
    }
    
    /**
     * AGGIORNA TAG SELEZIONATE
     */
    updateSelectedTags() {
        if (this.selectedCategories.size === 0) {
            this.elements.tags.innerHTML = '<div class="selected-categories-empty">Nessuna categoria selezionata</div>';
            return;
        }
        
        const tagsHtml = Array.from(this.selectedCategories).map(category => `
            <span class="category-tag" data-category="${category}">
                ${category}
                <button type="button" class="category-tag-remove" data-category="${category}">×</button>
            </span>
        `).join('');
        
        this.elements.tags.innerHTML = tagsHtml;
        
        // Event listeners per rimozione tag
        this.elements.tags.querySelectorAll('.category-tag-remove').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const category = btn.dataset.category;
                this.deselectCategory(category);
            });
        });
    }
    
    /**
     * AGGIORNA CONTATORE
     */
    updateCounter() {
        const count = this.selectedCategories.size;
        this.elements.count.textContent = `${count} selezionate`;
        
        this.elements.clear.style.display = count > 0 ? 'inline-block' : 'none';
    }
    
    /**
     * AGGIORNA PLACEHOLDER
     */
    updatePlaceholder() {
        const trigger = this.elements.trigger.querySelector('.categories-dropdown-placeholder');
        
        if (this.selectedCategories.size === 0) {
            trigger.textContent = this.options.placeholder;
            trigger.style.color = '#6c757d';
            trigger.style.fontStyle = 'italic';
        } else {
            const count = this.selectedCategories.size;
            trigger.textContent = `${count} categori${count === 1 ? 'a' : 'e'} selezionat${count === 1 ? 'a' : 'e'}`;
            trigger.style.color = '#495057';
            trigger.style.fontStyle = 'normal';
        }
    }
    
    /**
     * AGGIORNA INPUT NASCOSTO
     */
    updateHiddenInput() {
        const values = Array.from(this.selectedCategories);
        this.elements.input.value = JSON.stringify(values);
        
        // Dispatch evento change per compatibilità
        this.elements.input.dispatchEvent(new Event('change', { bubbles: true }));
    }
    
    /**
     * APPLICA PRESELEZIONATE
     */
    applyPreselected() {
        console.log(' Applicando categorie preselezionate...');
        
        try {
            // Leggi categorie preselezionate dal data attribute
            const preselectedData = this.container.dataset.preselected;
            
            if (preselectedData && preselectedData.trim() !== '') {
                console.log(' Dati preselezionati trovati:', preselectedData);
                
                // Parse JSON delle categorie preselezionate
                const preselectedCategories = JSON.parse(preselectedData);
                
                if (Array.isArray(preselectedCategories) && preselectedCategories.length > 0) {
                    console.log(` Caricando ${preselectedCategories.length} categorie preselezionate:`, preselectedCategories);
                    
                    // Carica ogni categoria preselezionata
                    preselectedCategories.forEach(category => {
                        if (category && category.trim() !== '') {
                            this.selectedCategories.add(category.trim());
                            console.log(` Categoria preselezionata caricata: ${category}`);
                        }
                    });
                    
                    console.log(` Totale categorie caricate: ${this.selectedCategories.size}`);
                } else {
                    console.log(' Array categorie preselezionate vuoto o non valido');
                }
            } else {
                console.log(' Nessuna categoria preselezionata trovata');
            }
        } catch (error) {
            console.error(' Errore parsing categorie preselezionate:', error);
            console.log(' Dati originali:', this.container.dataset.preselected);
        }
        
        // Aggiorna interfaccia e input hidden
        this.updateInterface();
        this.updateHiddenInput();
        
        console.log(' Categorie preselezionate applicate con successo');
    }
    
    /**
     * MOSTRA ERRORE
     */
    showError(message) {
        this.container.innerHTML = `
            <div class="categories-error">
                ❌ ${message}
            </div>
        `;
    }
    
    /**
     * API PUBBLICA
     */
    getSelectedCategories() {
        return Array.from(this.selectedCategories);
    }
    
    setSelectedCategories(categories = []) {
        this.selectedCategories = new Set(categories);
        this.updateInterface();
        this.updateHiddenInput();
    }
    
    addCategory(category) {
        return this.selectCategory(category);
    }
    
    removeCategory(category) {
        this.deselectCategory(category);
    }
    
    clearAll() {
        this.selectedCategories.clear();
        this.updateInterface();
        this.updateHiddenInput();
    }
    
    /**
     * MOSTRA MESSAGGIO LIMITE RAGGIUNTO
     */
    showLimitMessage() {
        // Rimuovi messaggi precedenti
        this.removeLimitMessage();
        
        // Crea messaggio professionale
        const messageEl = document.createElement('div');
        messageEl.className = 'categories-limit-message';
        messageEl.innerHTML = `
            <i class="bi bi-info-circle"></i>
            Limite massimo raggiunto (${this.options.maxSelections}/${this.options.maxSelections}). 
            Rimuovi una categoria per aggiungerne un'altra.
        `;
        
        // Inserisci dopo il trigger
        this.elements.trigger.insertAdjacentElement('afterend', messageEl);
        
        // Auto-rimozione dopo 4 secondi
        setTimeout(() => {
            this.removeLimitMessage();
        }, 4000);
        
        // Disabilita dropdown quando limite raggiunto
        this.updateLimitState();
    }
    
    /**
     * RIMUOVI MESSAGGIO LIMITE
     */
    removeLimitMessage() {
        const existingMessage = this.container.querySelector('.categories-limit-message');
        if (existingMessage) {
            existingMessage.remove();
        }
    }
    
    /**
     * AGGIORNA STATO UI BASATO SUL LIMITE
     */
    updateLimitState() {
        const isAtLimit = this.selectedCategories.size >= this.options.maxSelections;
        
        // Disabilita/abilita checkboxes non selezionate
        const checkboxes = this.elements.list.querySelectorAll('.category-item-checkbox');
        checkboxes.forEach(checkbox => {
            const item = checkbox.closest('.category-item');
            const category = item.dataset.category;
            const isSelected = this.selectedCategories.has(category);
            
            if (!isSelected && isAtLimit) {
                checkbox.disabled = true;
                item.classList.add('disabled');
            } else {
                checkbox.disabled = false;
                item.classList.remove('disabled');
            }
        });
        
        // Disabilita campo custom
        if (this.elements.custom) {
            this.elements.custom.disabled = isAtLimit;
            this.elements.customAdd.disabled = isAtLimit || !this.elements.custom.value.trim();
        }
    }
    
    destroy() {
        // Cleanup event listeners e DOM
        this.container.innerHTML = '';
    }
}

// Auto-inizializzazione
document.addEventListener('DOMContentLoaded', function() {
    // Cerca elementi con attributo data-business-categories
    const containers = document.querySelectorAll('[data-business-categories]');
    
    containers.forEach(container => {
        const options = {
            placeholder: container.dataset.placeholder || undefined,
            maxSelections: parseInt(container.dataset.maxSelections) || undefined,
            allowCustom: container.dataset.allowCustom !== 'false',
            required: container.dataset.required === 'true',
            preselected: container.dataset.preselected ? 
                          JSON.parse(container.dataset.preselected) : []
        };
        
        new BusinessCategoriesSelector(container.id, options);
    });
});

// Export per uso globale
window.BusinessCategoriesSelector = BusinessCategoriesSelector;
