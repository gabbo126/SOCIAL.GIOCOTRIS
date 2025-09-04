-- 🚨 FIX CRITICO: Creazione Tabella piani_media_limits MANCANTE
-- Questa tabella è richiesta dall'API getMediaLimits() per funzionare correttamente

-- Crea la tabella piani_media_limits
CREATE TABLE IF NOT EXISTS `piani_media_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `piano` varchar(50) NOT NULL,
  `max_media_totali` int(11) NOT NULL DEFAULT 3,
  `max_media_galleria` int(11) NOT NULL DEFAULT 3,
  `max_file_size_mb` int(11) NOT NULL DEFAULT 5,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `piano` (`piano`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Popola la tabella con i limiti per Piano Base e Pro
INSERT INTO `piani_media_limits` (`piano`, `max_media_totali`, `max_media_galleria`, `max_file_size_mb`) VALUES
('foto', 3, 3, 5),        -- Piano Base (foto): max 3 media totali, 3 galleria, 5MB per file
('foto_video', 5, 5, 10); -- Piano Pro (foto+video): max 5 media totali, 5 galleria, 10MB per file

-- Verifica dati inseriti
SELECT * FROM piani_media_limits;
