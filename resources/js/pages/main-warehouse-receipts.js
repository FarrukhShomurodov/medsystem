/*
| The receipt form of the main warehouse (admin/main_warehouses/receipts_goods): every item picked in a
| section's select gets a line built from that section's <template>. A drug may come in several batches,
| so a medication line can be repeated ("+ партия") and the medication lines are numbered on their own;
| supplies and household materials have one line per item, keyed by the item's id.
*/
import $ from 'jquery';
import { initDatepickers } from '../lib/datepicker';

const KEY = /__LINE__/g;

/** A line of the section's template for item `id`, its fields named with `key`. */
function buildLine(template, key, id, name) {
    const line = template.content.firstElementChild.cloneNode(true);

    for (const element of line.querySelectorAll('[id], [for], [name]')) {
        for (const attribute of ['id', 'for', 'name']) {
            const value = element.getAttribute(attribute);

            if (value !== null) {
                element.setAttribute(attribute, value.replace(KEY, key));
            }
        }
    }

    line.dataset.id = id;
    line.querySelector('[data-line-id]').value = id;
    line.querySelector('[data-line-title]').textContent = name;
    line.querySelector('[data-remove-line]').setAttribute('aria-label', `Убрать «${name}»`);

    return line;
}

/** Remove a line with the date pickers it opened (their calendars live outside the line). */
function removeLine(line) {
    line.querySelectorAll('input').forEach((input) => input._flatpickr?.destroy());
    line.remove();
}

function setupSection(section) {
    const kind = section.dataset.receiptSection;
    const template = document.getElementById(`receipt-${kind}-line`);
    const select = section.querySelector('[data-section-select]');
    const $select = $(select);
    const lines = section.querySelector('[data-section-lines]');
    const empty = section.querySelector('[data-section-empty]');
    const count = section.querySelector('[data-section-count]');
    const body = section.querySelector('[data-section-body]');
    const toggle = section.querySelector('[data-section-toggle]');

    let nextLine = 0;
    const keyOf = (id) => (kind === 'medications' ? String(nextLine++) : id);
    const linesOf = (id) => [...lines.children].filter((line) => line.dataset.id === id);
    const nameOf = (id) => [...select.options].find((option) => option.value === id)?.textContent.trim() ?? '';

    const refresh = () => {
        count.textContent = String(lines.children.length);
        empty.hidden = lines.children.length > 0;
    };

    const addLine = (id, after = null) => {
        const line = buildLine(template, keyOf(id), id, nameOf(id));

        if (after) {
            after.after(line);
        } else {
            lines.append(line);
        }

        initDatepickers(line);
    };

    // select2 reports its changes as jQuery events.
    $select.on('change', () => {
        const selected = ($select.val() ?? []).map(String);

        [...lines.children].filter((line) => !selected.includes(line.dataset.id)).forEach(removeLine);
        selected.filter((id) => linesOf(id).length === 0).forEach((id) => addLine(id));
        refresh();
    });

    lines.addEventListener('click', (event) => {
        const line = event.target.closest('[data-line]');

        if (!line) {
            return;
        }

        if (event.target.closest('[data-add-batch]')) {
            addLine(line.dataset.id, line);
            refresh();
        } else if (event.target.closest('[data-remove-line]')) {
            if (linesOf(line.dataset.id).length > 1) {
                // Another batch of the same drug stays: only this line goes.
                removeLine(line);
                refresh();
            } else {
                // The item's last line: unselect the item, which removes the line.
                $select.val(($select.val() ?? []).filter((value) => String(value) !== line.dataset.id)).trigger('change');
            }
        }
    });

    // A section switched off is not part of the receipt.
    toggle.addEventListener('change', () => {
        body.hidden = !toggle.checked;

        if (!toggle.checked) {
            $select.val(null).trigger('change');
        }
    });

    refresh();
}

document.querySelectorAll('[data-receipt-section]').forEach(setupSection);
