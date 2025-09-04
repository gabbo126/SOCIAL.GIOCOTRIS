<?php
$page_title = 'Aggiungi Nuova Azienda';
require_once 'partials/admin_header.php';
?>

<h1 class="h2 mb-4">Aggiungi Nuova Azienda</h1>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">Dettagli Azienda</h5>
    </div>
    <div class="card-body">
        <form action="processa_aggiunta.php" method="POST" enctype="multipart/form-data">
            <div class="row">
                <!-- Dati Principali -->
                <div class="col-md-6 mb-3">
                    <label for="nome" class="form-label">Nome Azienda <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nome" name="nome" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="tipo_struttura" class="form-label">Tipo Struttura <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="tipo_struttura" name="tipo_struttura" required>
                </div>
                <div class="col-12 mb-3">
                    <label for="descrizione" class="form-label">Descrizione <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="descrizione" name="descrizione" rows="4" required></textarea>
                </div>

                <hr class="my-4">

                <!-- Contatti -->
                <h5 class="mb-3">Contatti</h5>
                <div class="col-md-6 mb-3">
                    <label for="indirizzo" class="form-label">Indirizzo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="indirizzo" name="indirizzo" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="telefono" class="form-label">Telefono</label>
                    <input type="tel" class="form-control" id="telefono" name="telefono">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="sito_web" class="form-label">Sito Web</label>
                    <input type="url" class="form-control" id="sito_web" name="sito_web">
                </div>

                <hr class="my-4">

                <!-- Servizi e Media -->
                <h5 class="mb-3">Servizi e Media</h5>
                <div class="col-12 mb-3">
                    <label for="servizi" class="form-label">Servizi (separati da virgola)</label>
                    <input type="text" class="form-control" id="servizi" name="servizi">
                </div>
                
                <!-- Logo separato -->
                <div class="col-md-6 mb-3">
                    <label for="logo" class="form-label">Logo</label>
                    <input class="form-control" type="file" id="logo" name="logo" accept="image/*">
                    <small class="form-text text-muted">Formato: JPG, PNG, WebP (max 5MB)</small>
                </div>
                
                <!-- Upload Multiplo Media -->
                <div class="col-12 mb-3">
                    <label class="form-label">Media (Foto e Video - Max 5 file)</label>
                    <input class="form-control" type="file" id="media_files" name="media_files[]" multiple accept=".jpg,.jpeg,.png,.webp,.mp4,.webm,.ogg">
                    <small class="form-text text-muted">Seleziona fino a 5 file: Immagini (JPG, PNG, WebP max 5MB) e Video (MP4, WebM, OGG max 100MB)</small>
                    
                    <!-- Preview Area -->
                    <div id="media_preview" class="mt-3 row g-2" style="display: none;">
                        <!-- I file selezionati appariranno qui -->
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-2"></i>Aggiungi Azienda</button>
                <a href="dashboard.php" class="btn btn-secondary">Annulla</a>
            </div>
        </form>
    </div>
</div>

<script>
// JavaScript per gestire upload multiplo con preview
document.addEventListener('DOMContentLoaded', function() {
    const mediaInput = document.getElementById('media_files');
    const previewContainer = document.getElementById('media_preview');
    const maxFiles = 5;
    
    mediaInput.addEventListener('change', function(e) {
        const files = Array.from(e.target.files);
        
        // Controlla limite file
        if (files.length > maxFiles) {
            alert(`Massimo ${maxFiles} file consentiti`);
            e.target.value = '';
            previewContainer.style.display = 'none';
            return;
        }
        
        // Pulisce preview precedente
        previewContainer.innerHTML = '';
        previewContainer.style.display = files.length > 0 ? 'block' : 'none';
        
        // Crea preview per ogni file
        files.forEach((file, index) => {
            const isImage = file.type.startsWith('image/');
            const isVideo = file.type.startsWith('video/');
            
            // Controllo formato
            const validExtensions = ['jpg', 'jpeg', 'png', 'webp', 'mp4', 'webm', 'ogg'];
            const fileExt = file.name.split('.').pop().toLowerCase();
            if (!validExtensions.includes(fileExt)) {
                alert(`Formato non supportato: ${file.name}`);
                e.target.value = '';
                previewContainer.style.display = 'none';
                return;
            }
            
            // Controllo dimensioni
            const maxSize = isVideo ? 100 * 1024 * 1024 : 5 * 1024 * 1024; // 100MB video, 5MB immagini
            if (file.size > maxSize) {
                const maxMB = maxSize / (1024 * 1024);
                alert(`File troppo grande: ${file.name} (max ${maxMB}MB)`);
                e.target.value = '';
                previewContainer.style.display = 'none';
                return;
            }
            
            // Crea card preview
            const col = document.createElement('div');
            col.className = 'col-6 col-md-4 col-lg-3';
            
            const card = document.createElement('div');
            card.className = 'card h-100';
            card.innerHTML = `
                <div class="card-body p-2 text-center">
                    <div class="preview-content mb-2" style="height: 80px; display: flex; align-items: center; justify-content: center;">
                        ${isImage ? `<img src="${URL.createObjectURL(file)}" alt="Preview" style="max-width: 100%; max-height: 100%; object-fit: cover;">` : ''}
                        ${isVideo ? `<video src="${URL.createObjectURL(file)}" style="max-width: 100%; max-height: 100%; object-fit: cover;" controls></video>` : ''}
                    </div>
                    <small class="text-muted d-block" style="font-size: 0.75rem;">${file.name}</small>
                    <small class="text-muted d-block" style="font-size: 0.7rem;">${(file.size / 1024 / 1024).toFixed(1)}MB</small>
                    <span class="badge ${isImage ? 'bg-primary' : 'bg-success'} mt-1">${isImage ? 'Immagine' : 'Video'}</span>
                </div>
            `;
            
            col.appendChild(card);
            previewContainer.appendChild(col);
        });
    });
});
</script>

<?php require_once 'partials/admin_footer.php'; ?>
