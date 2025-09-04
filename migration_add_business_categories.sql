-- ===================================================================
-- MIGRATION: Aggiunta colonna business_categories alla tabella aziende
-- Data: 2025-08-19
-- Scopo: Supportare il nuovo sistema categorie avanzato JSON
-- ===================================================================

-- Aggiunge la colonna business_categories come JSON/TEXT alla tabella aziende
ALTER TABLE `aziende` 
ADD COLUMN `business_categories` TEXT NULL 
COMMENT 'Categorie business in formato JSON per sistema avanzato' 
AFTER `servizi`;

-- Verifica che la colonna sia stata aggiunta correttamente
DESCRIBE `aziende`;

-- Query di test per verificare la struttura
SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT, COLUMN_COMMENT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'aziende' 
AND COLUMN_NAME = 'business_categories';

-- ===================================================================
-- MIGRATION COMPLETATA
-- 
-- ISTRUZIONI:
-- 1. Esegui questo file SQL in phpMyAdmin o MySQL Workbench
-- 2. Verifica che la colonna sia stata creata
-- 3. Testa registrazione e modifica aziende
-- ===================================================================
