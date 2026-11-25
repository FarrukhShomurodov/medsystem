/*
| The booking grid of today (receptions/appointment): pick a service, and the doctors who provide
| it appear as columns, with their free and booked slots of today as rows. A free slot books an
| appointment, a booked one shows it. The grid's step is the shortest appointment length of those
| doctors; a longer appointment covers several rows, and each of them books or shows that one slot
| (its start), never a slot of its own that would straddle two of the doctor's.
*/
import $ from 'jquery';
import { pageData } from '../lib/data';
import { fetchJson } from '../lib/http';
import { addMinutes, fromMinutes, toMinutes } from '../lib/time';
import { errorMessage, gridMessage, setupBooking, setupReceptionView, slotCell, slotRow, url } from './receptions/booking';

const data = pageData('appointment-data');
const grid = document.getElementById('slot-grid');
const service = document.getElementById('service');
let doctors = [];
let request = 0;

/** The rows to the end of each slot, `step` minutes apart: the keys of the cells a slot covers. */
function* coveredTimes(slot, step) {
    const start = toMinutes(slot.time);

    for (let minutes = start; minutes < start + Number(slot.admission_period); minutes += step) {
        yield fromMinutes(minutes);
    }
}

function headerCell(doctor) {
    const th = document.createElement('th');
    th.scope = 'col';

    const name = document.createElement('span');
    name.textContent = doctor.name;
    th.append(name);

    if (data.canOpenCalendar) {
        const link = document.createElement('a');
        link.href = url(data.urls.calendar, { STAFF: doctor.id, SERVICE: service.value });
        link.className = 'btn btn-sm btn-icon btn-ghost-primary ms-1';
        link.title = 'Календарь на неделю';
        link.setAttribute('aria-label', `Календарь на неделю: ${doctor.name}`);

        const icon = document.createElement('i');
        icon.className = 'bx bx-calendar';
        icon.setAttribute('aria-hidden', 'true');
        link.append(icon);
        th.append(link);
    }

    return th;
}

/**
 * slots[time][doctorId] = { state: 'free', start } | { state: 'booked', start, receptionId }, where
 * `time` is a row of the grid and `start` the start of the doctor's slot that covers it.
 */
function slotMap(availability, bookings, step) {
    const slots = {};

    doctors.forEach((doctor, index) => {
        for (const slot of availability[index]) {
            for (const time of coveredTimes(slot, step)) {
                slots[time] ??= {};

                if (slot.is_available) {
                    slots[time][doctor.id] = { state: 'free', start: slot.time };
                }
            }
        }

        // A booking marks the cells it covers, also outside today's free slots (an absence or
        // reception hours recorded after it was booked), so it stays in sight to be moved.
        for (const slot of bookings[index].filter((booking) => booking.is_booked)) {
            for (const time of coveredTimes(slot, step)) {
                slots[time] ??= {};
                slots[time][doctor.id] = { state: 'booked', start: slot.time, receptionId: slot.reception_id };
            }
        }
    });

    return slots;
}

function render(slots) {
    const times = Object.keys(slots)
        .filter((time) => doctors.some((doctor) => slots[time][doctor.id]))
        .sort();

    if (times.length === 0) {
        gridMessage(grid, 'На сегодня у врачей этой услуги свободного времени нет.');

        return;
    }

    const table = document.createElement('table');
    table.className = 'table table-bordered table-sm table-vcenter text-center mb-0';

    const caption = document.createElement('caption');
    caption.className = 'visually-hidden';
    caption.textContent = `Расписание на сегодня, ${data.today}`;

    const head = document.createElement('thead');
    const headRow = document.createElement('tr');
    const timeHeader = document.createElement('th');
    timeHeader.scope = 'col';
    timeHeader.textContent = 'Время';
    headRow.append(timeHeader, ...doctors.map(headerCell));
    head.append(headRow);

    const body = document.createElement('tbody');

    for (const time of times) {
        body.append(slotRow(time, doctors.map((doctor) => {
            const slot = slots[time][doctor.id];

            return slotCell(slot && { ...slot, time: slot.start, date: data.today, staffId: doctor.id, doctor: doctor.name }, data);
        })));
    }

    table.append(caption, head, body);

    const wrapper = document.createElement('div');
    wrapper.className = 'table-responsive slot-grid-scroll border-top';
    wrapper.append(table);
    grid.replaceChildren(wrapper);
}

async function load() {
    const serviceId = service.value;
    const current = ++request;

    if (!serviceId) {
        return;
    }

    grid.setAttribute('aria-busy', 'true');
    gridMessage(grid, 'Загрузка расписания…');

    try {
        const list = await fetchJson(url(data.urls.doctors, { SERVICE: serviceId }));

        if (current !== request) {
            return;
        }

        doctors = list.map((doctor) => ({
            id: doctor.id,
            name: doctor.full_name,
            period: Number(doctor.admission_period),
        }));

        if (doctors.length === 0) {
            gridMessage(grid, 'Нет врачей, к которым можно записаться на эту услугу.');

            return;
        }

        const step = Math.min(...doctors.map((doctor) => doctor.period));
        const [availability, bookings] = await Promise.all([
            Promise.all(doctors.map((doctor) => fetchJson(url(data.urls.availability, { STAFF: doctor.id })))),
            Promise.all(doctors.map((doctor) => fetchJson(url(data.urls.booked, { STAFF: doctor.id })))),
        ]);

        if (current === request) {
            render(slotMap(availability, bookings, step));
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

const booking = setupBooking(data, () => service.value, load);
const reception = setupReceptionView(data, load);

grid.addEventListener('click', (event) => {
    const free = event.target.closest('[data-book]');

    if (free) {
        const doctor = doctors.find((item) => String(item.id) === free.dataset.staffId);

        booking.open({
            staffId: doctor.id,
            doctor: doctor.name,
            date: free.dataset.date,
            start: free.dataset.time,
            end: addMinutes(free.dataset.time, doctor.period),
        });

        return;
    }

    const booked = event.target.closest('[data-reception-id]');

    if (booked) {
        reception.show(booked.dataset.receptionId);
    }
});

// The service is a select2 field: its changes come through jQuery.
$(service).on('change', load);
