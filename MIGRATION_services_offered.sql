-- =====================================================
-- MIGRATION: AGGIUNTA COLONNA services_offered
-- Data: 2024 
-- Descrizione: Aggiunge colonna JSON per servizi offerti
-- =====================================================

-- Verifica se la colonna esiste già
SELECT COLUMN_NAME 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'social_gioco_tris' 
AND TABLE_NAME = 'aziende' 
AND COLUMN_NAME = 'services_offered';

-- Se la query sopra non restituisce risultati, eseguire:
ALTER TABLE aziende 
ADD COLUMN services_offered JSON 
AFTER servizi 
COMMENT 'Servizi offerti dalla azienda (formato JSON)';

-- Verifica finale della struttura
DESCRIBE aziende;
