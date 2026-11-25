/*
| Dates and times of the schedules, without a date library. Times of day are "HH:mm" strings and
| calendar days are local Date objects at midnight, so no timezone conversion ever happens: the
| server sends the clinic's dates and times and gets the same strings back.
|
|   moment(t, 'HH:mm').add(30, 'minutes').format('HH:mm')  →  addMinutes(t, 30)
|   moment(t, 'HH:mm:ss').format('HH:mm')                  →  formatTime(t)
|   a.isBefore(b) on times                                 →  toMinutes(a) < toMinutes(b)
|   moment(d, 'YYYY-MM-DD')                                →  parseDate(d)
|   moment(d).add(i, 'days')                               →  addDays(d, i)
|   moment(d).startOf('isoWeek') / endOf('isoWeek')        →  startOfIsoWeek(d) / endOfIsoWeek(d)
|   day.format('YYYY-MM-DD') / day.format('DD.MM')         →  toIsoDate(day) / formatDate(day)
*/

const pad = (n) => String(n).padStart(2, '0');

/** "09:30" or "09:30:00" → 570 */
export function toMinutes(time) {
    const [hours, minutes] = String(time).split(':').map(Number);

    return hours * 60 + minutes;
}

/** 570 → "09:30"; wraps around midnight like a clock. */
export function fromMinutes(total) {
    const minutes = ((total % 1440) + 1440) % 1440;

    return `${pad(Math.floor(minutes / 60))}:${pad(minutes % 60)}`;
}

/** addMinutes("09:30", 45) → "10:15" */
export function addMinutes(time, minutes) {
    return fromMinutes(toMinutes(time) + Number(minutes));
}

/** "09:30:00" → "09:30" */
export function formatTime(time) {
    return fromMinutes(toMinutes(time));
}

/** The slots of a day every `interval` minutes from 00:00: ["00:00", "00:30", ...] */
export function daySlots(interval) {
    const step = Math.max(1, Number(interval));
    const slots = [];

    for (let minutes = 0; minutes < 1440; minutes += step) {
        slots.push(fromMinutes(minutes));
    }

    return slots;
}

/** "2026-11-18" → that day at local midnight */
export function parseDate(isoDate) {
    const [year, month, day] = String(isoDate).split('-').map(Number);

    return new Date(year, month - 1, day);
}

/** A day → "2026-11-18" */
export function toIsoDate(date) {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

/** A new Date `days` later (or earlier when negative). */
export function addDays(date, days) {
    return new Date(date.getFullYear(), date.getMonth(), date.getDate() + Number(days));
}

/** The Monday of the day's week. */
export function startOfIsoWeek(date) {
    return addDays(date, -((date.getDay() + 6) % 7));
}

/** The Sunday of the day's week. */
export function endOfIsoWeek(date) {
    return addDays(startOfIsoWeek(date), 6);
}

/**
 * A day in Russian: formatDate(day) → "18.11"; any Intl.DateTimeFormat options:
 * formatDate(day, { day: 'numeric', month: 'long', year: 'numeric' }) → "18 ноября 2026 г."
 */
export function formatDate(date, options = { day: '2-digit', month: '2-digit' }) {
    return new Intl.DateTimeFormat('ru-RU', options).format(date);
}
