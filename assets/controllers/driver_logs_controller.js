import { Controller } from '@hotwired/stimulus';
import "fullcalendar";
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin, {Draggable} from '@fullcalendar/interaction';
import bootstrapPlugin from '@fullcalendar/bootstrap';
import itLocale from '@fullcalendar/core/locales/it';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['calendarEl','viewUrl','eventsUrl']

    initialize() {
        // Called once when the controller is first instantiated (per element)

        // Here you can initialize variables, create scoped callables for event
        // listeners, instantiate external libraries, etc.
        // this._fooBar = this.fooBar.bind(this)
    }

    connect() {
        // Called every time the controller is connected to the DOM
        // (on page load, when it's added to the DOM, moved in the DOM, etc.)

        // Here you can add event listeners on the element or target elements,
        // add or remove classes, attributes, dispatch custom events, etc.
        // this.fooTarget.addEventListener('click', this._fooBar)
    }

    // Add custom controller actions here
    // fooBar() { this.fooTarget.classList.toggle(this.bazClass) }

    disconnect() {
        // Called anytime its element is disconnected from the DOM
        // (on page change, when it's removed from or moved in the DOM, etc.)

        // Here you should remove all event listeners added in "connect()" 
        // this.fooTarget.removeEventListener('click', this._fooBar)
    }

    calendarElTargetConnected(el) {
        let viewUrl = this.viewUrlTarget.dataset.url;

        let calendar = new Calendar(el, {
                plugins: [dayGridPlugin, interactionPlugin, bootstrapPlugin],
                // height: '95%',
                headerToolbar: {
                    left: 'title',
                    right: 'prev,next',
                },
                initialView: 'dayGridMonth',
                displayEventTime: false,
                locales: [itLocale],
                // locale: 'it',
                timeZone: 'Europe/Rome',
                hiddenDays: [0, ], //hide sunday
                // eventSources: this.getActiveEventSource(),
                eventSources: {
                    url: this.eventsUrlTarget.dataset.url,
                    failure: 'Errore caricamento dati',
                },
                eventClick: function(info) {
                    let url = new URL(viewUrl);
                    url.pathname = url.pathname.replace(/\/[^\/]*$/, `/${info.event.id}`);
                    window.open(url, '_self');
                },
            }).render()
        ;

    }
}
