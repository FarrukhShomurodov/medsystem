import { describe, expect, it } from 'vitest';
import {
    addDays,
    addMinutes,
    daySlots,
    endOfIsoWeek,
    formatDate,
    formatTime,
    fromMinutes,
    parseDate,
    startOfIsoWeek,
    toIsoDate,
    toMinutes,
} from '../../../resources/js/lib/time';

describe('times of day', () => {
    it('converts "HH:mm" and "HH:mm:ss" to minutes and back', () => {
        expect(toMinutes('09:30')).toBe(570);
        expect(toMinutes('09:30:00')).toBe(570);
        expect(fromMinutes(570)).toBe('09:30');
        expect(formatTime('14:05:00')).toBe('14:05');
    });

    it('wraps around midnight like a clock', () => {
        expect(fromMinutes(1440)).toBe('00:00');
        expect(fromMinutes(-15)).toBe('23:45');
        expect(addMinutes('23:30', 45)).toBe('00:15');
        expect(addMinutes('09:30', '30')).toBe('10:00');
    });

    it('lists the slots of a day from midnight, every interval minutes', () => {
        const slots = daySlots(30);

        expect(slots).toHaveLength(48);
        expect(slots.slice(0, 3)).toEqual(['00:00', '00:30', '01:00']);
        expect(slots.at(-1)).toBe('23:30');
    });

    it('stops at the end of the day when the interval does not divide it', () => {
        const slots = daySlots(25);

        expect(slots).toHaveLength(58);
        expect(slots.at(-1)).toBe('23:45');
    });

    it('never loops forever on an interval below one minute', () => {
        expect(daySlots(0)).toHaveLength(1440);
    });
});

describe('calendar days', () => {
    it('reads and writes "YYYY-MM-DD" as a local day, with no timezone shift', () => {
        const day = parseDate('2026-11-19');

        expect([day.getFullYear(), day.getMonth(), day.getDate(), day.getHours()]).toEqual([2026, 10, 19, 0]);
        expect(toIsoDate(day)).toBe('2026-11-19');
    });

    it('adds days across a month and a year', () => {
        expect(toIsoDate(addDays(parseDate('2026-11-30'), 1))).toBe('2026-12-01');
        expect(toIsoDate(addDays(parseDate('2026-12-31'), 1))).toBe('2027-01-01');
        expect(toIsoDate(addDays(parseDate('2027-01-01'), -1))).toBe('2026-12-31');
    });

    it('finds the Monday and the Sunday of a day\'s week', () => {
        // 2026-11-16 is a Monday, 2026-11-22 a Sunday.
        for (const day of ['2026-11-16', '2026-11-19', '2026-11-22']) {
            expect(toIsoDate(startOfIsoWeek(parseDate(day)))).toBe('2026-11-16');
            expect(toIsoDate(endOfIsoWeek(parseDate(day)))).toBe('2026-11-22');
        }

        // A week across the new year.
        expect(toIsoDate(startOfIsoWeek(parseDate('2027-01-03')))).toBe('2026-12-28');
    });

    it('formats a day in Russian', () => {
        expect(formatDate(parseDate('2026-11-08'))).toBe('08.11');
        expect(formatDate(parseDate('2026-11-08'), { day: 'numeric', month: 'long' })).toBe('8 ноября');
    });
});
