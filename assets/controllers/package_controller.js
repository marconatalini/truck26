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
    static targets = ['length', 'width', 'height', 'weight'];
    connect() {
        // this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
    }

    autofill(e) {
        const option = e.target.options[e.target.selectedIndex];
        const attr = option.dataset.default.split('x');

        this.lengthTarget.value = attr[0];
        this.widthTarget.value = attr[1];
        this.heightTarget.value = attr[2];
        this.weightTarget.value = attr[3];
    }
}
