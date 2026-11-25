import flatpickr from 'flatpickr';
import { Russian } from 'flatpickr/dist/esm/l10n/ru.js';

flatpickr.localize(Russian);
flatpickr.setDefaults({ time_24hr: true });

// The classes the views mark their fields with, and the value format each one submits. Only inputs
// match: flatpickr's own calendars (appended to <body>) use class names of the same family, and a
// time-only field is .flatpickr-clock because .flatpickr-time is the time row inside a calendar.
const presets = [
    ['input.flatpickr-date', { dateFormat: 'Y-m-d' }],
    ['input.flatpickr-date-time', { enableTime: true, dateFormat: 'Y-m-d H:i' }],
    ['input.flatpickr-clock', { enableTime: true, noCalendar: true, dateFormat: 'H:i' }],
];

/** Options a field may add with data-* attributes: data-mode="range", data-min-date="today" ... */
function optionsOf(input) {
    const { mode, minDate, maxDate } = input.dataset;

    return Object.fromEntries(Object.entries({ mode, minDate, maxDate }).filter(([, value]) => value !== undefined));
}

/**
 * The one date/time picker initialiser: every .flatpickr-date (Y-m-d), .flatpickr-date-time
 * (Y-m-d H:i) and .flatpickr-clock (H:i) field under `root` that has no picker yet. The submitted
 * value keeps those formats; the calendar is in Russian.
 *
 * @param {ParentNode} [root]
 */
export function initDatepickers(root = document) {
    for (const [selector, options] of presets) {
        const fields = [...root.querySelectorAll(selector)];

        if (root instanceof Element && root.matches(selector)) {
            fields.unshift(root);
        }

        for (const input of fields) {
            if (!input._flatpickr) {
                flatpickr(input, { ...options, ...optionsOf(input) });
            }
        }
    }
}

export { flatpickr };
