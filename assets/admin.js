import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
// import './styles/app.css';
import './styles/admin_mission.css';

function addCreateFilterTomselect() {
    const toms = document.querySelectorAll('.tomselected');
    toms.forEach(tom => {
        let control = tom.tomselect;
        if (true === control.settings.create) {
            if (null !== tom.getAttribute('data-ea-autocomplete-create-filter')) {
                control.settings.createFilter = function (input) {
                    const regex = new RegExp(tom.dataset.eaAutocompleteCreateFilter);
                    return regex.test(input)
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function(){
    addCreateFilterTomselect();
})

document.addEventListener('ea.collection.item-added', function() {
    addCreateFilterTomselect();
})

