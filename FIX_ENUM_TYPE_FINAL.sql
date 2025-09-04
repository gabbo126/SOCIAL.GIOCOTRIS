-- ===================================================================
-- 🔧 FIX DEFINITIVO CAMPO TYPE - AGGIUNGE 'creazione' ALL'ENUM
-- ===================================================================
-- ✅ Risolve il bug: Campo type era ENUM('registrazione','modifica')
-- ✅ MySQL convertiva 'creazione' in stringa vuota perché non permesso
-- ✅ Aggiunge 'creazione' ai valori permessi dell'ENUM
-- ===================================================================

USE social_gioco_tris;

-- 🎯 BEFORE: ENUM('registrazione','modifica') 
-- 🎯 AFTER:  ENUM('registrazione','modifica','creazione')

ALTER TABLE tokens 
MODIFY COLUMN type ENUM('registrazione','modifica','creazione') 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci 
NOT NULL;

-- ===================================================================
-- ✅ VERIFICA POST-FIX
-- ===================================================================

-- Mostra la nuova definizione del campo
SELECT COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = DATABASE() 
AND TABLE_NAME = 'tokens' 
AND COLUMN_NAME = 'type';

-- Test inserimento 'creazione' (dovrebbe funzionare ora)
-- INSERT INTO tokens (token, type, data_scadenza, status, tipo_pacchetto) 
-- VALUES ('TEST_ENUM_FIX', 'creazione', NOW() + INTERVAL 24 HOUR, 'attivo', 'foto');

-- ===================================================================
-- 🚀 ESEGUIRE IN phpMyAdmin/MySQL
-- ===================================================================
-- 1. Apri phpMyAdmin
-- 2. Seleziona database 'social_gioco_tris'
-- 3. Vai su SQL
-- 4. Copia/incolla questa query
-- 5. Esegui
-- 6. Verifica che type ora accetti 'creazione'
-- ===================================================================
