#!/bin/bash

echo "🚀 Avvio Server Nelle Tue Mani..."
echo ""

# Controlla se Docker è installato
if ! command -v docker &> /dev/null; then
    echo "❌ Docker non trovato!"
    echo ""
    echo "📥 Installa Docker Desktop:"
    echo "   Mac: https://docs.docker.com/desktop/install/mac-install/"
    echo "   Windows: https://docs.docker.com/desktop/install/windows-install/"
    echo ""
    echo "Dopo l'installazione, riavvia questo script."
    exit 1
fi

if ! command -v docker-compose &> /dev/null; then
    echo "❌ Docker Compose non trovato!"
    echo "Di solito è incluso in Docker Desktop."
    exit 1
fi

echo "✅ Docker trovato"
echo "🔄 Avvio container..."
echo ""

# Avvia i container
docker-compose up -d

# Attendi che MySQL sia pronto
echo "⏳ Attesa avvio database (20 secondi)..."
sleep 20

# Verifica se i container sono running
if docker ps | grep -q ntm_web; then
    echo ""
    echo "✅ SERVER AVVIATO CON SUCCESSO!"
    echo ""
    echo "🌐 SITO LANDING:     http://localhost:8080"
    echo "🔐 ADMIN DASHBOARD:  http://localhost:8080/admin/login.php"
    echo "🗄️  phpMyAdmin:       http://localhost:8081"
    echo ""
    echo "👤 Login Admin:"
    echo "   Username: admin"
    echo "   Password: changeme"
    echo ""
    echo "🛑 Per fermare: ./STOP_SERVER.sh"
    echo ""
    # Apri browser automaticamente
    if [[ "$OSTYPE" == "darwin"* ]]; then
        open "http://localhost:8080/admin/login.php"
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        xdg-open "http://localhost:8080/admin/login.php" 2>/dev/null || true
    fi
else
    echo "❌ Errore nell'avvio dei container"
    echo "Controlla con: docker-compose logs"
fi
