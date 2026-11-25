import { beforeEach, expect, it, vi } from 'vitest';
import { initDatepickers } from '../../../resources/js/lib/datepicker';
import { initRowLists } from '../../../resources/js/lib/rows';
import { initSelects } from '../../../resources/js/lib/select2';

vi.mock('../../../resources/js/lib/datepicker', () => ({ initDatepickers: vi.fn() }));
vi.mock('../../../resources/js/lib/select2', () => ({ initSelects: vi.fn() }));

/** The bed list of the ward room edit form: two beds, the next index after them, at least one. */
function bedList({ next = 2, min = 1 } = {}) {
    document.body.innerHTML = `
        <fieldset data-row-list data-row-list-next="${next}" data-row-list-min="${min}">
            <div data-row-list-items>
                <div data-row><input name="bed_numbers[0][bed_number]" value="1"><button type="button" data-row-remove>x</button></div>
                <div data-row><input name="bed_numbers[1][bed_number]" value="2"><button type="button" data-row-remove>x</button></div>
            </div>
            <template data-row-list-template>
                <div data-row>
                    <input type="hidden" name="bed_numbers[__INDEX__][id]">
                    <label for="bed-__INDEX__">Койка</label>
                    <input id="bed-__INDEX__" name="bed_numbers[__INDEX__][bed_number]">
                    <button type="button" data-row-remove>x</button>
                </div>
            </template>
            <button type="button" data-row-add>Добавить</button>
        </fieldset>`;

    initRowLists();

    return document.querySelector('[data-row-list]');
}

const names = () => [...document.querySelectorAll('[data-row-list-items] input:not([type="hidden"])')].map((input) => input.name);
const removeButtons = () => [...document.querySelectorAll('[data-row-list-items] [data-row-remove]')];

beforeEach(() => {
    vi.mocked(initSelects).mockClear();
    vi.mocked(initDatepickers).mockClear();
});

it('adds a row from the template with the next index in its names, ids and labels', () => {
    const list = bedList();

    list.querySelector('[data-row-add]').click();

    const row = list.querySelectorAll('[data-row]')[2];

    expect(names()).toEqual(['bed_numbers[0][bed_number]', 'bed_numbers[1][bed_number]', 'bed_numbers[2][bed_number]']);
    expect(row.querySelector('[type="hidden"]').name).toBe('bed_numbers[2][id]');
    expect(row.querySelector('label').htmlFor).toBe('bed-2');
    expect(row.querySelector('#bed-2')).not.toBeNull();
    expect(document.activeElement).toBe(row.querySelector('#bed-2'));
});

it('gives the select and date fields of a new row their widgets', () => {
    const list = bedList();

    list.querySelector('[data-row-add]').click();

    const row = list.querySelectorAll('[data-row]')[2];

    expect(initSelects).toHaveBeenCalledWith(row);
    expect(initDatepickers).toHaveBeenCalledWith(row);
});

it('never reuses an index after a removal, so old() keys with gaps stay apart', () => {
    const list = bedList();

    list.querySelector('[data-row-add]').click();
    removeButtons()[2].click();
    list.querySelector('[data-row-add]').click();

    // The removed row's index 2 is not given again: the server would merge the two rows.
    expect(names()).toEqual(['bed_numbers[0][bed_number]', 'bed_numbers[1][bed_number]', 'bed_numbers[3][bed_number]']);
});

it('starts at data-row-list-next, the index after the highest one of the rendered rows', () => {
    const list = bedList({ next: 7 });

    list.querySelector('[data-row-add]').click();

    expect(names().at(-1)).toBe('bed_numbers[7][bed_number]');
});

it('keeps the minimum number of rows: their remove buttons are disabled', () => {
    const list = bedList({ min: 2 });

    expect(removeButtons().map((button) => button.disabled)).toEqual([true, true]);

    list.querySelector('[data-row-add]').click();
    expect(removeButtons().map((button) => button.disabled)).toEqual([false, false, false]);

    removeButtons()[0].click();
    expect(names()).toEqual(['bed_numbers[1][bed_number]', 'bed_numbers[2][bed_number]']);
    expect(removeButtons().map((button) => button.disabled)).toEqual([true, true]);
});

it('sets a list up once, however often it is asked', () => {
    const list = bedList();

    initRowLists();
    list.querySelector('[data-row-add]').click();

    expect(names()).toHaveLength(3);
});
