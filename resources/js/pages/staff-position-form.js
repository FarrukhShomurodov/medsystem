/*
| The permissions of the staff position form (staff_positions/create|edit, partial
| staff_positions/form): a search over the permission names, "select all" / "clear", and one
| checkbox per group that checks the whole group and shows when only part of it is checked.
*/

const selector = document.querySelector('[data-permission-selector]');

if (selector) {
    const items = [...selector.querySelectorAll('input[name="permissions[]"]')];
    const categories = [...selector.querySelectorAll('[data-permission-category]')];
    const emptyMessage = selector.querySelector('[data-permission-empty]');

    const itemsOf = (group) => items.filter((item) => item.dataset.category === group);

    /** A group checkbox is checked when all its permissions are, "partly" when some are. */
    const syncCategory = (category) => {
        const group = itemsOf(category.dataset.permissionCategory);
        const checked = group.filter((item) => item.checked).length;

        category.checked = group.length > 0 && checked === group.length;
        category.indeterminate = checked > 0 && checked < group.length;
    };

    const syncAll = () => categories.forEach(syncCategory);

    for (const category of categories) {
        category.addEventListener('change', () => {
            itemsOf(category.dataset.permissionCategory).forEach((item) => {
                item.checked = category.checked;
            });
            category.indeterminate = false;
        });
    }

    selector.addEventListener('change', (event) => {
        if (event.target.matches('input[name="permissions[]"]')) {
            syncCategory(categories.find((category) => category.dataset.permissionCategory === event.target.dataset.category));
        }
    });

    for (const button of selector.querySelectorAll('[data-permission-select]')) {
        button.addEventListener('click', () => {
            const checked = button.dataset.permissionSelect === 'all';

            items.forEach((item) => {
                item.checked = checked;
            });
            syncAll();
        });
    }

    // Search: hide the permissions whose name does not match, and the groups left empty.
    selector.querySelector('[data-permission-search]')?.addEventListener('input', (event) => {
        const term = event.target.value.trim().toLocaleLowerCase('ru');
        let visible = 0;

        for (const group of selector.querySelectorAll('[data-permission-group]')) {
            let visibleInGroup = 0;

            for (const item of group.querySelectorAll('[data-permission-item]')) {
                const matches = item.textContent.toLocaleLowerCase('ru').includes(term);

                item.hidden = !matches;
                visibleInGroup += matches ? 1 : 0;
            }

            group.hidden = visibleInGroup === 0;
            visible += visibleInGroup;
        }

        emptyMessage.hidden = visible > 0;
    });

    syncAll();
}
