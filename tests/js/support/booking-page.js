import { Modal } from '@tabler/core';
import $ from 'jquery';
import { vi } from 'vitest';

/*
| What the two booking grids (receptions/appointment, receptions/calendar) need from their page: the
| dialogs of admin/receptions/partials/modals.blade.php (the ids the scripts look up), the flash
| container, Bootstrap's modal and select2, and endpoints that answer as the API does. The test file
| mocks '@tabler/core' (vi.mock); the dialogs' Modal instances are stubbed on that mock here.
*/

/** The dialogs and the flash container; `grid` is the page's own markup above them. */
export function mountBookingPage(grid, data, dataId) {
    document.body.innerHTML = `
        <div id="flash-messages"></div>
        ${grid}
        <div class="modal" id="appointment-modal">
            <form id="appointment-form" novalidate>
                <select id="patient" name="patient_id" data-placeholder="Найдите пациента"></select>
                <div id="patient-error" hidden>Выберите пациента.</div>
                <input id="appointment-doctor" readonly>
                <input id="appointment-date" readonly>
                <input id="appointment-time" readonly>
                <select id="appointment-status"><option value="new">Новый</option></select>
                <textarea id="comment"></textarea>
                <button type="submit" data-submit>Записать</button>
            </form>
        </div>
        <div class="modal" id="reception-modal">
            ${['patient', 'time', 'doctor', 'position', 'date', 'status', 'comment'].map((field) => `<dd data-reception-field="${field}"></dd>`).join('')}
            <button type="button" data-cancel-reception hidden>Отменить приём</button>
        </div>
        <script type="application/json" id="${dataId}">${JSON.stringify(data)}</script>`;

    const modals = new Map();

    Modal.getOrCreateInstance = (element) => {
        if (!modals.has(element.id)) {
            modals.set(element.id, { show: vi.fn(), hide: vi.fn() });
        }

        return modals.get(element.id);
    };
    $.fn.select2 = vi.fn(function select2() {
        return this;
    });

    return { modal: (id) => Modal.getOrCreateInstance(document.getElementById(id)) };
}

/**
 * fetch() answering by path: `routes` maps "GET /api/x" (or "POST /api/x") to a JSON body or to a
 * function of the request's init that returns one. Anything else is a 404.
 */
export function stubApi(routes) {
    const fetch = vi.fn(async (url, init = {}) => {
        const key = `${init.method ?? 'GET'} ${url}`;
        const route = routes[key];

        if (route === undefined) {
            return new Response(JSON.stringify({ message: `No route ${key}` }), { status: 404 });
        }

        return new Response(JSON.stringify(typeof route === 'function' ? route(init) : route), { status: 200 });
    });

    vi.stubGlobal('fetch', fetch);

    return fetch;
}

/**
 * A doctor's slots of a day as SlotGrid::availability() and bookings() send them: back to back
 * from midnight, `minutes` long; free inside the reception hours, booked where an appointment is.
 *
 * @param {number} minutes
 * @param {{ hours: [string, string], booked?: Record<string, number> }} day  booked: start time => reception id
 */
export function slotsOf(minutes, { hours: [from, to], booked = {} }) {
    const toMinutes = (time) => Number(time.slice(0, 2)) * 60 + Number(time.slice(3, 5));
    const format = (total) => `${String(Math.floor(total / 60)).padStart(2, '0')}:${String(total % 60).padStart(2, '0')}`;
    const availability = [];
    const bookings = [];

    for (let start = 0; start < 1440; start += minutes) {
        const time = format(start);

        availability.push({ time, is_available: start >= toMinutes(from) && start + minutes <= toMinutes(to), admission_period: minutes });
        bookings.push({ time, admission_period: minutes, is_booked: time in booked, reception_id: booked[time] ?? null });
    }

    return { availability, bookings };
}

/** The grid as text: one line per row, the time and then each cell. */
export function gridText(grid) {
    return [...grid.querySelectorAll('tbody tr')].map((row) => [...row.children].map((cell) => cell.textContent.trim()).join(' | '));
}
