// Funzioni JavaScript per la dashboard admin

// Gestione copiare negli appunti
function copyToClipboard(element) {
    var copyText = document.querySelector(element);
    copyText.select();
    document.execCommand("copy");
    
    // Feedback visivo
    var originalText = copyText.nextElementSibling.innerHTML;
    copyText.nextElementSibling.innerHTML = '<i class="bi bi-check-lg"></i> Copiato!';
    copyText.nextElementSibling.classList.add('btn-success');
    copyText.nextElementSibling.classList.remove('btn-outline-secondary');
    
    // Ripristina dopo 2 secondi
    setTimeout(function() {
        copyText.nextElementSibling.innerHTML = originalText;
        copyText.nextElementSibling.classList.remove('btn-success');
        copyText.nextElementSibling.classList.add('btn-outline-secondary');
    }, 2000);
}

// Inizializzazione della pagina
document.addEventListener('DOMContentLoaded', function() {
    // Aggiungi freccia per tornare in cima alla pagina
    var backToTopButton = document.createElement('button');
    backToTopButton.className = 'btn btn-primary btn-sm rounded-circle back-to-top';
    backToTopButton.innerHTML = '<i class="bi bi-arrow-up"></i>';
    backToTopButton.style.position = 'fixed';
    backToTopButton.style.bottom = '20px';
    backToTopButton.style.right = '20px';
    backToTopButton.style.display = 'none';
    backToTopButton.style.zIndex = '1000';
    document.body.appendChild(backToTopButton);
    
    // Mostra/nascondi freccia per tornare in cima
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTopButton.style.display = 'block';
        } else {
            backToTopButton.style.display = 'none';
        }
    });
    
    // Scroll in cima quando si clicca il pulsante
    backToTopButton.addEventListener('click', function() {
        window.scrollTo({top: 0, behavior: 'smooth'});
    });
    
    // Configurazione dei pulsanti "Vedi altri"
    document.querySelectorAll('.show-more-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            var additionalContainer = document.getElementById('additional-tokens-container-' + targetId);
            var moreContainer = document.getElementById('show-more-container-' + targetId);
            
            if (additionalContainer) {
                additionalContainer.style.display = 'block';
                moreContainer.style.display = 'none';
            }
        });
    });

    // Configurazione dei pulsanti "Mostra meno"
    document.querySelectorAll('.show-less-btn').forEach(function(button) {
        button.addEventListener('click', function() {
            var targetId = this.getAttribute('data-target');
            var additionalContainer = document.getElementById('additional-tokens-container-' + targetId);
            var moreContainer = document.getElementById('show-more-container-' + targetId);
            
            if (additionalContainer) {
                additionalContainer.style.display = 'none';
                moreContainer.style.display = 'flex';
                
                // Scorri fino all'inizio della sezione token
                document.getElementById('tokens-container-' + targetId).scrollIntoView({behavior: 'smooth'});
            }
        });
    });
});
