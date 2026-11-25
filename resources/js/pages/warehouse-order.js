/*
| The supply request form of a department warehouse (admin/warehouses/order): the warehouse picked first
| decides which items can be ordered (one <template> per warehouse type), then the form takes any number
| of item lines, items[n][id] and items[n][quantity].
*/
import { showFlash } from '../lib/flash';

const INDEX = /__INDEX__/g;

const form = document.querySelector('[data-order-form]');

if (form) {
    const warehouse = form.querySelector('[data-order-warehouse]');
    const warehouseId = form.querySelector('[data-order-warehouse-id]');
    const items = form.querySelector('[data-order-items]');

    let template = null;
    let nextIndex = 0;

    const addItem = () => {
        if (!template) {
            showFlash('Сначала выберите склад.', 'warning');
            warehouse.focus();

            return;
        }

        const item = template.content.firstElementChild.cloneNode(true);
        const index = String(nextIndex++);

        for (const element of item.querySelectorAll('[id], [for], [name]')) {
            for (const attribute of ['id', 'for', 'name']) {
                const value = element.getAttribute(attribute);

                if (value !== null) {
                    element.setAttribute(attribute, value.replace(INDEX, index));
                }
            }
        }

        items.append(item);
    };

    warehouse.addEventListener('change', () => {
        const type = warehouse.selectedOptions[0]?.dataset.type;

        template = type ? document.getElementById(`order-item-${type}`) : null;
        warehouseId.value = template ? warehouse.value : '';
        items.replaceChildren();
        nextIndex = 0;

        if (template) {
            addItem();
            // The items offered depend on the warehouse: it cannot change once the order has lines.
            warehouse.disabled = true;
        }
    });

    form.querySelector('[data-order-add]').addEventListener('click', addItem);

    items.addEventListener('click', (event) => {
        event.target.closest('[data-order-remove]')?.closest('[data-order-item]').remove();
    });
}
