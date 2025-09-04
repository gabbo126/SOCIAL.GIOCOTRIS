# Documentazione Integrazione PayPal - Social Gioco Tris

## Indice
1. [Panoramica del Sistema](#panoramica-del-sistema)
2. [Fase 1 - Definizione e Logica dei Piani](#fase-1---definizione-e-logica-dei-piani)
3. [Fase 2 - Integrazione Tecnica PayPal](#fase-2---integrazione-tecnica-paypal)
4. [Struttura Database](#struttura-database)
5. [API Endpoints](#api-endpoints)
6. [Flussi di Pagamento](#flussi-di-pagamento)
7. [Gestione Errori](#gestione-errori)
8. [Testing e Deployment](#testing-e-deployment)

---

## Panoramica del Sistema

### Stato Attuale
Il sistema **Social Gioco Tris** gestisce già due tipologie di pacchetto:
- **Piano Base** (`tipo_pacchetto: 'foto'`): Limite 3 media (foto + foto link)
- **Piano Pro** (`tipo_pacchetto: 'foto_video'`): Limite 5 media (foto + video + YouTube + foto link)

### Obiettivo Integrazione
Implementare sistema di abbonamenti ricorrenti con PayPal per monetizzare i piani esistenti e gestire modifiche azienda tramite pagamenti una tantum.

---

## Fase 1 - Definizione e Logica dei Piani

### 1.1 Piani di Abbonamento Ricorrenti

#### Piano Base
- **Codice interno**: `foto`
- **Nome commerciale**: Piano Base 🏷️
- **Caratteristiche**:
  - Logo aziendale
  - Massimo 3 media (foto + foto link)
  - Accesso limitato alle funzionalità
- **Prezzo**: €9,99/mese - €99,99/anno (20% sconto)
- **PayPal Plan ID**: `PLAN_BASE_MONTHLY` / `PLAN_BASE_YEARLY`

#### Piano Pro
- **Codice interno**: `foto_video`
- **Nome commerciale**: Piano Pro 🌟
- **Caratteristiche**:
  - Logo aziendale
  - Massimo 5 media (foto + video + YouTube + foto link)
  - Accesso completo alle funzionalità
  - Supporto prioritario
- **Prezzo**: €19,99/mese - €199,99/anno (17% sconto)
- **PayPal Plan ID**: `PLAN_PRO_MONTHLY` / `PLAN_PRO_YEARLY`

### 1.2 Associazione Piano-Registrazione

#### Workflow Registrazione
1. **Token Generation**: `genera_token.php` crea token con tipo pacchetto
2. **Registration Form**: `register_company.php` mostra UI basata sul piano
3. **Payment Redirect**: Dopo compilazione form → redirect a PayPal
4. **Subscription Creation**: Creazione abbonamento PayPal
5. **Company Creation**: Dopo conferma pagamento → salvataggio azienda
6. **Success Page**: Conferma e dettagli abbonamento

#### Codice Esempio - Modifica `processa_registrazione.php`
```php
// Dopo validazione dati, prima del salvataggio
if (!isset($_SESSION['paypal_subscription_confirmed'])) {
    // Redirect a PayPal per pagamento abbonamento
    $plan_id = ($tipo_pacchetto === 'foto') ? 'PLAN_BASE_MONTHLY' : 'PLAN_PRO_MONTHLY';
    header("Location: paypal/create_subscription.php?plan_id=$plan_id&token=$token");
    exit();
}
```

### 1.3 Gestione Upgrade/Downgrade

#### Upgrade: Base → Pro
- **Trigger**: Click su bottone "Passa al Piano Pro" in `modifica_azienda_token.php`
- **Processo**:
  1. Calcolo costo proporzionale
  2. Aggiornamento abbonamento PayPal
  3. Aggiornamento `tipo_pacchetto` nel database
  4. Mantenimento media esistenti (≤5)

#### Downgrade: Pro → Base
- **Trigger**: Richiesta utente via supporto o area admin
- **Processo**:
  1. Verifica media attuali
  2. Se >3 media → Marcatura media extra come "non supportati"
  3. **UI Warning**: "Hai 5 media, il Piano Base supporta solo 3. Rimuovi 2 media o torna al Piano Pro"
  4. Solo dopo rimozione → Downgrade abbonamento
  5. Aggiornamento database

---

## Fase 2 - Integrazione Tecnica PayPal

### 2.1 Account PayPal Business

#### Setup Iniziale
1. **Creazione Account**: [PayPal Developer](https://developer.paypal.com/)
2. **Tipo Account**: Business (necessario per API)
3. **App Creation**: Crea app per ottenere:
   - `Client ID`
   - `Client Secret`
4. **Webhook Setup**: URL per notifiche eventi

#### Credenziali Environment
```php
// config/paypal.php
define('PAYPAL_MODE', 'sandbox'); // 'live' per produzione
define('PAYPAL_CLIENT_ID', 'your_client_id');
define('PAYPAL_CLIENT_SECRET', 'your_client_secret');
define('PAYPAL_WEBHOOK_URL', BASE_URL . '/paypal/webhook.php');
```

### 2.2 Creazione Piani Ricorrenti

#### API Call - Create Plans
```php
// paypal/create_plans.php
function createSubscriptionPlan($planData) {
    $accessToken = getPayPalAccessToken();
    
    $planPayload = [
        "product_id" => $planData['product_id'],
        "name" => $planData['name'],
        "description" => $planData['description'],
        "status" => "ACTIVE",
        "billing_cycles" => [
            [
                "frequency" => [
                    "interval_unit" => "MONTH",
                    "interval_count" => 1
                ],
                "tenure_type" => "REGULAR",
                "sequence" => 1,
                "total_cycles" => 0, // Infinite
                "pricing_scheme" => [
                    "fixed_price" => [
                        "value" => $planData['price'],
                        "currency_code" => "EUR"
                    ]
                ]
            ]
        ],
        "payment_preferences" => [
            "auto_bill_outstanding" => true,
            "setup_fee" => [
                "value" => "0",
                "currency_code" => "EUR"
            ],
            "setup_fee_failure_action" => "CONTINUE",
            "payment_failure_threshold" => 3
        ]
    ];
    
    // Execute API call...
}
```

#### Piani da Creare
```php
$plans = [
    'PLAN_BASE_MONTHLY' => [
        'product_id' => 'PROD_SOCIAL_TRIS_BASE',
        'name' => 'Piano Base - Mensile',
        'description' => 'Piano Base con limite 3 media',
        'price' => '9.99'
    ],
    'PLAN_BASE_YEARLY' => [
        'product_id' => 'PROD_SOCIAL_TRIS_BASE',
        'name' => 'Piano Base - Annuale',
        'description' => 'Piano Base annuale con sconto 20%',
        'price' => '99.99'
    ],
    'PLAN_PRO_MONTHLY' => [
        'product_id' => 'PROD_SOCIAL_TRIS_PRO',
        'name' => 'Piano Pro - Mensile',
        'description' => 'Piano Pro completo con limite 5 media',
        'price' => '19.99'
    ],
    'PLAN_PRO_YEARLY' => [
        'product_id' => 'PROD_SOCIAL_TRIS_PRO',
        'name' => 'Piano Pro - Annuale',
        'description' => 'Piano Pro annuale con sconto 17%',
        'price' => '199.99'
    ]
];
```

### 2.3 Workflow Abbonamento

#### Create Subscription
```php
// paypal/create_subscription.php
function createSubscription($planId, $userToken) {
    $subscriptionPayload = [
        "plan_id" => $planId,
        "start_time" => gmdate('Y-m-d\TH:i:s\Z', strtotime('+1 minute')),
        "subscriber" => [
            "name" => [
                "given_name" => $_SESSION['company_data']['nome'],
                "surname" => ""
            ],
            "email_address" => $_SESSION['company_data']['email']
        ],
        "application_context" => [
            "brand_name" => "Social Gioco Tris",
            "locale" => "it-IT",
            "shipping_preference" => "NO_SHIPPING",
            "user_action" => "SUBSCRIBE_NOW",
            "payment_method" => [
                "payer_selected" => "PAYPAL",
                "payee_preferred" => "IMMEDIATE_PAYMENT_REQUIRED"
            ],
            "return_url" => BASE_URL . "/paypal/subscription_success.php?token=$userToken",
            "cancel_url" => BASE_URL . "/paypal/subscription_cancelled.php?token=$userToken"
        ]
    ];
    
    // Execute API call and return approval URL
}
```

### 2.4 Endpoint Webhook

#### Webhook Handler
```php
// paypal/webhook.php
header('Content-Type: application/json');

// Verify webhook signature
$headers = getallheaders();
$webhookId = 'YOUR_WEBHOOK_ID';
$requestBody = file_get_contents('php://input');

if (!verifyWebhookSignature($headers, $requestBody, $webhookId)) {
    http_response_code(401);
    exit('Unauthorized');
}

$event = json_decode($requestBody, true);

switch ($event['event_type']) {
    case 'BILLING.SUBSCRIPTION.ACTIVATED':
        handleSubscriptionActivated($event['resource']);
        break;
        
    case 'BILLING.SUBSCRIPTION.CANCELLED':
        handleSubscriptionCancelled($event['resource']);
        break;
        
    case 'PAYMENT.SALE.COMPLETED':
        handlePaymentCompleted($event['resource']);
        break;
        
    case 'BILLING.SUBSCRIPTION.SUSPENDED':
        handleSubscriptionSuspended($event['resource']);
        break;
}

function handleSubscriptionActivated($subscription) {
    global $conn;
    
    $subscriptionId = $subscription['id'];
    $customId = $subscription['custom_id']; // Il nostro token
    
    // Attiva l'azienda e salva subscription ID
    $sql = "UPDATE aziende SET 
            paypal_subscription_id = ?, 
            subscription_status = 'ACTIVE',
            subscription_start_date = NOW()
            WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $subscriptionId, $customId);
    $stmt->execute();
}
```

### 2.5 Pagamenti Una Tantum (Modifiche Azienda)

#### Workflow Modifica
1. **Modifica Dati**: Utente compila form in `modifica_azienda_token.php`
2. **Payment Required**: Calcolo costo basato su modifiche
3. **PayPal Order**: Creazione ordine una tantum
4. **Payment Execution**: Utente completa pagamento
5. **Data Update**: Salvataggio modifiche dopo conferma

#### Create Order per Modifiche
```php
// paypal/create_modification_order.php
function createModificationOrder($modificationData, $userToken) {
    $basePrice = 5.00; // Prezzo base modifica
    $mediaChanges = calculateMediaChanges($modificationData);
    $totalPrice = $basePrice + ($mediaChanges * 1.00);
    
    $orderPayload = [
        "intent" => "CAPTURE",
        "purchase_units" => [
            [
                "reference_id" => "MODIFY_" . $userToken,
                "amount" => [
                    "currency_code" => "EUR",
                    "value" => number_format($totalPrice, 2, '.', '')
                ],
                "description" => "Modifica dati azienda - Social Gioco Tris"
            ]
        ],
        "application_context" => [
            "return_url" => BASE_URL . "/paypal/modification_success.php?token=$userToken",
            "cancel_url" => BASE_URL . "/paypal/modification_cancelled.php?token=$userToken"
        ]
    ];
    
    // Execute API call
}
```

---

## Struttura Database

### 2.6 Tabelle da Aggiornare

#### Tabella `aziende` - Nuovi Campi
```sql
ALTER TABLE aziende ADD COLUMN paypal_subscription_id VARCHAR(50) NULL;
ALTER TABLE aziende ADD COLUMN subscription_status ENUM('PENDING', 'ACTIVE', 'CANCELLED', 'SUSPENDED') DEFAULT 'PENDING';
ALTER TABLE aziende ADD COLUMN subscription_start_date DATETIME NULL;
ALTER TABLE aziende ADD COLUMN subscription_end_date DATETIME NULL;
ALTER TABLE aziende ADD COLUMN last_payment_date DATETIME NULL;
ALTER TABLE aziende ADD COLUMN next_billing_date DATETIME NULL;
```

#### Nuova Tabella `payment_transactions`
```sql
CREATE TABLE payment_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT,
    transaction_type ENUM('SUBSCRIPTION', 'MODIFICATION', 'UPGRADE', 'DOWNGRADE'),
    paypal_order_id VARCHAR(50),
    paypal_transaction_id VARCHAR(50),
    amount DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'EUR',
    status ENUM('PENDING', 'COMPLETED', 'FAILED', 'REFUNDED'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (azienda_id) REFERENCES aziende(id)
);
```

#### Nuova Tabella `subscription_changes`
```sql
CREATE TABLE subscription_changes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    azienda_id INT,
    change_type ENUM('UPGRADE', 'DOWNGRADE', 'CANCEL'),
    old_plan VARCHAR(20),
    new_plan VARCHAR(20),
    effective_date DATETIME,
    paypal_subscription_id VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (azienda_id) REFERENCES aziende(id)
);
```

---

## API Endpoints

### 2.7 Nuovi File da Creare

#### Struttura Directory
```
paypal/
├── config.php                 # Configurazione PayPal
├── PayPalClient.php           # Client API PayPal
├── create_plans.php           # Creazione piani iniziale
├── create_subscription.php    # Crea abbonamento
├── create_order.php          # Crea ordine una tantum
├── webhook.php               # Handler webhook
├── subscription_success.php  # Successo abbonamento
├── subscription_cancelled.php # Abbonamento cancellato
├── modification_success.php  # Successo modifica
├── modification_cancelled.php # Modifica cancellata
├── manage_subscription.php   # Gestione abbonamento
└── utils.php                 # Funzioni utility
```

#### PayPal Client Base
```php
// paypal/PayPalClient.php
class PayPalClient {
    private $clientId;
    private $clientSecret;
    private $mode;
    private $baseUrl;
    
    public function __construct() {
        $this->clientId = PAYPAL_CLIENT_ID;
        $this->clientSecret = PAYPAL_CLIENT_SECRET;
        $this->mode = PAYPAL_MODE;
        $this->baseUrl = ($this->mode === 'sandbox') 
            ? 'https://api-m.sandbox.paypal.com' 
            : 'https://api-m.paypal.com';
    }
    
    public function getAccessToken() {
        // Implementazione ottenimento access token
    }
    
    public function makeRequest($endpoint, $method = 'GET', $data = null) {
        // Implementazione chiamate API generiche
    }
    
    public function createProduct($productData) {
        // Crea prodotto PayPal
    }
    
    public function createPlan($planData) {
        // Crea piano abbonamento
    }
    
    public function createSubscription($subscriptionData) {
        // Crea abbonamento
    }
    
    public function createOrder($orderData) {
        // Crea ordine una tantum
    }
}
```

---

## Flussi di Pagamento

### 2.8 Diagramma Flusso Registrazione
```
[Genera Token] → [Form Registrazione] → [Compila Dati] 
    ↓
[Redirect PayPal] → [Approva Pagamento] → [Webhook Attivazione]
    ↓
[Salva Azienda] → [Email Conferma] → [Dashboard Attiva]
```

### 2.9 Diagramma Flusso Modifica
```
[Login Token] → [Form Modifica] → [Compila Modifiche]
    ↓
[Calcola Costo] → [PayPal Order] → [Approva Pagamento]
    ↓
[Webhook Pagamento] → [Salva Modifiche] → [Email Conferma]
```

### 2.10 Diagramma Flusso Upgrade
```
[Click "Upgrade"] → [Conferma Scelta] → [PayPal Subscription Update]
    ↓
[Webhook Update] → [Aggiorna Database] → [Nuove Funzionalità Attive]
```

---

## Gestione Errori

### 2.11 Scenari di Errore

#### Pagamento Fallito
- **Webhook**: `PAYMENT.SALE.DENIED`
- **Azione**: Mantieni account sospeso, invia email retry
- **Retry Logic**: 3 tentativi automatici

#### Abbonamento Scaduto
- **Webhook**: `BILLING.SUBSCRIPTION.EXPIRED`
- **Azione**: Disattiva account, mantieni dati 30 giorni

#### Downgrade con Media Incompatibili
- **Check**: Controllo media count prima del downgrade
- **UI**: Warning chiaro con elenco media da rimuovere
- **Block**: Impedisci downgrade finché non conformi

#### Webhook non Ricevuto
- **Fallback**: Cron job giornaliero per sincronizzare stati
- **API Polling**: Controlla stato abbonamenti attivi

### 2.12 Logging e Monitoring
```php
// paypal/logger.php
function logPayPalEvent($type, $data, $status = 'INFO') {
    $logEntry = [
        'timestamp' => date('Y-m-d H:i:s'),
        'type' => $type,
        'status' => $status,
        'data' => json_encode($data),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    file_put_contents(
        'logs/paypal_' . date('Y-m-d') . '.log', 
        json_encode($logEntry) . "\n", 
        FILE_APPEND | LOCK_EX
    );
}
```

---

## Testing e Deployment

### 2.13 Testing Phases

#### Fase 1: Sandbox Testing
- [ ] Crea account sandbox PayPal
- [ ] Testa creazione piani
- [ ] Testa flusso abbonamento completo
- [ ] Testa webhook con eventi simulati
- [ ] Testa upgrade/downgrade
- [ ] Testa pagamenti una tantum

#### Fase 2: Staging Environment
- [ ] Configura ambiente staging
- [ ] Testa con dati reali (non pagamenti)
- [ ] Verifica email notifications
- [ ] Testa failure scenarios
- [ ] Load testing pagamenti

#### Fase 3: Production Deployment
- [ ] Switch a credenziali live
- [ ] Configura monitoraggio
- [ ] Deploy graduale (soft launch)
- [ ] Monitor primi pagamenti
- [ ] Full deployment

### 2.14 Checklist Pre-Deployment

#### Security
- [ ] HTTPS obbligatorio per tutti gli endpoint PayPal
- [ ] Validazione firma webhook implementata
- [ ] Sanitizzazione input PayPal
- [ ] Rate limiting su API endpoints
- [ ] Logging eventi sensibili

#### Business Logic
- [ ] Validazione piani vs funzionalità
- [ ] Gestione prorata upgrade/downgrade
- [ ] Business rules downgrade implementate
- [ ] Email templates configurate
- [ ] Support workflow definito

#### Technical
- [ ] Database backup strategy
- [ ] Cron job sync implementato
- [ ] Error handling robusto
- [ ] Performance optimization
- [ ] Monitoring e alerting

---

## Implementazione Priority

### 2.15 Sprint 1 (Setup Base)
1. Setup account PayPal Business
2. Configurazione ambiente sandbox
3. Creazione PayPalClient class
4. Implementazione creazione piani
5. Database schema update

### 2.16 Sprint 2 (Core Functionality)
1. Workflow abbonamento completo
2. Webhook handler base
3. Success/cancel pages
4. Integration con registrazione esistente
5. Testing sandbox completo

### 2.17 Sprint 3 (Advanced Features)
1. Pagamenti una tantum modifiche
2. Upgrade/downgrade logic
3. Error handling robusto
4. Admin panel integrations
5. Email notifications

### 2.18 Sprint 4 (Production Ready)
1. Security hardening
2. Performance optimization
3. Monitoring setup
4. Production deployment
5. Post-launch monitoring

---

## Conclusione

Questa documentazione fornisce una roadmap completa per l'integrazione PayPal nel sistema Social Gioco Tris. L'implementazione seguirà un approccio graduale, garantendo stabilità e funzionalità robuste.

**Prossimi Step Immediati:**
1. Setup account PayPal Business
2. Configurazione ambiente sandbox
3. Implementazione PayPalClient base
4. Testing workflow abbonamento

Per domande o chiarimenti su questa implementazione, consultare la documentazione PayPal ufficiale o contattare il team di sviluppo.
