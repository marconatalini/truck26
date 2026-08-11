import { Controller } from '@hotwired/stimulus';
import "fullcalendar";
import { Calendar } from '@fullcalendar/core';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin, {Draggable} from '@fullcalendar/interaction';
import bootstrapPlugin from '@fullcalendar/bootstrap';
import itLocale from '@fullcalendar/core/locales/it';

let currentVehicleId = 0;
let notBeforeModal;
export default class extends Controller {
    static targets = ['checkbox', 'vehicleChoice', 'draggable', 'viewUrl', 'dropUrl',
        'moveUrl', 'mapUrl', 'chartUrl', 'pdfUrl', 'calendarEl',
        'iframeMap', 'iframeChart', 'notBeforeModal'
    ]
    static values = {
        vehicle: Number
    }
    connect() {
        // this.element.textContent = 'Hello Stimulus! Edit me in assets/controllers/hello_controller.js';
        notBeforeModal = new bootstrap.Modal(this.notBeforeModalTarget);
    }

    notBeforeModalConnected(el) {
    }

    viewUrlTargetConnected(el) {
        // console.log(el.dataset.url);
    }

    draggableTargetConnected(el) {
        let drag = new Draggable(el);
    }

    calendarElTargetConnected(el) {
        let viewUrl = this.viewUrlTarget.dataset.url;
        let dropUrl = this.dropUrlTarget.dataset.url;
        let moveUrl = this.moveUrlTarget.dataset.url;
        let mapUrl = this.mapUrlTarget.dataset.url;
        let chartUrl = this.chartUrlTarget.dataset.url;
        let pdfUrl = this.pdfUrlTarget.dataset.url;
        let iframeMap = this.iframeMapTarget;
        let iframeChart = this.iframeChartTarget;

        let calendar = new Calendar(el, {
            plugins: [dayGridPlugin, interactionPlugin, bootstrapPlugin, listPlugin, timeGridPlugin],
            height: '95%',
            editable: true,
            droppable: true,
            customButtons: {
                mapBtn: {
                    text: 'Mappa',
                    click: function() {
                        let url = new URL(mapUrl);
                        url.pathname = url.pathname.replace(/\/[^\/]*$/, `/${currentVehicleId}`);
                        const panelElement = document.getElementById('offcanvasMap')
                        const bsOffcanvas = new bootstrap.Offcanvas(panelElement);
                        iframeMap.src = url;
                        bsOffcanvas.show();
                        // window.open(url, '_blank');
                    }
                },
                chartBtn: {
                    text: 'Carico',
                    click: function() {
                        let url = new URL(chartUrl);
                        url.pathname = url.pathname.replace(/\/[^\/]*$/, `/${currentVehicleId}`);
                        const panelElement = document.getElementById('offcanvasChart')
                        const bsOffcanvas = new bootstrap.Offcanvas(panelElement);
                        iframeChart.src = url;
                        bsOffcanvas.show();
                        // window.open(url, '_blank');
                    }
                },
                pdfBtn: {
                    text: 'Stampa',
                    click: function() {
                        let url = new URL(pdfUrl);
                        url.pathname = url.pathname.replace(/\/[^\/]*$/, `/${currentVehicleId}`);
                        window.open(url, '_blank');
                    }
                },


            },
            headerToolbar: {
                left: 'title',
                center: 'mapBtn chartBtn pdfBtn',
                right: 'timeGridTwoDay timeGridThreeDay prev,next',
            },
            initialView: 'timeGridDay',
            views: {
                timeGridTwoDay: {
                    type: 'timeGrid',
                    duration: { days: 2 },
                    buttonText: '2 GG'
                },
                timeGridThreeDay: {
                    type: 'timeGrid',
                    duration: { days: 3 },
                    buttonText: '3 GG'
                },
            },
            slotDuration: '00:05:00',
            slotMinTime: '07:00:00',
            slotMaxTime: '19:00:00',
            displayEventTime: false,
            defaultTimedEventDuration: '00:15',
            locales: [itLocale],
            // locale: 'it',
            timeZone: 'Europe/Rome',
            hiddenDays: [0, ], //hide sunday
            // eventSources: this.getActiveEventSource(),
            eventSources: this.getVehicleEventSource(),
            eventClick: function(info) {
                let url = new URL(viewUrl);
                url.pathname = url.pathname.replace(/\/[^\/]*$/, `/${info.event.id}`);
                window.open(url, '_self');
            },
            drop: function(info) {
                // info.draggedEl.remove(); //elimino l'evento HTML esterno
            },
            eventReceive: function (info) {
                let url = new URL(dropUrl);
                url.pathname = url.pathname.replace(/\/[^\/]*$/, `/${info.event.id}`);

                url.searchParams.append('vehicleId', currentVehicleId);
                url.searchParams.append('status', info.event.extendedProps.status);
                url.searchParams.append('start', info.event.start.toISOString());

                fetch(url.toString(), {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                }).then((function(i) {
                    if (i.status === 200) {
                        info.draggedEl.remove(); //elimino l'evento HTML esterno
                        return i.ok;
                    } else {
                        info.revert();
                        notBeforeModal.toggle();
                    }
                })).catch((function(reason) {
                    info.revert();
                    return reason;
                }))

            },
            /*eventDidMount: function(info) {
                let helpText = `${info.event.extendedProps.pickup_place} -> ${info.event.extendedProps.delivery_place}`;
                let tooltip = new bootstrap.Tooltip(info.el, {
                    title: helpText,
                    // title: info.event.title,
                    placement: 'top',
                    trigger: 'hover',
                    container: 'body',
                    delay: { "show": 3500, "hide": 50 }
                });
            },*/
            eventDrop: function(info) {
                let url = new URL(moveUrl);
                url.pathname = url.pathname.replace(/\/[^\/]*$/, `/${info.event.id}`);

                url.searchParams.append('start', info.event.start.toISOString());
                url.searchParams.append('type', info.event.extendedProps.type);

                fetch(url.toString(), {
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                }).then((function(i) {
                    if (i.status === 200) {
                        return i.ok;
                    } else {
                        info.revert();
                        notBeforeModal.toggle();
                    }
                })).catch((function(reason) {
                    info.revert();
                    return reason;
                }))
            }
        }).render()
        ;

    }

