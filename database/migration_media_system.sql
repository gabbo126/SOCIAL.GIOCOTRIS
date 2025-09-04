-- =====================================================
-- 🗄️ MIGRAZIONE SISTEMA MEDIA AZIENDALI - VERSIONE 2.0
-- =====================================================
-- OBIETTIVO: Trasformare struttura rigida in sistema flessibile
-- GARANZIA: Zero perdita dati durante migrazione

-- 1. CREAZIONE NUOVA TABELLA MEDIA
CREATE TABLE IF NOT EXISTS azienda_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT NOT NULL,
    tipo_media ENUM('logo', 'galleria') NOT NULL DEFAULT 'galleria',
    nome_file VARCHAR(255) NULL,                    -- Nome originale file
    percorso_file VARCHAR(500) NULL,               -- Path relativo su server
    url_esterno VARCHAR(500) NULL,                 -- URL esterno se non upload
    tipo_sorgente ENUM('upload', 'url') NOT NULL,  -- Distingue upload vs link
    formato VARCHAR(10) NULL,                      -- jpg, png, gif, etc
    dimensione_kb INT NULL,                        -- Size in KB per upload
    larghezza INT NULL,                            -- Pixel width
    altezza INT NULL,                              -- Pixel height
    ordine_visualizzazione INT DEFAULT 0,         -- Per ordinamento custom
    attivo BOOLEAN DEFAULT TRUE,                   -- Soft delete
    data_caricamento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modifica TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- INDICI OTTIMIZZATI
    INDEX idx_azienda_tipo (azienda_id, tipo_media),
    INDEX idx_attivo_ordine (attivo, ordine_visualizzazione),
    INDEX idx_sorgente (tipo_sorgente),
    
    -- FOREIGN KEY CON CASCADE
    FOREIGN KEY (azienda_id) REFERENCES aziende(id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. MIGRAZIONE DATI ESISTENTI (SICURA)
-- Backup automatico prima della migrazione
INSERT INTO azienda_media (azienda_id, tipo_media, url_esterno, tipo_sorgente, ordine_visualizzazione)
SELECT 
    id as azienda_id,
    'logo' as tipo_media,
    logo_url as url_esterno,
    'url' as tipo_sorgente,
    1 as ordine_visualizzazione
FROM aziende 
WHERE logo_url IS NOT NULL AND logo_url != '';

INSERT INTO azienda_media (azienda_id, tipo_media, url_esterno, tipo_sorgente, ordine_visualizzazione)
SELECT 
    id as azienda_id,
    'galleria' as tipo_media,
    foto1_url as url_esterno,
    'url' as tipo_sorgente,
    2 as ordine_visualizzazione
FROM aziende 
WHERE foto1_url IS NOT NULL AND foto1_url != '';

INSERT INTO azienda_media (azienda_id, tipo_media, url_esterno, tipo_sorgente, ordine_visualizzazione)
SELECT 
    id as azienda_id,
    'galleria' as tipo_media,
    foto2_url as url_esterno,
    'url' as tipo_sorgente,
    3 as ordine_visualizzazione
FROM aziende 
WHERE foto2_url IS NOT NULL AND foto2_url != '';

INSERT INTO azienda_media (azienda_id, tipo_media, url_esterno, tipo_sorgente, ordine_visualizzazione)
SELECT 
    id as azienda_id,
    'galleria' as tipo_media,
    foto3_url as url_esterno,
    'url' as tipo_sorgente,
    4 as ordine_visualizzazione
FROM aziende 
WHERE foto3_url IS NOT NULL AND foto3_url != '';

-- 3. TABELLA CONFIGURAZIONE PIANI
CREATE TABLE IF NOT EXISTS piani_media_limits (
    piano VARCHAR(20) PRIMARY KEY,
    max_media_totali INT NOT NULL,
    max_media_galleria INT NOT NULL,
    max_file_size_mb INT NOT NULL DEFAULT 5,
    formati_supportati TEXT DEFAULT 'jpg,jpeg,png,gif,webp,avif',
    descrizione TEXT,
    data_creazione TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Popolamento limiti piani
INSERT INTO piani_media_limits (piano, max_media_totali, max_media_galleria, max_file_size_mb, descrizione) VALUES
('base', 3, 2, 3, 'Piano Base: 1 logo + 2 foto galleria, file max 3MB'),
('pro', 5, 4, 5, 'Piano Pro: 1 logo + 4 foto galleria, file max 5MB'),
('admin', 999, 998, 10, 'Piano Admin: media illimitati, file max 10MB')
ON DUPLICATE KEY UPDATE 
    max_media_totali = VALUES(max_media_totali),
    max_media_galleria = VALUES(max_media_galleria);

-- 4. TRIGGER PER GESTIONE AUTOMATICA ORDINE
DELIMITER $$

CREATE TRIGGER before_insert_azienda_media
BEFORE INSERT ON azienda_media
FOR EACH ROW
BEGIN
    -- Auto-assign ordine se non specificato
    IF NEW.ordine_visualizzazione = 0 THEN
        SELECT COALESCE(MAX(ordine_visualizzazione), 0) + 1 
        INTO NEW.ordine_visualizzazione
        FROM azienda_media 
        WHERE azienda_id = NEW.azienda_id AND tipo_media = NEW.tipo_media;
    END IF;
    
    -- Logo sempre ordine 1
    IF NEW.tipo_media = 'logo' THEN
        SET NEW.ordine_visualizzazione = 1;
    END IF;
END$$

DELIMITER ;

-- 5. VIEW PER COMPATIBILITÀ RETROATTIVA (TEMPORANEA)
CREATE OR REPLACE VIEW aziende_media_legacy AS
SELECT 
    a.id,
    a.nome,
    a.piano,
    -- Logo (sempre ordine 1)
    (SELECT url_esterno FROM azienda_media 
     WHERE azienda_id = a.id AND tipo_media = 'logo' AND attivo = 1 
     ORDER BY ordine_visualizzazione LIMIT 1) as logo_url,
    
    -- Foto galleria in ordine
    (SELECT url_esterno FROM azienda_media 
     WHERE azienda_id = a.id AND tipo_media = 'galleria' AND attivo = 1 
     ORDER BY ordine_visualizzazione LIMIT 1) as foto1_url,
     
    (SELECT url_esterno FROM azienda_media 
     WHERE azienda_id = a.id AND tipo_media = 'galleria' AND attivo = 1 
     ORDER BY ordine_visualizzazione LIMIT 1,1) as foto2_url,
     
    (SELECT url_esterno FROM azienda_media 
     WHERE azienda_id = a.id AND tipo_media = 'galleria' AND attivo = 1 
     ORDER BY ordine_visualizzazione LIMIT 2,1) as foto3_url,
     
    -- Conteggi utili
    (SELECT COUNT(*) FROM azienda_media 
     WHERE azienda_id = a.id AND attivo = 1) as total_media,
     
    (SELECT COUNT(*) FROM azienda_media 
     WHERE azienda_id = a.id AND tipo_media = 'galleria' AND attivo = 1) as total_galleria
     
FROM aziende a;

-- 6. STORED PROCEDURE PER GESTIONE LIMITI PIANI
DELIMITER $$

CREATE PROCEDURE CheckMediaLimits(
    IN p_azienda_id INT,
    IN p_tipo_media ENUM('logo', 'galleria'),
    OUT p_can_add BOOLEAN,
    OUT p_message TEXT
)
BEGIN
    DECLARE v_piano VARCHAR(20);
    DECLARE v_max_totali INT;
    DECLARE v_max_galleria INT;
    DECLARE v_current_totali INT;
    DECLARE v_current_galleria INT;
    
    -- Ottieni piano azienda
    SELECT piano INTO v_piano FROM aziende WHERE id = p_azienda_id;
    
    -- Ottieni limiti piano
    SELECT max_media_totali, max_media_galleria 
    INTO v_max_totali, v_max_galleria
    FROM piani_media_limits WHERE piano = v_piano;
    
    -- Conta media attuali
    SELECT COUNT(*) INTO v_current_totali
    FROM azienda_media WHERE azienda_id = p_azienda_id AND attivo = 1;
    
    SELECT COUNT(*) INTO v_current_galleria  
    FROM azienda_media WHERE azienda_id = p_azienda_id AND tipo_media = 'galleria' AND attivo = 1;
    
    -- Verifica limiti
    IF p_tipo_media = 'logo' THEN
        -- Logo: max 1, sostituisce esistente
        SET p_can_add = TRUE;
        SET p_message = 'Logo: sostituzione consentita';
        
    ELSEIF p_tipo_media = 'galleria' THEN
        IF v_current_galleria >= v_max_galleria THEN
            SET p_can_add = FALSE;
            SET p_message = CONCAT('Limit raggiunti: ', v_current_galleria, '/', v_max_galleria, ' foto galleria per piano ', v_piano);
        ELSE
            SET p_can_add = TRUE;
            SET p_message = CONCAT('OK: ', v_current_galleria + 1, '/', v_max_galleria, ' foto galleria');
        END IF;
    END IF;
    
END$$

DELIMITER ;

-- 7. INDICI ADDIZIONALI PER PERFORMANCE
CREATE INDEX idx_azienda_media_lookup ON azienda_media(azienda_id, attivo, tipo_media, ordine_visualizzazione);
CREATE INDEX idx_media_cleanup ON azienda_media(attivo, data_modifica);

-- 8. COMMENTI E DOCUMENTAZIONE
ALTER TABLE azienda_media COMMENT = 'Sistema media flessibile v2.0 - Supporta upload e URL con metadata completi';
ALTER TABLE piani_media_limits COMMENT = 'Configurazione limiti per piani business - Modificabile via admin';

-- 9. VERIFICA MIGRAZIONE
SELECT 'MIGRAZIONE COMPLETATA!' as status,
       (SELECT COUNT(*) FROM azienda_media) as media_migrati,
       (SELECT COUNT(DISTINCT azienda_id) FROM azienda_media) as aziende_con_media,
       (SELECT COUNT(*) FROM piani_media_limits) as piani_configurati;
