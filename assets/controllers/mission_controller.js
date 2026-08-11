import { Controller } from '@hotwired/stimulus';

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
    static targets = ['modalText','modalConfirmBtn', 'prevUrl']

    connect() {
        // this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
    }

    modalConfirm(e) {
        let targetUrl = e.target.href;
        this.modalTextTarget.innerHTML = e.target.dataset.message;
        this.modalConfirmBtnTarget.addEventListener('click', function(evt) {
            window.location.href = targetUrl; // Reindirizza l'utente
        });
    }

    prevUrlTargetConnected(element) {
        const targetUrl = element.getAttribute('data-previous-url');

        // 2. Aggiungiamo uno stato fittizio alla cronologia
        // Questo "inganna" il browser facendogli credere che ci sia una pagina precedente interna
        history.pushState(null, null, window.location.href);

        // 3. Restiamo in ascolto del tasto "Indietro" (evento popstate)
        window.addEventListener('popstate',  function() {
            // Invece di tornare indietro, forziamo il redirect
            window.location.replace(targetUrl);
        });
    }
}
