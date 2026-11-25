import { describe, expect, it, vi } from 'vitest';
import { errorMessage, slotCell, url } from '../../../../resources/js/pages/receptions/booking';

vi.mock('@tabler/core', () => ({ Alert: {}, Modal: vi.fn() }));

describe('url', () => {
    it('fills the placeholders of an endpoint, encoded', () => {
        expect(url('/dashboard/receptions/calendar/__STAFF__/__SERVICE__', { STAFF: 4, SERVICE: 'a b' }))
            .toBe('/dashboard/receptions/calendar/4/a%20b');
        expect(url('/api/patients')).toBe('/api/patients');
    });
});

describe('errorMessage', () => {
    it('prefers the server\'s message to the fallback', () => {
        expect(errorMessage({ data: { message: 'Это время уже занято.' } }, 'Не удалось')).toBe('Это время уже занято.');
        expect(errorMessage(new TypeError('Failed to fetch'), 'Не удалось')).toBe('Не удалось');
    });
});

describe('slotCell', () => {
    const free = { state: 'free', time: '09:00', date: '2026-11-19', staffId: 2, doctor: 'Юсупова Нигора' };

    it('offers a free slot as a button that carries the slot, to an account that may book', () => {
        const button = slotCell(free, { canBook: true }).querySelector('button');

        expect(button.textContent).toBe('Свободно');
        expect(button.getAttribute('aria-label')).toBe('Записать на 09:00: Юсупова Нигора');
        expect(button.matches('[data-book]')).toBe(true);
        expect([button.dataset.time, button.dataset.date, button.dataset.staffId]).toEqual(['09:00', '2026-11-19', '2']);
    });

    it('shows a free slot as text only to an account that may not book', () => {
        const cell = slotCell(free, { canBook: false });

        expect(cell.className).toBe('slot slot-free');
        expect(cell.querySelector('button')).toBeNull();
        expect(cell.textContent).toBe('Свободно');
    });

    it('opens a booked slot by its appointment', () => {
        const button = slotCell({ state: 'booked', receptionId: 41, time: '09:30', doctor: 'Юсупова Нигора' }, { canBook: false }).querySelector('button');

        expect(button.dataset.receptionId).toBe('41');
        expect(button.getAttribute('aria-label')).toBe('Запись на 09:30: Юсупова Нигора, подробнее');
    });

    it('puts text in with textContent, never as markup', () => {
        const cell = slotCell({ ...free, doctor: '<img src=x onerror=alert(1)>' }, { canBook: true });

        expect(cell.querySelector('img')).toBeNull();
    });

    it('draws past, unavailable and empty cells as plain cells', () => {
        expect(slotCell({ state: 'past' }, { canBook: true }).textContent).toBe('Прошло');
        expect(slotCell({ state: 'unavailable' }, { canBook: true }).textContent).toBe('Недоступно');
        expect(slotCell(null, { canBook: true }).outerHTML).toBe('<td></td>');
    });
});
