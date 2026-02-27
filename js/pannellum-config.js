/**
 * Configurazione Viewer 360° - Pannellum
 * Nelle Tue Mani - Studio Onicotecnico
 */

document.addEventListener('DOMContentLoaded', function() {
    const panorama = document.getElementById('panorama-360');
    
    if (panorama && typeof pannellum !== 'undefined') {
        pannellum.viewer('panorama-360', {
            // Tipo di panorama (equirettangolare = foto standard 360°)
            type: 'equirectangular',
            
            // Percorso dell'immagine
            // Per sostituire: carica la tua foto 360° in assets/panorami/ e aggiorna il nome
            panorama: 'assets/panorami/negozio-360-esempio.jpg',
            
            // Titolo disabilitato - lascia l'immagine pulita
            // title: 'Nelle Tue Mani - Studio Onicotecnico',
            
            // Angolo di visualizzazione iniziale (in gradi)
            yaw: 0,
            pitch: 0,
            
            // Zoom iniziale (0 = zoom out massimo, 100 = zoom in)
            hfov: 100,
            
            // Limiti di zoom
            minHfov: 50,   // Massimo zoom in
            maxHfov: 120,  // Massimo zoom out
            
            // Abilita rotazione automatica (opzionale)
            autoLoad: true,
            autoRotate: -2, // Velocità rotazione (negativo = senso antiorario, 0 = disabilitato)
            autoRotateInactivityDelay: 3000, // Inizia a ruotare dopo 3 secondi di inattività
            
            // Controlli UI
            showControls: true,
            showZoomCtrl: true,
            showFullscreenCtrl: true,
            
            // Controlli personalizzati
            controls: {
                mouseZoom: true,      // Zoom con rotella mouse
                touchZoom: true,      // Zoom con pinch su mobile
                mouseDrag: true,      // Rotazione con drag mouse
                touchDrag: true,      // Rotazione con swipe mobile
                arrowKeys: true,      // Navigazione con frecce tastiera
            },
            
            // Messaggio di caricamento
            strings: {
                loadButtonLabel: 'Carica il Tour Virtuale',
                loadingLabel: 'Caricamento...',
                bylineLabel: 'di %s', 
                noPanoramaError: 'Nessun panorama specificato.',
                fileAccessError: 'Impossibile accedere al file %s.',
                malformedURLError: 'URL del panorama malformato.',
                iOS8WebGLError: 'Errore WebGL su iOS 8.',
                genericWebGLError: 'Il tuo browser non supporta WebGL necessario per visualizzare il panorama.',
                textureSizeError: 'Questo dispositivo non supporta texture di questa dimensione.',
                unknownError: 'Errore sconosciuto.'
            },
            
            // Hotspot (punti di interesse cliccabili) - OPZIONALE
            // Puoi aggiungere hotspot per evidenziare aree del negozio
            hotSpots: [
                {
                    pitch: -5,
                    yaw: 15,
                    type: 'info',
                    text: 'Area Ricostruzione Unghie',
                    // URL: 'servizi.html' // se vuoi linkare
                },
                {
                    pitch: -3,
                    yaw: -30,
                    type: 'info',
                    text: 'Postazione Nail Art'
                }
            ]
        });
    }
});

// Gestione errore se l'immagine non è presente
window.addEventListener('error', function(e) {
    if (e.target.src && e.target.src.includes('panorami')) {
        const panoramaDiv = document.getElementById('panorama-360');
        if (panoramaDiv) {
            panoramaDiv.innerHTML = `
                <div class="flex flex-col items-center justify-center h-full bg-gray-100 rounded-xl p-8">
                    <i class="fas fa-image text-6xl text-gray-300 mb-4"></i>
                    <p class="text-gray-500 text-center">
                        Immagine 360° non trovata.<br>
                        Carica la tua foto in <code>assets/panorami/</code>
                    </p>
                    <p class="text-gray-400 text-sm mt-2 text-center">
                        Leggi <code>README-360.md</code> per le istruzioni
                    </p>
                </div>
            `;
        }
    }
}, true);
