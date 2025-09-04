<?php
// File di configurazione principale

// Impostazioni per il report degli errori (utile in fase di sviluppo)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Definizione delle costanti del sito
define('SITE_NAME', 'Social Gioco Tris');
// URL per desktop (localhost)
define('BASE_URL_DESKTOP', 'http://localhost/SOCIAL.GIOCOTRIS');
// URL per mobile (IP di rete)
define('BASE_URL_MOBILE', 'http://172.20.10.2/SOCIAL.GIOCOTRIS');
// AUTO-detect mobile/desktop e usa URL appropriato
define('BASE_URL', (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/Mobile|Android|iPhone|iPad/', $_SERVER['HTTP_USER_AGENT'])) ? BASE_URL_MOBILE : BASE_URL_DESKTOP);

// Includi i file necessari
require_once 'includes/db.php';
require_once 'includes/functions.php';
