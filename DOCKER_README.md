# 🐳 Avvio Rapido con Docker

**Non devi installare nulla a mano!** Docker fa tutto automaticamente.

## 📥 Step 1: Installa Docker Desktop

### Mac:
1. Vai su: https://docs.docker.com/desktop/install/mac-install/
2. Scarica e installa (dmg)
3. Apri Docker Desktop dall'Applications

### Windows:
1. Vai su: https://docs.docker.com/desktop/install/windows-install/
2. Scarica e installa
3. Riavvia il computer se richiesto

## 🚀 Step 2: Avvia il Server

### Mac/Linux:
1. Apri **Terminal**
2. Vai nella cartella del progetto:
   ```bash
   cd "/Users/lorenzopuccetti/Lavoro/Nelle Tue Mani/v.0"
   ```
3. Esegui:
   ```bash
   ./START_SERVER.sh
   ```

### Windows:
1. Apri **PowerShell** o **Git Bash**
2. Vai nella cartella del progetto
3. Esegui:
   ```bash
   docker-compose up -d
   ```

## 🌐 Step 3: Accedi al sito

Dopo l'avvio (circa 30 secondi), apri il browser:

| Servizio | URL |
|----------|-----|
| 🌐 **Sito Landing** | http://localhost:8080 |
| 🔐 **Admin Login** | http://localhost:8080/admin/login.php |
| 🗄️ **Database** | http://localhost:8081 (phpMyAdmin) |

**Credenziali Admin:**
- Username: `admin`
- Password: `changeme`

**Database (phpMyAdmin):**
- Username: `root`
- Password: `root`

## 🛑 Fermare il Server

### Mac/Linux:
```bash
./STOP_SERVER.sh
```

### Windows:
```bash
docker-compose down
```

## 🔄 Riavviare

Ogni volta che vuoi vedere il sito:
```bash
./START_SERVER.sh
```

I dati del database vengono conservati anche se fermi il server!

## ❌ Problemi?

### "Docker not found"
Docker non è installato o non è in PATH. Riavvia Docker Desktop.

### Porte già in uso
Se vedi errori sulle porte 8080 o 3306:
1. Ferma altri server attivi (XAMPP, MAMP, etc.)
2. Oppure modifica le porte in `docker-compose.yml`

### Container non parte
```bash
# Vedi log errori
docker-compose logs

# Ricrea tutto da zero
docker-compose down -v
docker-compose up -d
```

---

**Requisiti:** ~2GB RAM libera, Docker Desktop
