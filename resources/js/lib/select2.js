import select2 from 'select2';
import $ from './jquery';

// select2 ships as a CommonJS factory: register it on the page's jQuery.
select2(window, $);

const plural = (n, one, few, many) => {
    const mod10 = n % 10;
    const mod100 = n % 100;

    if (mod10 === 1 && mod100 !== 11) {
        return one;
    }

    return mod10 >= 2 && mod10 <= 4 && (mod100 < 12 || mod100 > 14) ? few : many;
};

const russian = {
    errorLoading: () => 'Не удалось загрузить результаты',
    inputTooLong: ({ input, maximum }) => {
        const n = input.length - maximum;

        return `Удалите ${n} ${plural(n, 'символ', 'символа', 'символов')}`;
    },
    inputTooShort: ({ input, minimum }) => {
        const n = minimum - input.length;

        return `Введите ещё ${n} ${plural(n, 'символ', 'символа', 'символов')}`;
    },
    loadingMore: () => 'Загрузка…',
    maximumSelected: ({ maximum }) => `Можно выбрать не больше ${maximum} ${plural(maximum, 'элемента', 'элементов', 'элементов')}`,
    noResults: () => 'Ничего не найдено',
    searching: () => 'Поиск…',
    removeAllItems: () => 'Очистить',
    removeItem: () => 'Удалить',
    search: () => 'Поиск',
};

$.fn.select2.defaults.set('language', russian);
$.fn.select2.defaults.set('width', '100%');

/**
 * The one select2 initialiser: every <select class="select2"> under `root` that is not one yet.
 *
 * - Options only; free text (a typed value becomes a new option) only with .select2-tags, where
 *   the form turns a typed name into a new record.
 * - A select inside a modal opens its dropdown in that modal, so it stays clickable and scrolls with it.
 * - Other options come from data-* attributes: data-placeholder="Выберите ...", data-allow-clear="true".
 *
 * @param {ParentNode} [root]
 */
export function initSelects(root = document) {
    $(root)
        .find('select.select2')
        .addBack('select.select2')
        .not('.select2-hidden-accessible')
        .each(function () {
            const $select = $(this);
            const $modal = $select.closest('.modal');

            $select.select2({
                tags: $select.hasClass('select2-tags'),
                dropdownParent: $modal.length ? $modal : $(document.body),
            });
        });
}