    getVehicleEventSource() {
        let eventSources = []
        currentVehicleId = this.vehicleChoiceTarget.value;
        const pickingUrl = this.vehicleChoiceTarget.options[this.vehicleChoiceTarget.selectedIndex].dataset.pickingUrl;
        const deliveringUrl = this.vehicleChoiceTarget.options[this.vehicleChoiceTarget.selectedIndex].dataset.deliveringUrl;
        eventSources.push({
            url: pickingUrl,
            failure: () => {alert('Errore! Non riesco a scaricare i ritiri.');},
            eventDataTransform: (m) => {
                m['title'] = `${m['pickupPlaceName']} < ${m['weight']}kg (${m['area']}mq) per ${m['deliveryPlaceName']}`
                if (m['express'] === true) {
                    m['color'] = '#1c4971';
                }
                if (m['picked'] === true) {
                    m['color'] = '#919191';
                }
            }
            // method: 'POST',
        })
        eventSources.push({
            url: deliveringUrl,
            failure: () => {alert('Errore! Non riesco a scaricare le consegne.');},
            eventDataTransform: (m) => {
                m['title'] = `${m['weight']}kg (${m['area']}mq) > ${m['deliveryPlaceName']}`
                m['color'] = '#629677'
                if (m['delivered'] === true) {
                    m['color'] = '#919191';
                }
            }
            // method: 'POST',
        })

        return eventSources;
    }

    getActiveEventSource() {
        let eventSources = []
        for (const inputEl of this.checkboxTargets) {
            if (inputEl.checked) {
                currentVehicleId = inputEl.value;
                eventSources.push({
                    url: inputEl.dataset.pickingUrl,
                    failure: () => {alert('Errore! Non riesco a scaricare i ritiri.');},
                    eventDataTransform: (m) => {
                        m['title'] = `${m['pickupPlaceName']} < ${m['weight']}kg (${m['area']}mq) per ${m['deliveryPlaceName']}`
                        if (m['express'] === true) {
                            m['color'] = '#1c4971';
                        }
                        if (m['picked'] === true) {
                            m['color'] = '#919191';
                        }
                    }
                    // method: 'POST',
                })
                eventSources.push({
                    url: inputEl.dataset.deliveringUrl,
                    failure: () => {alert('Errore! Non riesco a scaricare le consegne.');},
                    eventDataTransform: (m) => {
                        m['title'] = `${m['weight']}kg (${m['area']}mq) > ${m['deliveryPlaceName']}`
                        m['color'] = '#629677'
                        if (m['delivered'] === true) {
                            m['color'] = '#919191';
                        }
                    }
                    // method: 'POST',
                })

                break;
            }
        }
        return eventSources;
    }


    renderCalendar(el) {
        this.calendarElTargetConnected(this.calendarElTarget);
    }
}
