<?php 
require_once 'config.php';
require_once 'includes/db.php';
require_once 'templates/header.php';

// Inclusione CSS per design moderno e professionale
echo '<link rel="stylesheet" href="assets/css/business-categories.css">';
echo '<link rel="stylesheet" href="assets/css/company-detail.css">';

$azienda_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($azienda_id <= 0) {
    echo "<div class='container py-5'><div class='alert alert-danger'>ID azienda non valido.</div></div>";
    require_once 'templates/footer.php';
    exit();
}

$stmt = $conn->prepare("SELECT * FROM aziende WHERE id = ?");
$stmt->bind_param("i", $azienda_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='container py-5'><div class='alert alert-warning'>Azienda non trovata.</div></div>";
    require_once 'templates/footer.php';
    exit();
}
$azienda = $result->fetch_assoc();

// Prepara un array con i file multimediali disponibili (immagini e video)
$media = [];
$media_fields = ['logo_url', 'foto1_url', 'foto2_url', 'foto3_url', 'video1_url', 'video2_url'];
foreach ($media_fields as $field) {
    if (!empty($azienda[$field]) && file_exists(__DIR__ . '/' . $azienda[$field])) {
        $file_path = $azienda[$field];
        $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        // Determina se è un'immagine o un video
        $is_video = in_array($file_extension, ['mp4', 'webm', 'ogg']);
        $is_youtube = (strpos($file_path, 'youtube.com') !== false || strpos($file_path, 'youtu.be') !== false);

        
        // Se è un embed di YouTube, estrae l'ID
        $embed_id = '';
        if ($is_youtube) {
            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/ ]{11})/', $file_path, $matches);
            if (isset($matches[1])) {
                $embed_id = $matches[1];
            }

        }
        
        $media[] = [
            'path' => $file_path,
            'type' => ($is_youtube ? 'youtube' : ($is_video ? 'video' : 'image')),
            'embed_id' => $embed_id
        ];
        
        // Limita a massimo 5 file multimediali
        if (count($media) >= 5) break;
    }
}
?>

