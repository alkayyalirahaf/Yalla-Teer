import './bootstrap';

document.querySelectorAll('[data-dismiss-toast]').forEach((button) => {
    button.addEventListener('click', () => button.closest('.site-toast').remove());
});

document.querySelectorAll('[data-assistant-composer]').forEach((form) => {
    const options = form.querySelectorAll('[data-assistant-options]');
    const trip = form.elements.namedItem('trip');
    const returnDate = form.elements.namedItem('return');

    const update = () => {
        for (const section of options) {
            const active = section.dataset.assistantOptions === form.elements.mode.value;
            section.hidden = !active;
            section.open = active;
            section.querySelectorAll('input, select').forEach((field) => {
                field.disabled = !active || (field === returnDate && trip.value !== 'round_trip');
            });
        }
    };

    for (const [airportName, cityName] of [['from', 'origin_city_id'], ['to', 'destination_city_id']]) {
        const airport = form.elements.namedItem(airportName);
        const city = form.elements.namedItem(cityName);
        airport.addEventListener('change', () => { if (airport.value) city.value = ''; });
        city.addEventListener('change', () => { if (city.value) airport.value = ''; });
        // Old input from a rejected request may already contain both values.
        if (airport.value && city.value) city.value = '';
    }

    trip.addEventListener('change', update);
    form.querySelectorAll('[name="mode"]').forEach((input) => input.addEventListener('change', update));
    update();
});
import './date-constraints';

document.querySelectorAll('[data-airline-image]').forEach((image) => {
    const hideFailedImage = () => {
        (image.closest('[data-airline-photo]') ?? image).hidden = true;
    };

    image.addEventListener('error', hideFailedImage, { once: true });
    if (image.complete && !image.naturalWidth) hideFailedImage();
});

const menu = document.querySelector('.menu-toggle');
const navigation = document.querySelector('#primary-nav');
const compact = window.matchMedia('(max-width: 64rem)');

if (menu && navigation) {
    const update = (expanded) => {
        menu.hidden = !compact.matches;
        menu.setAttribute('aria-expanded', String(expanded));
        menu.setAttribute('aria-label', expanded ? menu.dataset.closeLabel : menu.dataset.openLabel);
        navigation.dataset.collapsed = String(compact.matches && !expanded);
    };

    menu.addEventListener('click', () => update(menu.getAttribute('aria-expanded') !== 'true'));
    menu.closest('.site-header').addEventListener('keydown', (event) => {
        if (event.key !== 'Escape' || menu.getAttribute('aria-expanded') !== 'true') return;
        update(false);
        menu.focus();
    });
    compact.addEventListener('change', () => update(false));
    update(false);
}

document.querySelectorAll('[data-compare]').forEach((form) => {
    const choices = [...form.elements].filter((field) => field.type === 'checkbox');
    const status = form.querySelector('[data-compare-status]');
    const submit = form.querySelector('button[type="submit"]');

    if (!status) return;

    const update = () => {
        const selected = choices.filter((choice) => choice.checked).length;
        submit.disabled = selected < 2;
        status.textContent = selected ? `${selected} selected (choose 2 or 3).` : 'Select 2 or 3 flights.';
        for (const choice of choices) {
            choice.disabled = !choice.checked && selected >= 3;
        }
    };

    choices.forEach((choice) => choice.addEventListener('change', update));
    form.addEventListener('submit', (event) => {
        if (choices.filter((choice) => choice.checked).length < 2) {
            event.preventDefault();
            status.textContent = 'Select at least two flights to compare.';
        }
    });
    update();
});
import './preview-forms';
import './landing';

document.querySelectorAll('[data-seat-picker]').forEach((picker) => {
    const toolbar = picker.querySelector('[data-seat-traveler-switch]');
    const travelers = [...toolbar.querySelectorAll('[data-seat-traveler]')];
    const panels = [...picker.querySelectorAll('[data-seat-panel]')];
    const groups = panels.map((panel) => [...panel.querySelectorAll('input[type="radio"]')]);
    const unavailable = groups.map((seats) => new Set(seats.filter((seat) => seat.disabled)));
    let active = 0;

    const update = () => {
        const selected = groups.map((seats) => seats.find((seat) => seat.checked));

        groups.forEach((seats, index) => {
            for (const seat of seats) {
                const assigned = selected.some((choice, owner) => owner !== index && choice?.value === seat.value);
                seat.disabled = unavailable[index].has(seat) || assigned;
            }

            panels[index].hidden = index !== active;
            travelers[index].setAttribute('aria-pressed', String(index === active));
            travelers[index].querySelector('[data-seat-summary]').textContent = selected[index]
                ? ` · ${selected[index].dataset.seatLabel}`
                : '';
        });
    };

    travelers.forEach((traveler, index) => {
        traveler.addEventListener('click', () => {
            active = index;
            update();
        });
    });

    picker.addEventListener('change', update);
    picker.addEventListener('invalid', (event) => {
        const index = panels.indexOf(event.target.closest('[data-seat-panel]'));
        if (index !== -1 && panels[index].hidden) {
            active = index;
            update();
        }
    }, true);

    toolbar.hidden = travelers.length < 2;
    picker.classList.add('seat-picker-ready');
    update();
});
