import $ from 'jquery';
import { beforeEach, expect, it, vi } from 'vitest';
import { gridText, mountBookingPage, slotsOf, stubApi } from '../support/booking-page';

vi.mock('@tabler/core', () => ({
    Alert: { getOrCreateInstance: () => ({ close: vi.fn() }) },
    Modal: vi.fn(),
}));

/*
| The booking grid of today: the doctors of a service as columns, their slots as rows. Its step is
| the shortest appointment length of those doctors, so a longer appointment covers several rows.
*/

const today = '2026-11-19';
const data = {
    today,
    canBook: true,
    canOpenCalendar: false,
    canCancel: true,
    cancelled: 'cancelled',
    urls: {
        doctors: '/api/staff-by-service/__SERVICE__',
        availability: '/api/staff-availability-today/__STAFF__',
        booked: '/api/staff-booked-list-today/__STAFF__',
        calendar: '/dashboard/receptions/calendar/__STAFF__/__SERVICE__',
        patients: '/api/patients',
        store: '/api/receptions',
        show: '/api/receptions/__RECEPTION__',
        status: '/api/reception-status-update/__RECEPTION__',
    },
};

// Two doctors of one service, 09:00-10:00: one sees a patient every 15 minutes, the other every 30
// and has an appointment at 09:30.
const quick = slotsOf(15, { hours: ['09:00', '10:00'] });
const slow = slotsOf(30, { hours: ['09:00', '10:00'], booked: { '09:30': 41 } });

let page;
let api;

beforeEach(async () => {
    page = mountBookingPage(`
        <select id="service"><option value=""></option><option value="7">Консультация кардиолога</option></select>
        <div id="slot-grid"></div>`, data, 'appointment-data');
    api = stubApi({
        'GET /api/staff-by-service/7': [
            { id: 1, full_name: 'Алиев Бахтиёр', admission_period: 15 },
            { id: 2, full_name: 'Юсупова Нигора', admission_period: 30 },
        ],
        'GET /api/staff-availability-today/1': quick.availability,
        'GET /api/staff-booked-list-today/1': quick.bookings,
        'GET /api/staff-availability-today/2': slow.availability,
        'GET /api/staff-booked-list-today/2': slow.bookings,
        'GET /api/receptions/41': {
            id: 41, patient_name: 'Каримова Дильноза', start_time: '09:30:00', end_time: '10:00:00', staff_name: 'Юсупова Нигора',
            position: 'Кардиолог', date: today, status_label: 'Новый', comment: null, can_cancel: true,
        },
        'POST /api/receptions': { success: true },
    });

    vi.resetModules();
    await import('../../../resources/js/pages/reception-appointment');

    $('#service').val('7').trigger('change');
    await vi.waitFor(() => expect(document.querySelector('#slot-grid table')).not.toBeNull());
});

/** The free or booked button of a doctor's column (1-based) in the row of `time`. */
function cellButton(time, column) {
    const row = [...document.querySelectorAll('#slot-grid tbody tr')].find((tr) => tr.firstElementChild.textContent === time);

    return row.children[column].querySelector('button');
}

it('draws the doctors\' slots of today on the step of the shortest appointment', () => {
    expect([...document.querySelectorAll('#slot-grid thead th')].map((th) => th.textContent)).toEqual(['Время', 'Алиев Бахтиёр', 'Юсупова Нигора']);
    expect(gridText(document.getElementById('slot-grid'))).toEqual([
        '09:00 | Свободно | Свободно',
        '09:15 | Свободно | Свободно',
        '09:30 | Свободно | Забронировано',
        '09:45 | Свободно | Забронировано',
    ]);
});

it('books a free row with the doctor\'s own appointment length', () => {
    cellButton('09:45', 1).click();

    expect(document.getElementById('appointment-doctor').value).toBe('Алиев Бахтиёр');
    expect(document.getElementById('appointment-date').value).toBe(today);
    expect(document.getElementById('appointment-time').value).toBe('09:45 – 10:00');
    expect(page.modal('appointment-modal').show).toHaveBeenCalledOnce();
});

it('books the slot a row belongs to when a longer appointment covers several rows', () => {
    // The 09:15 row of the 30-minute doctor lies inside her 09:00 slot. It booked 09:15-09:45,
    // which the server accepted and which then took both her 09:00 and her 09:30 slot.
    const free = cellButton('09:15', 2);

    expect(free.getAttribute('aria-label')).toBe('Записать на 09:00: Юсупова Нигора');

    free.click();

    expect(document.getElementById('appointment-time').value).toBe('09:00 – 09:30');
});

it('posts the booking of the chosen slot and patient, then draws the grid again', async () => {
    cellButton('09:15', 2).click();
    // What a pick in the patient picker leaves in the select.
    $('#patient').append($('<option value="5" selected>').text('Каримова Дильноза'));
    document.getElementById('comment').value = 'Повторный приём';

    document.getElementById('appointment-form').dispatchEvent(new Event('submit', { cancelable: true }));

    await vi.waitFor(() => expect(page.modal('appointment-modal').hide).toHaveBeenCalled());

    const [, init] = api.mock.calls.find(([url, request]) => url === '/api/receptions' && request.method === 'POST');

    expect(JSON.parse(init.body)).toEqual({
        patient_id: '5',
        staff_id: 2,
        service_id: '7',
        date: today,
        start_time: '09:00',
        end_time: '09:30',
        status: 'new',
        comment: 'Повторный приём',
    });
    expect(document.getElementById('flash-messages').textContent).toContain('Запись успешно создана.');
    await vi.waitFor(() => expect(api.mock.calls.filter(([url]) => url === '/api/staff-by-service/7')).toHaveLength(2));
});

it('shows the server\'s reason when a booking is refused, and keeps the dialog open', async () => {
    // Another registrar took the slot after the grid was drawn: the server refuses it with a 422.
    const reason = 'Это время уже занято: выберите другой слот.';
    cellButton('09:00', 1).click();
    $('#patient').append($('<option value="5" selected>').text('Каримова Дильноза'));
    api.mockImplementationOnce(async () => new Response(JSON.stringify({ message: reason }), { status: 422 }));

    document.getElementById('appointment-form').dispatchEvent(new Event('submit', { cancelable: true }));

    await vi.waitFor(() => expect(document.getElementById('flash-messages').textContent).toContain(reason));
    expect(page.modal('appointment-modal').hide).not.toHaveBeenCalled();
});

it('asks for a patient before it posts anything', () => {
    cellButton('09:00', 1).click();

    document.getElementById('appointment-form').dispatchEvent(new Event('submit', { cancelable: true }));

    expect(document.getElementById('patient-error').hidden).toBe(false);
    expect(api.mock.calls.some(([, request]) => request?.method === 'POST')).toBe(false);
});

it('opens a booked appointment from any row it covers', async () => {
    cellButton('09:45', 2).click();

    await vi.waitFor(() => expect(page.modal('reception-modal').show).toHaveBeenCalledOnce());

    const field = (name) => document.querySelector(`[data-reception-field="${name}"]`).textContent;

    expect([field('patient'), field('time'), field('doctor'), field('comment')]).toEqual(['Каримова Дильноза', '09:30 – 10:00', 'Юсупова Нигора', '—']);
    expect(document.querySelector('[data-cancel-reception]').hidden).toBe(false);
});
