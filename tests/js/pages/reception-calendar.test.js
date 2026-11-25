import { beforeEach, expect, it, vi } from 'vitest';
import { mountBookingPage, stubApi } from '../support/booking-page';

vi.mock('@tabler/core', () => ({
    Alert: { getOrCreateInstance: () => ({ close: vi.fn() }) },
    Modal: vi.fn(),
}));

/*
| A doctor's week: one row per appointment length from midnight, one column per day from Monday.
| The first week is the clinic's current one (page data), whatever the browser's clock says.
*/

const data = {
    doctor: { id: 2, name: 'Юсупова Нигора' },
    serviceId: 7,
    admissionPeriod: 30,
    weekStart: '2026-11-16',
    today: '2026-11-19',
    canBook: true,
    canCancel: false,
    cancelled: 'cancelled',
    urls: {
        week: '/api/staff-availability-week/2',
        patients: '/api/patients',
        store: '/api/receptions',
        show: '/api/receptions/__RECEPTION__',
        status: '/api/reception-status-update/__RECEPTION__',
    },
};

/** A slot of the week as AppointmentCalendar::days() sends it. */
const slot = (date, time, { available = true, reception = null } = {}) => ({
    date, time, is_available: available, is_booked: reception !== null, reception_id: reception,
});

let page;
let api;

beforeEach(async () => {
    page = mountBookingPage(`
        <span id="current-week"></span>
        <button type="button" data-week="-1">Назад</button>
        <button type="button" data-week="1">Вперёд</button>
        <div id="slot-grid"></div>`, data, 'calendar-data');
    api = stubApi({
        'GET /api/staff-availability-week/2?start=2026-11-16': [
            slot('2026-11-16', '09:00'),
            slot('2026-11-19', '09:00'),
            slot('2026-11-19', '09:30', { reception: 41 }),
            slot('2026-11-20', '09:00'),
        ],
        'GET /api/staff-availability-week/2?start=2026-11-23': [slot('2026-11-23', '10:00')],
    });

    vi.resetModules();
    await import('../../../resources/js/pages/reception-calendar');
    await vi.waitFor(() => expect(document.querySelector('#slot-grid table')).not.toBeNull());
});

/** The cells of a row, by its time. */
function row(time) {
    const tr = [...document.querySelectorAll('#slot-grid tbody tr')].find((element) => element.firstElementChild.textContent === time);

    return [...tr.children].slice(1);
}

it('draws the clinic\'s week from Monday, one row per appointment length from midnight', () => {
    const days = [...document.querySelectorAll('#slot-grid thead th')].slice(1);

    expect(document.getElementById('current-week').textContent).toBe('16.11 – 22.11');
    expect(days.map((th) => th.textContent)).toEqual(['Пн16.11', 'Вт17.11', 'Ср18.11', 'Чт19.11', 'Пт20.11', 'Сб21.11', 'Вс22.11']);
    expect(days[3].getAttribute('aria-current')).toBe('date');
    expect(document.querySelectorAll('#slot-grid tbody tr')).toHaveLength(48);
});

it('marks each slot free, booked, past or unavailable', () => {
    expect(row('09:00').map((td) => td.textContent)).toEqual(['Прошло', 'Прошло', 'Прошло', 'Свободно', 'Свободно', 'Недоступно', 'Недоступно']);
    expect(row('09:30').map((td) => td.textContent)).toEqual(['Прошло', 'Прошло', 'Прошло', 'Забронировано', 'Недоступно', 'Недоступно', 'Недоступно']);
});

it('books a free slot for the doctor\'s appointment length', () => {
    row('09:00')[4].querySelector('button').click();

    expect(document.getElementById('appointment-date').value).toBe('2026-11-20');
    expect(document.getElementById('appointment-time').value).toBe('09:00 – 09:30');
    expect(page.modal('appointment-modal').show).toHaveBeenCalledOnce();
});

it('moves to the next week from Monday', async () => {
    document.querySelector('[data-week="1"]').click();

    await vi.waitFor(() => expect(row('10:00')[0].textContent).toBe('Свободно'));
    expect(document.getElementById('current-week').textContent).toBe('23.11 – 29.11');
    expect(api).toHaveBeenLastCalledWith('/api/staff-availability-week/2?start=2026-11-23', expect.anything());
});
