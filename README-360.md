# Guida Foto 360° - Nelle Tue Mani

## 📸 Preparazione dell'immagine

### Requisiti tecnici:
- **Formato**: JPG o PNG
- **Risoluzione minima**: 4000x2000 pixel (2:1 ratio)
- **Risoluzione consigliata**: 6000x3000 o superiore per HDR
- **Aspect ratio**: 2:1 (equirettangolare)

### Esportazione da Insta360 X4:
1. Apri l'app Insta360 sul telefono
2. Seleziona la foto 360° scattata
3. Tocca il pulsante **Esporta**
4. Scegli:
   - **Formato**: JPG
   - **Qualità**: Alta o Ultra
   - **Tipo**: Equirettangolare (Flat)
   - **HDR**: Mantieni attivo se vuoi l'HDR
5. Salva sul telefono
6. Trasferisci sul computer via:
   - AirDrop (Mac)
   - Google Drive / Dropbox
   - Cavo USB

## 📁 Caricamento sul sito

### Step 1: Rinomina il file
```
negozio-360.jpg
```

### Step 2: Carica nella cartella corretta
```
assets/panorami/              ← NUOVA CARTELLA DEDICATA
    └── negozio-360.jpg     ← LA TUA FOTO QUI
```

**Nota**: Nella cartella `assets/panorami/` c'è già un file di esempio (`negozio-360-esempio.jpg`) 
che puoi usare come riferimento. Per sostituirlo con la tua foto:
1. Elimina o rinomina il file di esempio
2. Carica la tua foto come `negozio-360.jpg`

### Step 3: Verifica
1. Apri il sito nel browser
2. Vai alla sezione "Negozio"
3. Dovresti vedere il tour 360° interattivo

## 🎨 Personalizzazione

### Modificare i punti di interesse (hotspot):
Edita il file `js/pannellum-config.js`:

```javascript
hotSpots: [
    {
        pitch: -5,      // Altezza verticale (-90 a 90)
        yaw: 15,        // Direzione orizzontale (-180 a 180)
        type: 'info',
        text: 'Area Ricostruzione'
    },
    {
        pitch: -3,
        yaw: -30,
        type: 'info',
        text: 'Postazione Nail Art'
    }
]
```

### Modificare la posizione iniziale:
```javascript
// Angolo di visualizzazione iniziale
yaw: 0,      // -180 a 180 (dove guarda)
pitch: 0,    // -90 a 90 (su/giù)
hfov: 100,   // Zoom (50 = vicino, 120 = lontano)
```

### Disabilitare la rotazione automatica:
```javascript
autoRotate: 0,  // Invece di -2
```

## 📱 Test su dispositivi

### Desktop:
- **Mouse drag**: Ruota la visuale
- **Rotella**: Zoom in/out
- **Frecce**: Navigazione
- **Doppio click**: Zoom rapido

### Mobile/Tablet:
- **Swipe**: Ruota la visuale
- **Pinch**: Zoom in/out
- **Tocco**: Interagisce con hotspot

## 🔧 Risoluzione problemi

### "Immagine non trovata"
- Verifica che il file si chiami esattamente `negozio-360.jpg`
- Verifica che sia in `assets/images/`
- Controlla maiuscole/minuscole

### "Errore WebGL"
- Aggiorna il browser
- Prova Chrome o Firefox
- Verifica che WebGL sia abilitato

### Immagine distorta/stirata
- Assicurati che l'aspect ratio sia 2:1
- Verifica che sia in formato equirettangolare
- Non usare proiezioni cubiche o altre

### Caricamento lento
- Comprimi l'immagine (Photoshop: "Save for Web")
- Mantieni qualità 80-85%
- Dimensione file ideale: 2-5 MB

## 📞 Supporto

Per problemi tecnici:
- Documentazione Pannellum: https://pannellum.org/documentation/
- Controlla la console del browser (F12 → Console) per errori

---

**Nota**: L'immagine 360° viene caricata una sola volta e poi cachata dal browser. Se modifichi l'immagine, svuota la cache (Ctrl+Shift+R) per vedere le modifiche.
