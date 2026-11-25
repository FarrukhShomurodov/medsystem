import { initDatepickers } from './datepicker';
import { initSelects } from './select2';

/*
| Lists of form rows the user adds and removes, set up once for the page by app.js: the beds of a
| ward room, the subgroups of a service group, the excipients of a medication, the lines of a transfer.
|
|   <div data-row-list data-row-list-next="3" data-row-list-min="1">
|       <div data-row-list-items> ...rows... </div>
|       <template data-row-list-template>             one row; "__INDEX__" in name/id/for becomes its index
|           <div data-row> <label for="beds-__INDEX__"> <input id="beds-__INDEX__" name="beds[__INDEX__]">
|                          <button type="button" data-row-remove aria-label="Удалить"> </div>
|       </template>
|       <button type="button" data-row-add>Добавить</button>
|   </div>
|
| - data-row-list-next: the index of the next new row. Give it max(existing index) + 1 when the rows
|   come from old() input, whose keys can have gaps; the default is the number of rows.
| - data-row-list-min: rows that must stay (their remove buttons are disabled); default 0.
| - The select2 and date picker fields of a new row get their widget; the markup (labels, names,
|   texts) comes from the Blade view, this module only clones it.
*/

const PLACEHOLDER = /__INDEX__/g;
const lists = new WeakSet();

function setUp(list) {
    const items = list.querySelector('[data-row-list-items]');
    const template = list.querySelector('template[data-row-list-template]');
    const addButton = list.querySelector('[data-row-add]');

    if (!items || !template || lists.has(list)) {
        return;
    }

    lists.add(list);

    const min = Number(list.dataset.rowListMin ?? 0);
    const rows = () => [...items.children].filter((row) => row.matches('[data-row]'));
    let next = Number(list.dataset.rowListNext ?? rows().length);

    const syncRemoveButtons = () => {
        const locked = rows().length <= min;

        items.querySelectorAll('[data-row-remove]').forEach((button) => {
            button.disabled = locked;
        });
    };

    function addRow() {
        const fragment = template.content.cloneNode(true);
        const index = String(next++);

        for (const field of fragment.querySelectorAll('[name], [id], [for]')) {
            for (const attribute of ['name', 'id', 'for']) {
                const value = field.getAttribute(attribute);

                if (value !== null) {
                    field.setAttribute(attribute, value.replace(PLACEHOLDER, index));
                }
            }
        }

        const row = fragment.firstElementChild;
        items.append(fragment);

        if (row) {
            initSelects(row);
            initDatepickers(row);
            row.querySelector('input:not([type="hidden"]), select, textarea')?.focus();
        }

        syncRemoveButtons();
    }

    list.addEventListener('click', (event) => {
        if (addButton && event.target.closest('[data-row-add]') === addButton) {
            addRow();

            return;
        }

        const remove = event.target.closest('[data-row-remove]');

        if (remove && items.contains(remove) && rows().length > min) {
            const row = remove.closest('[data-row]');

            // Date pickers keep their calendar outside the row.
            row?.querySelectorAll('input').forEach((input) => input._flatpickr?.destroy());
            row?.remove();
            syncRemoveButtons();
            addButton?.focus();
        }
    });

    syncRemoveButtons();
}

/**
 * Set up every [data-row-list] under `root` that is not set up yet.
 *
 * @param {ParentNode} [root]
 */
export function initRowLists(root = document) {
    root.querySelectorAll('[data-row-list]').forEach(setUp);
}
