/*
| What the two booking grids share (reception-appointment.js: today's slots of the doctors of a
| service; reception-calendar.js: a doctor's week): the cells of a slot, the booking dialog and the
| dialog of a booked appointment with its cancellation (view receptions/partials/modals).
|
| Not a page entry of its own (vite.config.js builds only the files directly in resources/js/pages).
*/
import { Modal } from '@tabler/core';
import $ from 'jquery';
import { confirmAction } from '../../lib/confirm';
import { showFlash } from '../../lib/flash';
import { fetchJson } from '../../lib/http';
import { initPatientPicker } from '../../lib/patient-picker';
import { formatTime } from '../../lib/time';

/** An endpoint of the page data with its placeholders filled: url(data.urls.show, { RECEPTION: 5 }). */
export function url(template, params = {}) {
    return Object.entries(params).reduce(
        (result, [name, value]) => result.replace(`__${name}__`, encodeURIComponent(value)),
        template,
    );
}

/** The message of a failed request, for MedSystem.flash. */
export function errorMessage(error, fallback) {
    return error?.data?.message ?? fallback;
}

/** A table cell of the grid; all text goes in with textContent. */
function cell(className, child) {
    const td = document.createElement('td');
    td.className = `slot ${className}`;
    td.append(child);

    return td;
}

function button(className, text, label, data) {
    const element = document.createElement('button');
    element.type = 'button';
    element.className = `btn btn-sm w-100 ${className}`;
    element.textContent = text;
    element.setAttribute('aria-label', label);
    Object.assign(element.dataset, data);

    return element;
}

/**
 * The cell of a slot:
 *   { state: 'free', time, date, staffId, doctor }  bookable (a button when the account may book)
 *   { state: 'booked', receptionId, time, doctor }  opens the appointment
 *   { state: 'unavailable' | 'past' } / null        plain cells
 */
export function slotCell(slot, { canBook }) {
    switch (slot?.state) {
        case 'free':
            return canBook
                ? cell('slot-free', button('btn-success', 'Свободно', `Записать на ${slot.time}: ${slot.doctor}`, {
                    book: '', time: slot.time, date: slot.date, staffId: slot.staffId,
                }))
                : cell('slot-free', document.createTextNode('Свободно'));
        case 'booked':
            return cell('slot-booked', button('btn-info', 'Забронировано', `Запись на ${slot.time}: ${slot.doctor}, подробнее`, {
                receptionId: slot.receptionId,
            }));
        case 'unavailable':
            return cell('slot-unavailable', document.createTextNode('Недоступно'));
        case 'past':
            return cell('slot-past', document.createTextNode('Прошло'));
        default:
            return document.createElement('td');
    }
}

/** A row of the grid: the time as the row header, then one cell per column. */
export function slotRow(time, cells) {
    const row = document.createElement('tr');
    const header = document.createElement('th');
    header.scope = 'row';
    header.textContent = time;
    row.append(header, ...cells);

    return row;
}

/**
 * A message in place of the grid (loading, nothing to show, an error). The grid is not a live
 * region (a whole week's table would be read out); an error is announced as an alert.
 */
export function gridMessage(container, text, variant = 'secondary') {
    const message = document.createElement('p');
    message.className = `card-body border-top mb-0 text-${variant}`;
    message.textContent = text;

    if (variant === 'danger') {
        message.setAttribute('role', 'alert');
    }

    container.replaceChildren(message);
}

/**
 * The booking dialog. open({ staffId, doctor, date, start, end }) fills it for a free slot;
 * the patient is searched through data.urls.patients. onBooked() runs after a successful booking.
 */
