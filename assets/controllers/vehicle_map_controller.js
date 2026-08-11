import { Controller } from '@hotwired/stimulus';
import 'leaflet/dist/leaflet.min.css';
import 'leaflet';
import 'leaflet.icon.glyph'; // Carica il plugin



/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {

    connect() {
        // 1. Inizializzazione della mappa su un punto generico
        const map = L.map('map').setView([42.5, 12.5], 6);

        // 2. Aggiunta del layer OpenStreetMap
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // 3. Recupero dei dati dall'attributo data-places
        const mapElement = document.getElementById('map');
        const rawData = mapElement.getAttribute('data-places');
        const mode = mapElement.getAttribute('data-mode');

        let lastVehicle = '';

        try {
            const places = JSON.parse(rawData);

            // 4. Ciclo sui luoghi e creazione dei marker
            const markers = [];
            // Array che conterrà solo le coordinate per la linea
            let pathCoordinates = [];

            places.forEach(place => {
                // 1. Rimuoviamo le parentesi tonde
                // 2. Dividiamo la stringa alla virgola
                const [lat, lng] = place.coordinates.replace(/[()]/g, '').split(',').map(parseFloat);
                const marker = L.marker([lat,lng], {
                        icon: L.icon.glyph({
                            prefix: 'fa',      // Prefisso per FontAwesome (es: fa-solid)
                            glyph: 'truck',     // Nome dell'icona (es: fa-car)
                            color: 'white',   // Colore del testo/icona
                            bgPos: [0, 0],    // Opzionale
                            bgSize: [40, 62]  // Opzionale
                        })
                    })
                    .bindPopup(`<b>${place.name}</b><br>${place.plate}`)
                ;

                if ((place.plate !== lastVehicle && mode !== 'all') || mode === 'all') {
                    marker.addTo(map);
                    markers.push(marker);
                    // Salviamo la coordinata nell'array del percorso
                    pathCoordinates.push([lat, lng]);
                }

                lastVehicle = place.plate;
            });

            // Tracciamo la linea che unisce tutti i punti in ordine
            if (pathCoordinates.length > 1) {
                L.polyline(pathCoordinates, { color: 'orange' }).addTo(map);
            }

            // Opzionale: Adatta automaticamente la vista per mostrare tutti i marker
            const group = new L.featureGroup(markers);
            map.fitBounds(group.getBounds().pad(0.1));

        } catch (e) {
            console.error("Errore nel parsing dei dati JSON:", e);
        }
    }
}
