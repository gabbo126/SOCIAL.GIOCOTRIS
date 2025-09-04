/**
 * SISTEMA SERVIZI BUSINESS MODERNO
 * Gestione interattiva servizi offerti con UI/UX avanzata
 * Versione: 2.0 - Completa e ottimizzata
 */

class BusinessServicesSelector {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error(`Container ${containerId} non trovato`);
            return;
        }

        // Configurazione
        this.options = {
            maxSelections: options.maxSelections || 8,
            allowCustom: options.allowCustom !== false,
            dataUrl: options.dataUrl || 'assets/data/business-services.json',
            preselectedServices: options.preselectedServices || [],
            ...options
        };

        // Stato interno
        this.servicesData = null;
        this.selectedServices = [...this.options.preselectedServices];
        this.searchQuery = '';
        this.isDropdownOpen = false;

        // Elementi DOM
        this.searchInput = null;
        this.dropdown = null;
        this.tagsContainer = null;
        this.counter = null;
        this.limitMessage = null;
        this.customInput = null;

        // Inizializzazione
        this.init();
    }

    async init() {
        try {
            await this.loadServicesData();
            this.render();
            this.bindEvents();
            this.updateDisplay();
            
            console.log('BusinessServicesSelector inizializzato con successo');
        } catch (error) {
            console.error('Errore inizializzazione BusinessServicesSelector:', error);
            this.renderError();
        }
    }

    async loadServicesData() {
        try {
            const response = await fetch(this.options.dataUrl);
            if (!response.ok) throw new Error(`HTTP ${response.status}`);
            
            this.servicesData = await response.json();
            
            // Merge configurazione
            if (this.servicesData.settings) {
                this.options = { ...this.options, ...this.servicesData.settings };
            }
        } catch (error) {
            console.error('Errore caricamento dati servizi:', error);
            throw error;
        }
    }

    render() {
        this.container.innerHTML = `
            <div class="business-services-container">
                <!-- Header -->
                <div class="services-header">
                    <h6 class="services-title">
                        <i class="bi bi-gear-fill"></i>
                        Servizi Offerti
                    </h6>
                    <span class="services-counter" id="servicesCounter">0/${this.options.maxSelections}</span>
                </div>

                <!-- Ricerca e Dropdown -->
                <div class="services-dropdown-container">
                    <input type="text" 
                           class="form-control services-search-input" 
                           id="servicesSearch"
                           placeholder="${this.options.searchPlaceholder || 'Cerca servizi...'}"
                           autocomplete="off">
                    
                    <div class="services-dropdown hidden" id="servicesDropdown">
                        ${this.renderDropdownContent()}
                    </div>
                </div>

                <!-- Servizi Selezionati -->
                <div class="selected-services-container">
                    <div class="selected-services-tags" id="selectedServicesTags">
                        ${this.renderEmptyState()}
                    </div>
                    
                    <!-- Servizio Personalizzato -->
                    ${this.options.allowCustom ? `
                        <input type="text" 
                               class="form-control custom-service-input mt-3" 
                               id="customServiceInput"
                               placeholder="${this.options.customPlaceholder || 'Aggiungi servizio personalizzato...'}"
                               maxlength="50">
                    ` : ''}
                    
                    <!-- Messaggio Limite -->
                    <div class="services-limit-message hidden" id="servicesLimitMessage">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        Hai raggiunto il limite massimo di ${this.options.maxSelections} servizi
                    </div>
                </div>

                <!-- Campo Hidden per Form -->
                <input type="hidden" name="services_offered" id="servicesOfferedHidden" value="">
            </div>
        `;

        // Riferimenti DOM
        this.searchInput = document.getElementById('servicesSearch');
        this.dropdown = document.getElementById('servicesDropdown');
        this.tagsContainer = document.getElementById('selectedServicesTags');
        this.counter = document.getElementById('servicesCounter');
        this.limitMessage = document.getElementById('servicesLimitMessage');
        this.customInput = document.getElementById('customServiceInput');
        this.hiddenInput = document.getElementById('servicesOfferedHidden');
    }

    renderDropdownContent() {
        if (!this.servicesData?.categories) return '<div class="p-3 text-muted">Dati non disponibili</div>';

        return Object.entries(this.servicesData.categories).map(([categoryId, category]) => `
            <div class="service-category">
                <button type="button" class="category-header" data-category="${categoryId}">
                    <span>
                        <i class="${category.icon || 'bi-list'} category-icon"></i>
                        ${category.name}
                    </span>
                    <i class="bi bi-chevron-down category-toggle"></i>
                </button>
                <div class="category-services" data-category="${categoryId}">
                    ${category.services.map(service => `
                        <button type="button" 
                                class="service-option ${this.selectedServices.includes(service) ? 'selected' : ''}"
                                data-service="${service}">
                            ${service}
                        </button>
                    `).join('')}
                </div>
            </div>
        `).join('');
    }

    renderEmptyState() {
        return `
            <div class="services-empty-state">
                <i class="bi bi-gear services-empty-icon"></i>
                <p class="mb-0">Nessun servizio selezionato</p>
                <small class="text-muted">Inizia a digitare per cercare i servizi</small>
            </div>
        `;
    }

    renderError() {
        this.container.innerHTML = `
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i>
                Errore nel caricamento del sistema servizi. Riprova più tardi.
            </div>
        `;
    }

    bindEvents() {
        // Ricerca servizi
        this.searchInput.addEventListener('input', (e) => {
            this.searchQuery = e.target.value.toLowerCase();
            this.filterServices();
            this.showDropdown();
        });

        this.searchInput.addEventListener('focus', () => this.showDropdown());

        // Click fuori per chiudere dropdown
        document.addEventListener('click', (e) => {
            if (!this.container.contains(e.target)) {
                this.hideDropdown();
            }
        });

        // Toggle categorie
        this.container.addEventListener('click', (e) => {
            if (e.target.classList.contains('category-header')) {
                this.toggleCategory(e.target);
            }
        });

        // Selezione servizi
        this.container.addEventListener('click', (e) => {
            if (e.target.classList.contains('service-option') && !e.target.classList.contains('disabled')) {
                const service = e.target.dataset.service;
                if (this.selectedServices.includes(service)) {
                    this.removeService(service);
                } else {
                    this.addService(service);
                }
            }
        });

        // Rimozione servizi dai tag
        this.container.addEventListener('click', (e) => {
            if (e.target.classList.contains('service-tag-remove')) {
                const service = e.target.closest('.service-tag').dataset.service;
                this.removeService(service);
            }
        });

        // Servizio personalizzato
        if (this.customInput) {
            this.customInput.addEventListener('keypress', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.addCustomService();
                }
            });

            this.customInput.addEventListener('blur', () => {
                if (this.customInput.value.trim()) {
                    this.addCustomService();
                }
            });
        }
    }

    showDropdown() {
        this.dropdown.classList.remove('hidden');
        this.isDropdownOpen = true;
    }

    hideDropdown() {
        this.dropdown.classList.add('hidden');
        this.isDropdownOpen = false;
        this.searchInput.value = '';
        this.searchQuery = '';
        this.filterServices();
    }

    toggleCategory(categoryHeader) {
        const categoryId = categoryHeader.dataset.category;
        const categoryServices = this.container.querySelector(`[data-category="${categoryId}"].category-services`);
        
        categoryHeader.classList.toggle('collapsed');
        categoryServices.classList.toggle('collapsed');
    }

    filterServices() {
        if (!this.searchQuery) {
            // Mostra tutte le categorie
            this.container.querySelectorAll('.service-category').forEach(cat => {
                cat.style.display = 'block';
            });
            this.container.querySelectorAll('.service-option').forEach(opt => {
                opt.style.display = 'block';
            });
            return;
        }

        // Filtra per query di ricerca
        let hasResults = false;
        
        this.container.querySelectorAll('.service-category').forEach(category => {
            const options = category.querySelectorAll('.service-option');
            let categoryHasResults = false;

            options.forEach(option => {
                const service = option.dataset.service.toLowerCase();
                if (service.includes(this.searchQuery)) {
                    option.style.display = 'block';
                    categoryHasResults = true;
                    hasResults = true;
                } else {
                    option.style.display = 'none';
                }
            });

            category.style.display = categoryHasResults ? 'block' : 'none';
            
            // Espandi categoria se ha risultati
            if (categoryHasResults) {
                const header = category.querySelector('.category-header');
                const services = category.querySelector('.category-services');
                header.classList.remove('collapsed');
                services.classList.remove('collapsed');
            }
        });

        // Mostra messaggio se nessun risultato
        if (!hasResults) {
            this.dropdown.innerHTML = `
                <div class="p-3 text-muted text-center">
                    <i class="bi bi-search"></i><br>
                    ${this.options.noResultsText || 'Nessun servizio trovato'}
                </div>
            `;
        } else {
            this.dropdown.innerHTML = this.renderDropdownContent();
            this.updateServiceStates();
        }
    }

    addService(service) {
        if (this.selectedServices.length >= this.options.maxSelections) {
            this.showLimitMessage();
            return;
        }

        if (!this.selectedServices.includes(service)) {
            this.selectedServices.push(service);
            this.updateDisplay();
            this.hideDropdown();
        }
    }

    addCustomService() {
        const customService = this.customInput.value.trim();
        if (!customService) return;

        if (this.selectedServices.length >= this.options.maxSelections) {
            this.showLimitMessage();
            return;
        }

        if (!this.selectedServices.includes(customService)) {
            this.selectedServices.push(customService);
            this.customInput.value = '';
            this.updateDisplay();
        }
    }

    removeService(service) {
        const index = this.selectedServices.indexOf(service);
        if (index > -1) {
            this.selectedServices.splice(index, 1);
            this.updateDisplay();
            this.hideLimitMessage();
        }
    }

    updateDisplay() {
        this.updateTags();
        this.updateCounter();
        this.updateServiceStates();
        this.updateHiddenInput();
    }

    updateTags() {
        if (this.selectedServices.length === 0) {
            this.tagsContainer.innerHTML = this.renderEmptyState();
            return;
        }

        this.tagsContainer.innerHTML = this.selectedServices.map(service => `
            <div class="service-tag" data-service="${service}">
                <span>${service}</span>
                <button type="button" class="service-tag-remove" title="Rimuovi ${service}">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `).join('');
    }

    updateCounter() {
        const count = this.selectedServices.length;
        this.counter.textContent = `${count}/${this.options.maxSelections}`;
        
        if (count >= this.options.maxSelections) {
            this.counter.classList.add('limit-reached');
        } else {
            this.counter.classList.remove('limit-reached');
        }
    }

    updateServiceStates() {
        const isLimitReached = this.selectedServices.length >= this.options.maxSelections;
        
        this.container.querySelectorAll('.service-option').forEach(option => {
            const service = option.dataset.service;
            const isSelected = this.selectedServices.includes(service);
            
            option.classList.toggle('selected', isSelected);
            option.classList.toggle('disabled', !isSelected && isLimitReached);
        });

        // Disabilita input personalizzato se limite raggiunto
        if (this.customInput) {
            this.customInput.disabled = isLimitReached;
            if (isLimitReached) {
                this.customInput.placeholder = 'Limite massimo raggiunto';
            } else {
                this.customInput.placeholder = this.options.customPlaceholder || 'Aggiungi servizio personalizzato...';
            }
        }
    }

    updateHiddenInput() {
        if (this.hiddenInput) {
            this.hiddenInput.value = JSON.stringify(this.selectedServices);
        }
    }

    showLimitMessage() {
        this.limitMessage.classList.remove('hidden');
        setTimeout(() => {
            this.limitMessage.classList.add('hidden');
        }, 3000);
    }

    hideLimitMessage() {
        this.limitMessage.classList.add('hidden');
    }

    // API Pubblica
    getSelectedServices() {
        return [...this.selectedServices];
    }

    setSelectedServices(services) {
        this.selectedServices = [...services];
        this.updateDisplay();
    }

    addServiceProgrammatically(service) {
        this.addService(service);
    }

    removeServiceProgrammatically(service) {
        this.removeService(service);
    }

    clearAllServices() {
        this.selectedServices = [];
        this.updateDisplay();
    }

    isLimitReached() {
        return this.selectedServices.length >= this.options.maxSelections;
    }
}

// Inizializzazione automatica per elementi con data-business-services
document.addEventListener('DOMContentLoaded', function() {
    // Inizializza automaticamente tutti gli elementi con attributo data-business-services
    document.querySelectorAll('[data-business-services]').forEach(element => {
        const options = {
            maxSelections: parseInt(element.dataset.maxSelections) || 8,
            allowCustom: element.dataset.allowCustom !== 'false',
            preselectedServices: element.dataset.preselectedServices ? 
                JSON.parse(element.dataset.preselectedServices) : []
        };

        new BusinessServicesSelector(element.id, options);
    });
});

// Export per uso modular
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BusinessServicesSelector;
} else if (typeof window !== 'undefined') {
    window.BusinessServicesSelector = BusinessServicesSelector;
}
