/*
| The employee wizard (staffs/create|edit, view staffs/form). app.js already runs the steps, the
| select2 fields and the .flatpickr-date pickers; this module adds:
|   - the additional position, which cannot be the main one;
|   - the previews of the chosen photo and signature;
|   - the weekly schedule: per day a "working day" switch and work / reception intervals that can
|     be added and removed, and one switch for "no reception hours" (a disabled field is not sent,
|     so a day off or a schedule without reception hours saves no intervals);
|   - on the edit form, the absences: one day or a period, picked as "Y-m-d" or "Y-m-d to Y-m-d",
|     the format StaffRequest reads (its picker is set up here, not by app.js, for that " to ").
*/
import $ from 'jquery';
import { flatpickr } from '../lib/datepicker';

const form = document.getElementById('staff-form');

// The default hours of a new interval.
const DEFAULT_HOURS = { work: ['08:30', '17:30'], reception: ['08:00', '17:30'] };

/** Copy the first element of a <template> and name its [data-field] controls with `nameOf(field)`. */
function fromTemplate(template, nameOf, idOf) {
    const row = template.content.firstElementChild.cloneNode(true);

    for (const control of row.querySelectorAll('[data-field]')) {
        const { field } = control.dataset;

        control.name = nameOf(field);
        control.id = idOf(field);
        row.querySelector(`[data-label-for="${field}"]`).htmlFor = control.id;
    }

    return row;
}

/* The additional position: the option of the main position is disabled (they must differ). */
function setupPositions() {
    const main = document.getElementById('position_id');
    const additional = document.getElementById('addition_position_id');

    const sync = () => {
        for (const option of additional.options) {
            option.disabled = option.value !== '' && option.value === main.value;
        }

        if (additional.selectedOptions[0]?.disabled) {
            $(additional).val('').trigger('change');
        }
    };

    // select2 reports its changes through jQuery only.
    $(main).on('change', sync);
    sync();
}

/* The photo and signature previews: the chosen image, or the saved one when the choice is cleared. */
function setupImagePreviews() {
    for (const input of form.querySelectorAll('[data-image-preview]')) {
        const preview = document.getElementById(input.dataset.imagePreview);
        const saved = preview.getAttribute('src');
        let objectUrl = null;

        input.addEventListener('change', () => {
            const [file] = input.files;

            if (objectUrl) {
                URL.revokeObjectURL(objectUrl);
                objectUrl = null;
            }

            if (file?.type.startsWith('image/')) {
                objectUrl = URL.createObjectURL(file);
                preview.src = objectUrl;
            } else if (saved) {
                preview.src = saved;
            } else {
                preview.removeAttribute('src');
            }

            preview.hidden = !preview.getAttribute('src');
        });
    }
}

/* The weekly schedule. */
function setupSchedule() {
    const schedule = document.getElementById('weekly-schedule');
    const template = document.getElementById('time-range-template');
    const receptionOff = form.querySelector('[data-reception-off]');
    const days = [...schedule.querySelectorAll('[data-schedule-day]')];

    /** Enable the fields the day's switches allow; a working day keeps at least one work interval. */
    const syncDay = (day) => {
        const working = day.querySelector('[data-day-toggle]').checked;
        const enabled = { work: working, reception: working && !receptionOff.checked };

        day.classList.toggle('is-off', !working);
        day.querySelector('[data-day-id]').disabled = !working;
        day.querySelector('[data-reception-block]').classList.toggle('is-off', working && receptionOff.checked);

        for (const [type, on] of Object.entries(enabled)) {
            const ranges = day.querySelector(`[data-ranges="${type}"]`);

            ranges.querySelectorAll('input, button').forEach((control) => {
                control.disabled = !on;
            });
            day.querySelector(`[data-add-range="${type}"]`).disabled = !on;

            const removeButtons = ranges.querySelectorAll('[data-remove-range]');

            if (type === 'work' && removeButtons.length === 1) {
                removeButtons[0].disabled = true;
            }
        }
    };

    const addRange = (day, type) => {
        const ranges = day.querySelector(`[data-ranges="${type}"]`);
        const dayIndex = Number(ranges.dataset.day);
        const index = Number(ranges.dataset.nextIndex);
        const part = { start_time: 'start', end_time: 'end' };

        ranges.dataset.nextIndex = String(index + 1);

        const row = fromTemplate(
            template,
            (field) => `days[${dayIndex}][${type}][${index}][${field}]`,
            (field) => `days-${dayIndex}-${type}-${index}-${part[field]}`,
        );
        const [start, end] = DEFAULT_HOURS[type];

        row.querySelector('[data-field="start_time"]').value = start;
        row.querySelector('[data-field="end_time"]').value = end;
        ranges.append(row);
        syncDay(day);
        row.querySelector('input').focus();
    };

    schedule.addEventListener('click', (event) => {
        const add = event.target.closest('[data-add-range]');

        if (add) {
            addRange(add.closest('[data-schedule-day]'), add.dataset.addRange === 'reception' ? 'reception' : 'work');

            return;
        }

        const remove = event.target.closest('[data-remove-range]');

        if (remove) {
            const day = remove.closest('[data-schedule-day]');
            const type = remove.closest('[data-ranges]').dataset.ranges;

            remove.closest('[data-time-range]').remove();
            syncDay(day);
            day.querySelector(`[data-add-range="${type}"]`).focus();
        }
    });

    schedule.addEventListener('change', (event) => {
        if (event.target.matches('[data-day-toggle]')) {
            syncDay(event.target.closest('[data-schedule-day]'));
        }
    });

    receptionOff.addEventListener('change', () => days.forEach(syncDay));
    days.forEach(syncDay);
}

/* The absences of the edit form. */
function setupAbsences() {
    const absences = form.querySelector('[data-absences]');

    if (!absences) {
        return;
    }

    const template = document.getElementById('absence-template');
    const { periodType } = absences.dataset;

    /** The date picker of a row: one day, or a period once the type says so. */
    const initDate = (row) => {
        const input = row.querySelector('.absence-date');
        const period = row.querySelector('[data-absence-type]').value === periodType;

        input._flatpickr?.destroy();

        if (!period) {
            [input.value] = input.value.split(' to ');
        }

        flatpickr(input, { dateFormat: 'Y-m-d', mode: period ? 'range' : 'single', locale: { rangeSeparator: ' to ' } });
    };

    absences.querySelectorAll('[data-absence]').forEach(initDate);

    // The type is a select2 field: its changes come through jQuery.
    $(absences).on('change', '[data-absence-type]', function () {
        initDate(this.closest('[data-absence]'));
    });

    absences.addEventListener('click', (event) => {
        const remove = event.target.closest('[data-remove-absence]');

        if (remove) {
            const row = remove.closest('[data-absence]');

            row.querySelector('.absence-date')._flatpickr?.destroy();
            row.remove();
            form.querySelector('[data-add-absence]').focus();
        }
    });

    form.querySelector('[data-add-absence]').addEventListener('click', () => {
        const index = Number(absences.dataset.nextIndex);

        absences.dataset.nextIndex = String(index + 1);

        const row = fromTemplate(
            template,
            (field) => `absence[${index}][${field}]`,
            (field) => `absence-${index}-${field}`,
        );

        absences.append(row);
        window.MedSystem.initWidgets(row);
        initDate(row);
        row.querySelector('.absence-date').focus();
    });
}

if (form) {
    setupPositions();
    setupImagePreviews();
    setupSchedule();
    setupAbsences();
}