<div class="container py-5">
    <!-- Header aziendale moderno e pulito -->
    <div class="company-detail-header mb-5">
        <div class="row align-items-center">
            <div class="col-lg-9">
                <nav aria-label="breadcrumb" class="mb-3">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Home</a></li>
                        <li class="breadcrumb-item"><a href="aziende.php" class="text-decoration-none">Aziende</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($azienda['nome']); ?></li>
                    </ol>
                </nav>
                <h1 class="company-title mb-2"><?php echo htmlspecialchars($azienda['nome']); ?></h1>
                
                <?php
                // IMPLEMENTAZIONE BUSINESS CATEGORIES - Layout Integrato
                $business_categories = [];
                if (!empty($azienda['business_categories'])) {
                    $categories_data = json_decode($azienda['business_categories'], true);
                    if (is_array($categories_data)) {
                        $business_categories = $categories_data;
                    }
                }
                
                if (!empty($business_categories)): ?>
                    <div class="company-categories mt-2 mb-0">
                        <?php 
                        // Mostra massimo 3 categorie principali per design pulito
                        $displayed_categories = array_slice($business_categories, 0, 3);
                        $remaining_count = count($business_categories) - 3;
                        
                        foreach ($displayed_categories as $index => $category): 
                            // Colori brand distintivi per tipologie
                            $badge_colors = [
                                'Ristorante' => 'bg-primary', 'Bar' => 'bg-success', 'Pizzeria' => 'bg-warning text-dark',
                                'Hotel' => 'bg-info', 'Negozio' => 'bg-secondary', 'Servizi' => 'bg-dark',
                                'Alimentari' => 'bg-success', 'Abbigliamento' => 'bg-purple', 'Bellezza' => 'bg-pink',
                                'Sport' => 'bg-orange', 'Tecnologia' => 'bg-cyan', 'Automotive' => 'bg-gray'
                            ];
                            
                            // Determina colore badge basato sulla categoria
                            $badge_class = 'bg-primary';
                            foreach ($badge_colors as $key => $color) {
                                if (stripos($category, $key) !== false) {
                                    $badge_class = $color;
                                    break;
                                }
                            }
                        ?>
                            <?php if ($index > 0): ?><span class="text-muted mx-2">•</span><?php endif; ?>
                            <span class="badge <?php echo $badge_class; ?> fs-6 fw-normal px-3 py-2 me-1">
                                <i class="bi bi-tag-fill me-1"></i><?php echo htmlspecialchars($category); ?>
                            </span>
                        <?php endforeach; ?>
                        
                        <?php if ($remaining_count > 0): ?>
                            <span class="text-muted mx-2">•</span>
                            <span class="badge bg-light text-dark fs-6 fw-normal px-3 py-2" title="<?php echo implode(', ', array_slice($business_categories, 3)); ?>">
                                <i class="bi bi-plus"></i><?php echo $remaining_count; ?> altre
                            </span>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-lg-3 text-lg-end">
                <a href="aziende.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Torna alla lista
                </a>
            </div>
        </div>
    </div>

    <div class="row g-6">
        <!-- Colonna Sinistra: Galleria Multimediale con Carosello -->
        <div class="col-lg-7">
            <?php if (!empty($media)): ?>
                <!-- CAROSELLO MODERNO -->
                <div class="media-gallery-modern mb-6">
                    <div id="customCarousel" class="custom-carousel-modern position-relative">
                        <!-- Contenitore principale delle slide -->
                        <div class="carousel-container" style="position: relative; height: 400px; background: #f8f9fa;">
                            <?php foreach ($media as $index => $item): ?>
                                <div class="carousel-slide" 
                                     data-slide-index="<?php echo $index; ?>" 
                                     style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: <?php echo $index === 0 ? '1' : '0'; ?>; transition: opacity 0.5s ease;">
                                    <?php if ($item['type'] === 'image'): ?>
                                        <img src="<?php echo htmlspecialchars($item['path']); ?>" 
                                             class="w-100 h-100" 
                                             style="object-fit: cover;" 
                                             alt="Immagine <?php echo $index + 1; ?>">
                                    <?php elseif ($item['type'] === 'video'): ?>
                                        <div class="h-100 d-flex align-items-center justify-content-center">
                                            <video class="carousel-video" 
                                                   style="max-width: 100%; max-height: 100%; width: auto; height: auto;" 
                                                   controls 
                                                   preload="metadata">
                                                <source src="<?php echo htmlspecialchars($item['path']); ?>" type="video/mp4">
                                                Il tuo browser non supporta i video HTML5.
                                            </video>
                                        </div>
                                    <?php elseif ($item['type'] === 'youtube'): ?>
                                        <div class="h-100 d-flex align-items-center justify-content-center">
                                            <iframe class="carousel-iframe" 
                                                    src="https://www.youtube.com/embed/<?php echo htmlspecialchars($item['embed_id']); ?>?rel=0&enablejsapi=1" 
                                                    style="width: 100%; height: 100%; max-width: 100%; max-height: 100%;" 
                                                    allowfullscreen 
                                                    title="Video YouTube"></iframe>
                                        </div>

                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Frecce di navigazione -->
                        <?php if (count($media) > 1): ?>
                            <button class="carousel-arrow carousel-arrow-prev" 
                                    style="position: absolute; top: 50%; left: 15px; transform: translateY(-50%); z-index: 10; background: rgba(0,0,0,0.5); color: white; border: none; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.3s ease;" 
                                    onmouseover="this.style.background='rgba(0,0,0,0.8)'" 
                                    onmouseout="this.style.background='rgba(0,0,0,0.5)'">
                                <i class="bi bi-chevron-left"></i>
                            </button>
                            <button class="carousel-arrow carousel-arrow-next" 
                                    style="position: absolute; top: 50%; right: 15px; transform: translateY(-50%); z-index: 10; background: rgba(0,0,0,0.5); color: white; border: none; border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.3s ease;" 
                                    onmouseover="this.style.background='rgba(0,0,0,0.8)'" 
                                    onmouseout="this.style.background='rgba(0,0,0,0.5)'">
                                <i class="bi bi-chevron-right"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Miniature di navigazione -->
                    <?php if (count($media) > 1): ?>
                        <div class="carousel-thumbnails mt-3 d-flex flex-wrap justify-content-center" id="carouselThumbnails">
                            <?php foreach ($media as $index => $item): ?>
                                <button type="button" 
                                        class="thumbnail-btn <?php echo $index === 0 ? 'active' : ''; ?>" 
                                        data-slide-index="<?php echo $index; ?>" 
                                        style="margin: 0 5px 5px 0; border: 3px solid <?php echo $index === 0 ? '#007bff' : '#ddd'; ?>; border-radius: 8px; padding: 0; background: none; cursor: pointer; transition: border-color 0.3s ease;">
                                    <div style="width: 60px; height: 45px; overflow: hidden; border-radius: 5px;">
                                        <?php if ($item['type'] === 'image'): ?>
                                            <img src="<?php echo htmlspecialchars($item['path']); ?>" 
                                                 class="w-100 h-100" 
                                                 style="object-fit: cover;" 
                                                 alt="Miniatura <?php echo $index + 1; ?>">
                                        <?php elseif ($item['type'] === 'video'): ?>
                                            <div class="d-flex justify-content-center align-items-center h-100 bg-light">
                                                <i class="bi bi-film text-dark"></i>
                                            </div>
                                        <?php elseif ($item['type'] === 'youtube'): ?>
                                            <div class="d-flex justify-content-center align-items-center h-100 bg-light">
                                                <i class="bi bi-youtube text-danger"></i>
                                            </div>
                                        <?php elseif ($item['type'] === 'vimeo'): ?>
                                            <div class="d-flex justify-content-center align-items-center h-100 bg-light">
                                                <i class="bi bi-vimeo text-info"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    // CAROSELLO COMPLETAMENTE NUOVO - VANILLA JS
                    const carousel = document.getElementById('customCarousel');
                    if (!carousel) return;
                    
                    const slides = carousel.querySelectorAll('.carousel-slide');
                    const thumbnails = document.querySelectorAll('.thumbnail-btn');
                    const prevBtn = carousel.querySelector('.carousel-arrow-prev');
                    const nextBtn = carousel.querySelector('.carousel-arrow-next');
                    
                    let currentSlide = 0;
                    const totalSlides = slides.length;
                    
                    // Funzione per mostrare una slide specifica
                    function showSlide(index) {
                        // Nascondi tutte le slide
                        slides.forEach(function(slide, i) {
                            slide.style.opacity = i === index ? '1' : '0';
                        });
                        
                        // Aggiorna miniature
                        thumbnails.forEach(function(thumb, i) {
                            thumb.classList.toggle('active', i === index);
                            thumb.style.borderColor = i === index ? '#007bff' : '#ddd';
                        });
                        
                        // Pausa tutti i video
                        pauseAllVideos();
                        
                        currentSlide = index;
                    }
                    
                    // Funzione per mettere in pausa tutti i video
                    function pauseAllVideos() {
                        // Pausa video HTML5
                        carousel.querySelectorAll('video').forEach(function(video) {
                            if (!video.paused) {
                                video.pause();
                            }
                        });
                        
                        // Pausa video YouTube
                        carousel.querySelectorAll('iframe[src*="youtube.com"]').forEach(function(iframe) {
                            try {
                                iframe.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                            } catch (e) {
                                console.log('Errore pausa YouTube:', e);
                            }
                        });
                    }
                    
                    // Funzione per andare alla slide precedente
                    function prevSlide() {
                        const newIndex = currentSlide === 0 ? totalSlides - 1 : currentSlide - 1;
                        showSlide(newIndex);
                    }
                    
                    // Funzione per andare alla slide successiva
                    function nextSlide() {
                        const newIndex = currentSlide === totalSlides - 1 ? 0 : currentSlide + 1;
                        showSlide(newIndex);
                    }
                    
                    // Event listeners per le frecce
                    if (prevBtn) {
                        prevBtn.addEventListener('click', prevSlide);
                    }
                    if (nextBtn) {
                        nextBtn.addEventListener('click', nextSlide);
                    }
                    
                    // Event listeners per le miniature
                    thumbnails.forEach(function(thumb, index) {
                        thumb.addEventListener('click', function() {
                            showSlide(index);
                        });
                    });
                    
                    // Supporto per i tasti freccia
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'ArrowLeft') {
                            prevSlide();
                        } else if (e.key === 'ArrowRight') {
                            nextSlide();
                        }
                    });
                    
                    // Impedisce autoplay dei video
                    carousel.querySelectorAll('video').forEach(function(video) {
                        video.removeAttribute('autoplay');
                        video.pause();
                    });
                    
                    // Inizializzazione
                    showSlide(0);
                    
                    // Debug console
                    console.log('Carosello personalizzato inizializzato:', totalSlides, 'slides');
                });
                </script>
            <?php else: ?>
                <!-- Placeholder moderno quando non ci sono media -->
                <div class="default-placeholder">
                    <i class="bi bi-image"></i>
                    <h4 class="mt-3 mb-2">Nessun contenuto multimediale</h4>
                    <p class="text-muted mb-0">L'azienda non ha ancora caricato immagini o video</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Colonna Destra: Informazioni -->
        <div class="col-lg-5">
            <div class="company-info-panel">
                <!-- Descrizione aziendale -->
                <?php if (!empty($azienda['descrizione'])): ?>
                <div class="info-section">
                    <h3 class="section-title">
                        <i class="bi bi-file-text section-icon"></i>
                        Descrizione
                    </h3>
                    <div class="section-content">
                        <p class="company-description-full"><?php echo nl2br(htmlspecialchars($azienda['descrizione'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Contatti -->
                <div class="info-section">
                    <h3 class="section-title">
                        <i class="bi bi-telephone section-icon"></i>
                        Contatti
                    </h3>
                    <div class="section-content">
                        <div class="contact-list">
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="bi bi-geo-alt-fill me-2 contact-icon"></i><?php echo htmlspecialchars($azienda['indirizzo']); ?></li>
                    <?php if (!empty($azienda['telefono'])): ?><li class="mb-2"><i class="bi bi-telephone-fill me-2 contact-icon"></i><?php echo htmlspecialchars($azienda['telefono']); ?></li><?php endif; ?>
                    <?php if (!empty($azienda['email'])): ?><li class="mb-2"><i class="bi bi-envelope-fill me-2 contact-icon"></i><a href="mailto:<?php echo htmlspecialchars($azienda['email']); ?>"><?php echo htmlspecialchars($azienda['email']); ?></a></li><?php endif; ?>
                    <?php if (!empty($azienda['sito_web'])): ?><li class="mb-2"><i class="bi bi-globe me-2 contact-icon"></i><a href="<?php echo htmlspecialchars($azienda['sito_web']); ?>" target="_blank" rel="noopener noreferrer">Visita il sito web</a></li><?php endif; ?>
                </ul>
            </div>

                    </div>
                </div>
                
                <!-- CATEGORIE ATTIVITÀ - SEMPRE VISIBILI -->
                <?php 
                // Carica categorie dal nuovo campo JSON business_categories
                $display_categories = [];
                if (!empty($azienda['business_categories'])) {
                    // Se esiste il nuovo campo JSON, usalo
                    $categories_data = json_decode($azienda['business_categories'], true);
                    if (is_array($categories_data)) {
                        $display_categories = $categories_data;
                    }
                } else {
                    // Fallback: combina tipo_struttura e servizi esistenti per compatibilità
                    if (!empty($azienda['tipo_struttura'])) {
                        $display_categories[] = trim($azienda['tipo_struttura']);
                    }
                    if (!empty($azienda['servizi'])) {
                        $servizi_array = array_map('trim', explode(',', $azienda['servizi']));
                        $display_categories = array_merge($display_categories, $servizi_array);
                    }
                }
                $display_categories = array_unique(array_filter($display_categories));
                ?>
                
                <?php if (!empty($display_categories)): ?>
                <div class="info-section">
                    <h3 class="section-title">
                        <i class="bi bi-tags section-icon"></i>
                        Categorie Attività
                    </h3>
                    <div class="section-content">
                        <div class="selected-categories-tags-readonly">
                            <?php foreach ($display_categories as $category): ?>
                                <span class="category-tag category-tag-readonly" title="<?php echo htmlspecialchars($category); ?>">
                                    <?php echo htmlspecialchars($category); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
        </div>
    </div>
</div>

<?php
$stmt->close();
$conn->close();

// Gallery-manager.js DISABILITATO - Usa il nuovo carosello custom
// echo '<script src="assets/js/gallery-manager.js"></script>';

require_once 'templates/footer.php';
?>
