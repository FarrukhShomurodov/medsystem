/*
| A doctor's week (receptions/calendar): the doctor's slots of every day from Monday to Sunday, one
| row per appointment length. Free slots book, booked slots show the appointment; the week moves
| with the buttons. The first week is the clinic's current one (page data), whatever the browser's
| clock says.
*/
import { pageData } from '../lib/data';
import { fetchJson } from '../lib/http';
import { addDays, addMinutes, daySlots, endOfIsoWeek, formatDate, parseDate, startOfIsoWeek, toIsoDate } from '../lib/time';
import { errorMessage, gridMessage, setupBooking, setupReceptionView, slotCell, slotRow } from './receptions/booking';

const data = pageData('calendar-data');
const grid = document.getElementById('slot-grid');
const weekLabel = document.getElementById('current-week');
const today = data.today;
const WEEKDAYS = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
let weekStart = parseDate(data.weekStart);
let request = 0;

const weekDays = () => Array.from({ length: 7 }, (_, index) => addDays(weekStart, index));

function updateWeekLabel() {
    weekLabel.textContent = `${formatDate(weekStart)} – ${formatDate(endOfIsoWeek(weekStart))}`;
}

/** The state of each cell: slots[date][time] from the week's slots of the server. */
function slotMap(slots) {
    const map = {};

    for (const slot of slots) {
        map[slot.date] ??= {};

        if (slot.is_booked) {
            map[slot.date][slot.time] = { state: 'booked', receptionId: slot.reception_id };
        } else if (slot.is_available) {
            map[slot.date][slot.time] = { state: slot.date < today ? 'past' : 'free' };
        }
    }

    return map;
}

function render(map) {
    const days = weekDays();
    const table = document.createElement('table');
    table.className = 'table table-bordered table-sm table-vcenter text-center mb-0';

    const caption = document.createElement('caption');
    caption.className = 'visually-hidden';
    caption.textContent = `Расписание врача ${data.doctor.name} на неделю ${weekLabel.textContent}`;

    const head = document.createElement('thead');
    const headRow = document.createElement('tr');
    const timeHeader = document.createElement('th');
    timeHeader.scope = 'col';
    timeHeader.textContent = 'Время';
    headRow.append(timeHeader);

    days.forEach((day, index) => {
        const th = document.createElement('th');
        th.scope = 'col';
        th.textContent = WEEKDAYS[index];

        const date = document.createElement('div');
        date.className = 'text-secondary fw-normal';
        date.textContent = formatDate(day);
        th.append(date);

        if (toIsoDate(day) === today) {
            th.classList.add('slot-today');
            th.setAttribute('aria-current', 'date');
        }

        headRow.append(th);
    });

    head.append(headRow);

    const body = document.createElement('tbody');
    let firstOpenRow = null;

    for (const time of daySlots(data.admissionPeriod)) {
        const row = slotRow(time, days.map((day) => {
            const date = toIsoDate(day);
            const slot = map[date]?.[time] ?? { state: date < today ? 'past' : 'unavailable' };

            return slotCell({ ...slot, time, date, staffId: data.doctor.id, doctor: data.doctor.name }, data);
        }));

        if (!firstOpenRow && row.querySelector('.slot-free, .slot-booked')) {
            firstOpenRow = row;
        }

        body.append(row);
    }

    table.append(caption, head, body);

    const wrapper = document.createElement('div');
    wrapper.className = 'table-responsive slot-grid-scroll border-top';
    wrapper.append(table);
    grid.replaceChildren(wrapper);

    // The night rows are rarely open: start at the first row with a free or booked slot.
    if (firstOpenRow) {
        wrapper.scrollTop = firstOpenRow.offsetTop - head.offsetHeight;
    }
}

async function load() {
    const current = ++request;

    updateWeekLabel();
    grid.setAttribute('aria-busy', 'true');
    gridMessage(grid, 'Загрузка расписания…');

    try {
        const query = new URLSearchParams({ start: toIsoDate(weekStart) });
        const slots = await fetchJson(`${data.urls.week}?${query}`);

        if (current === request) {
            render(slotMap(slots));
        }
    } catch (error) {
        if (current === request) {
            gridMessage(grid, errorMessage(error, 'Не удалось загрузить расписание.'), 'danger');
        }
    } finally {
        if (current === request) {
            grid.setAttribute('aria-busy', 'false');
        }
    }
}

const booking = setupBooking(data, () => data.serviceId, load);
const reception = setupReceptionView(data, load);

grid.addEventListener('click', (event) => {
    const free = event.target.closest('[data-book]');

    if (free) {
        booking.open({
            staffId: data.doctor.id,
            doctor: data.doctor.name,
            date: free.dataset.date,
            start: free.dataset.time,
            end: addMinutes(free.dataset.time, data.admissionPeriod),
        });

        return;
    }

    const booked = event.target.closest('[data-reception-id]');

    if (booked) {
        reception.show(booked.dataset.receptionId);
    }
});

for (const button of document.querySelectorAll('[data-week]')) {
    button.addEventListener('click', () => {
        weekStart = startOfIsoWeek(addDays(weekStart, 7 * Number(button.dataset.week)));
        load();
    });
}

load();
