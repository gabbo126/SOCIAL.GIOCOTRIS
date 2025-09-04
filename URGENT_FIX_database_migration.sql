-- ===================================================================
-- 🚨 MIGRATION URGENTE - RISOLUZIONE ERRORE CRITICO
-- Data: 2025-08-22
-- Problema: Colonna business_categories mancante
-- Impatto: Blocco totale registrazione/modifica aziende
-- ===================================================================

-- STEP 1: Backup consigliato prima dell'esecuzione
-- mysqldump -u root -p social_gioco_tris > backup_before_migration.sql

-- STEP 2: Aggiunta colonna business_categories
ALTER TABLE `aziende` 
ADD COLUMN `business_categories` TEXT NULL 
COMMENT 'Categorie business in formato JSON per sistema avanzato' 
AFTER `servizi`;

-- STEP 3: Verifica struttura aggiornata
DESCRIBE `aziende`;

-- STEP 4: Test inserimento di prova (opzionale)
-- INSERT INTO aziende (nome, business_categories) VALUES ('TEST', '["Ristorazione", "Bar"]');
-- SELECT nome, business_categories FROM aziende WHERE nome = 'TEST';
-- DELETE FROM aziende WHERE nome = 'TEST';

-- STEP 5: Verifica finale colonna
SELECT 
    COLUMN_NAME, 
    DATA_TYPE, 
    IS_NULLABLE, 
    COLUMN_DEFAULT,
    COLUMN_COMMENT
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'social_gioco_tris' 
  AND TABLE_NAME = 'aziende' 
  AND COLUMN_NAME = 'business_categories';

-- ===================================================================
-- RISULTATO ATTESO:
-- ✅ Colonna business_categories aggiunta come TEXT NULL
-- ✅ Sistema categorie avanzato completamente funzionante  
-- ✅ Registrazione e modifica aziende ripristinate
-- ===================================================================