export function setupBooking(data, serviceIdOf, onBooked) {
    const modalElement = document.getElementById('appointment-modal');
    const form = document.getElementById('appointment-form');
    const patient = document.getElementById('patient');
    const patientError = document.getElementById('patient-error');
    const status = document.getElementById('appointment-status');
    const comment = document.getElementById('comment');
    const submit = form.querySelector('[data-submit]');
    const modal = Modal.getOrCreateInstance(modalElement);
    let slot = null;

    initPatientPicker(patient, { url: data.urls.patients, dropdownParent: $(modalElement) });

    const showPatientError = (show) => {
        patient.classList.toggle('is-invalid', show);
        patientError.hidden = !show;
        $(patient).next('.select2').toggleClass('is-invalid', show);
    };

    $(patient).on('change', () => showPatientError(false));

    modalElement.addEventListener('hidden.bs.modal', () => {
        $(patient).val(null).trigger('change');
        comment.value = '';
        status.selectedIndex = 0;
        showPatientError(false);
        slot = null;
    });

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!slot) {
            return;
        }

        if (!patient.value) {
            showPatientError(true);
            $(patient).select2('open');

            return;
        }

        submit.disabled = true;

        try {
            await fetchJson(data.urls.store, {
                method: 'POST',
                body: {
                    patient_id: patient.value,
                    staff_id: slot.staffId,
                    service_id: serviceIdOf(),
                    date: slot.date,
                    start_time: slot.start,
                    end_time: slot.end,
                    status: status.value,
                    comment: comment.value,
                },
            });

            modal.hide();
            showFlash('Запись успешно создана.');
            onBooked(slot);
        } catch (error) {
            showFlash(errorMessage(error, 'Не удалось создать запись.'), 'danger');
        } finally {
            submit.disabled = false;
        }
    });

    return {
        open(chosen) {
            slot = chosen;
            document.getElementById('appointment-doctor').value = chosen.doctor;
            document.getElementById('appointment-date').value = chosen.date;
            document.getElementById('appointment-time').value = `${chosen.start} – ${chosen.end}`;
            modal.show();
        },
    };
}

/**
 * The dialog of a booked appointment. show(id) loads and shows it; the cancel button (only for an
 * account with change_receptions_status) asks first, then onCancelled() redraws the grid.
 */
export function setupReceptionView(data, onCancelled) {
    const modalElement = document.getElementById('reception-modal');
    const modal = Modal.getOrCreateInstance(modalElement);
    const cancel = modalElement.querySelector('[data-cancel-reception]');
    const fields = Object.fromEntries(
        [...modalElement.querySelectorAll('[data-reception-field]')].map((element) => [element.dataset.receptionField, element]),
    );
    let receptionId = null;

    const fill = (reception) => {
        const values = {
            patient: reception.patient_name,
            time: `${formatTime(reception.start_time)} – ${formatTime(reception.end_time)}`,
            doctor: reception.staff_name,
            position: reception.position,
            date: reception.date,
            status: reception.status_label,
            comment: reception.comment,
        };

        for (const [name, element] of Object.entries(fields)) {
            element.textContent = values[name] || '—';
        }
    };

    cancel?.addEventListener('click', async () => {
        const id = receptionId;

        // One dialog at a time: close this one before the confirmation opens.
        await new Promise((resolve) => {
            modalElement.addEventListener('hidden.bs.modal', resolve, { once: true });
            modal.hide();
        });

        if (!(await confirmAction('Отменить этот приём?', { accept: 'Отменить приём' }))) {
            return;
        }

        try {
            await fetchJson(url(data.urls.status, { RECEPTION: id }), { method: 'PUT', body: { status: data.cancelled } });
            showFlash('Приём отменён.');
            onCancelled();
        } catch (error) {
            showFlash(errorMessage(error, 'Не удалось отменить приём.'), 'danger');
        }
    });

    return {
        async show(id) {
            try {
                const reception = await fetchJson(url(data.urls.show, { RECEPTION: id }));

                receptionId = reception.id;
                fill(reception);

                if (cancel) {
                    cancel.hidden = !(data.canCancel && reception.can_cancel);
                }

                modal.show();
            } catch (error) {
                showFlash(errorMessage(error, 'Не удалось загрузить запись.'), 'danger');
            }
        },
    };
}
