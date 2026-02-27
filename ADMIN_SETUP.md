# Area Riservata - Setup Admin

## 🔐 Caratteristiche di Sicurezza

- **Password Hashing**: Argon2id
- **CSRF Protection**: Token su tutti i form
- **Sessioni Sicure**: HTTPOnly, Secure, SameSite
- **Rate Limiting**: 5 tentativi ogni 15 minuti
- **Audit Logging**: Traccia tutte le azioni admin
- **SQL Injection Proof**: PDO Prepared Statements
- **XSS Protection**: Output escaping

## 📁 Struttura Files

```
/admin/
├── login.php              → Pagina login
├── dashboard.php          → Gestione prenotazioni
├── orari.php             → Gestione disponibilità
├── logout.php            → Logout
├── includes/
│   ├── auth.php          → Classe autenticazione
│   └── db.php            → Classe database
├── api/
│   ├── disponibilita.php → API orari (frontend)
│   └── salva-prenotazione.php → API salvataggio
└── assets/               → CSS/JS admin

/config.php               → Configurazione (protetta)
/database/schema.sql      → Struttura DB
```

## 🚀 Installazione su SiteGround

### 1. Database MySQL

1. Accedi al cPanel SiteGround
2. Vai su **MySQL Database Wizard**
3. Crea database: `nelletuemani`
4. Crea utente e password (salva questi dati!)
5. Assegna tutti i privilegi

### 2. Importa Schema

Metodo A - phpMyAdmin:
1. cPanel → phpMyAdmin
2. Seleziona database `nelletuemani`
3. Tab "Import" → Scegli file `database/schema.sql`
4. Clicca "Go"

Metodo B - SSH (se disponibile):
```bash
mysql -u username -p nelletuemani < database/schema.sql
```

### 3. Configurazione

Edita `config.php`:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'tuo_database_nome');      // Da SiteGround
define('DB_USER', 'tuo_username');           // Da SiteGround
define('DB_PASS', 'tua_password');           // Da SiteGround

// Cambia password admin (genera hash):
// Esegui: php -r "echo password_hash('nuova_password', PASSWORD_ARGON2ID);"
define('ADMIN_PASS_HASH', '$2y$10$...');    // Nuovo hash
```

### 4. Upload File

Carica TUTTO su SiteGround via FTP:
- Tutto il contenuto della cartella `/admin/`
- `config.php` nella root
- `database/` (opzionale, per backup)

### 5. Permessi File

Imposta permessi (via FTP o SSH):
```bash
chmod 644 config.php          # Proteggi config
chmod 755 admin/              # Eseguibile directory
chmod 644 admin/*.php
```

### 6. HTTPS Obbligatorio

In `config.php` assicurati:
```php
// Forza HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], true, 301);
    exit;
}
```

SiteGround fornisce certificati SSL gratuiti (Let's Encrypt).

## 🔑 Primo Accesso

1. Vai su: `https://tuo-sito.com/admin/login.php`
2. Username: `admin`
3. Password: `changeme` (cambiala subito!)

## 📋 Funzionalità Admin

### Dashboard Prenotazioni
- Visualizza tutte le prenotazioni
- Filtra per stato (nuova, confermata, completata, cancellata)
- Filtra per data
- Cambia stato prenotazione
- Aggiungi note interne
- Elimina prenotazione (libera slot automaticamente)

### Gestione Orari
- Visualizza calendario settimanale
- Genera slot automatici (30 giorni)
- Chiudi giorni specifici
- Clicca su slot per aprire/chiudere
- Visualizza slot prenotati (bloccati)

## 🌐 API Frontend

Le API permettono al sito di:
1. Leggere orari disponibili per una data
2. Salvare prenotazioni
3. Bloccare automaticamente slot prenotati

Endpoint:
- `GET /admin/api/disponibilita.php?data=YYYY-MM-DD`
- `POST /admin/api/salva-prenotazione.php`

## 🔒 Sicurezza Aggiuntiva

### Proteggi config.php
Aggiungi a `.htaccess` nella root:
```apache
<Files config.php>
    Order allow,deny
    Deny from all
</Files>
```

### Proteggi cartella admin (opzionale)
```apache
# .htaccess in /admin/
AuthType Basic
AuthName "Area Riservata"
AuthUserFile /path/to/.htpasswd
Require valid-user
```

### Rate Limiting avanzato
SiteGround supporta mod_security. Configura regole per prevenire brute force.

## 🐛 Troubleshooting

### "Errore di connessione al database"
- Verifica credenziali in `config.php`
- Controlla che l'utente MySQL abbia i privilegi
- Su SiteGround l'host è sempre `localhost`

### "Token non valido"
- Sessioni PHP non funzionanti
- Verifica che `session.save_path` sia scrivibile
- Controlla cookie nel browser

### "Accesso diretto non consentito"
- Tentativo di accesso diretto a file includes
- Normale comportamento di sicurezza

### Non carica orari disponibili
- Verifica che lo schema DB sia importato correttamente
- Controlla errori in Console JavaScript
- Verifica che `admin/api/disponibilita.php` sia raggiungibile

## 📧 Notifiche Email (Opzionale)

Per ricevere email quando arriva una prenotazione, modifica `admin/api/salva-prenotazione.php`:

```php
// Aggiungi dopo inserimento DB
$to = ADMIN_EMAIL;
$subject = "Nuova prenotazione - " . $nome;
$message = "Cliente: $nome\nTel: $telefono\nData: $data $ora\nServizio: $servizio";
mail($to, $subject, $message);
```

Su SiteGround la funzione `mail()` funziona automaticamente.

## 🔄 Backup

Backup database (cPanel):
1. phpMyAdmin → Esporta
2. Scarica file SQL

Oppure via SSH:
```bash
mysqldump -u username -p nelletuemani > backup_$(date +%Y%m%d).sql
```

## 📝 Note Importanti

1. **Prima password**: Cambia subito dopo primo login
2. **Sessioni**: Scadono dopo 1 ora di inattività
3. **Log**: Tutte le azioni sono tracciate in `admin_logs`
4. **Slot**: Quando una prenotazione viene eliminata, lo slot torna disponibile
5. **Date passate**: Non si possono creare prenotazioni per date passate

## 🆘 Supporto

Per problemi tecnici:
1. Controlla error log SiteGround (cPanel → Error Logs)
2. Verifica `admin_logs` nel database
3. Controlla Console Browser per errori JS

---

**Stack Tecnico:**
- PHP 7.4+ (compatibile con 8.x)
- MySQL 5.7+ / MariaDB 10.2+
- PDO MySQL
- Tailwind CSS (CDN)
- Vanilla JS
