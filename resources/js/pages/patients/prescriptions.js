/*
| The fields of a prescription shared by the "Добавить медикамент" modal of the patient card and the
| prescription edit form (patients/medication/edit):
|
| - the times of intake: chips (.tag) picked from an hour and a minute select, kept as a
|   comma-separated list in the row's hidden admission_periods[] input;
| - the compatible medications: after the main drug is chosen, the drugs with the same active
|   substance are offered (GET api/medication/active-substance/{substance}); each one added becomes
|   a pair of hidden compatible_medications[i][] / compatible_dosages[i][] inputs, where i is the
|   position of its prescription row in the form (the server reads the rows in posting order).
|
| Markup: a .prescription-row holds .admission-times (hour, minute, .btn-add-time, .tag-list,
| .admission-hidden-input) and .compatible-medications (select, dosage, .btn-add-compatible,
| .compatible-list); the row's .medication-select carries data-active-substance on its options.
*/
import $ from 'jquery';
import { showFlash } from '../../lib/flash';
import { fetchJson } from '../../lib/http';
import { fillUrl } from './urls';

/** A time of intake as a removable chip. */
function timeChip(time) {
    const chip = document.createElement('span');
    chip.className = 'tag';
    chip.dataset.time = time;
    chip.append(time);

    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'btn-close';
    remove.dataset.removeTime = '';
    remove.setAttribute('aria-label', `Удалить время ${time}`);
    chip.append(remove);

    return chip;
}

function syncTimes(wrapper) {
    const times = [...wrapper.querySelectorAll('.tag-list [data-time]')].map((chip) => chip.dataset.time);
    wrapper.querySelector('.admission-hidden-input').value = times.join(',');
}

/** Draw the chips of the times already in the hidden input (the edit form). */
export function renderAdmissionTimes(wrapper) {
    const list = wrapper.querySelector('.tag-list');
    const times = wrapper.querySelector('.admission-hidden-input').value.split(',').filter(Boolean);

    list.replaceChildren(...times.map(timeChip));
}

/** The position of a compatible drug's prescription row among the rows of its form. */
function rowIndex(item) {
    const row = item.closest('.prescription-row');
    const form = row?.closest('form');

    return form ? [...form.querySelectorAll('.prescription-row')].indexOf(row) : 0;
}

/** Name the hidden inputs of every compatible drug after the current position of its row. */
export function reindexCompatibleMedications(form) {
    form.querySelectorAll('.compatible-item input[data-field]').forEach((input) => {
        input.name = `${input.dataset.field}[${rowIndex(input)}][]`;
    });
}

function compatibleItem(medicationId, label, dosage) {
    const item = document.createElement('div');
    item.className = 'input-group compatible-item mb-1';

    for (const [field, value] of [
        ['compatible_medications', medicationId],
        ['compatible_dosages', dosage],
    ]) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.dataset.field = field;
        input.value = value;
        item.append(input);
    }

    const text = document.createElement('span');
    text.className = 'input-group-text flex-grow-1';
    text.textContent = `${label} (${dosage})`;

    const remove = document.createElement('button');
    remove.type = 'button';
    remove.className = 'btn btn-outline-danger btn-icon btn-remove-compatible';
    remove.setAttribute('aria-label', `Убрать ${label}`);
    remove.innerHTML = '<i class="bx bx-x" aria-hidden="true"></i>';

    item.append(text, remove);

    return item;
}

/**
 * Offer the drugs with the active substance of the row's main drug in its compatible-drug select.
 *
 * @param {HTMLSelectElement} medicationSelect
 * @param {string} urlTemplate route('api.medications.by-active-substance', ['substance' => '__substance__'])
 */
export async function loadCompatibleMedications(medicationSelect, urlTemplate) {
    const row = medicationSelect.closest('.prescription-row');
    const select = row?.querySelector('.compatible-medication-select');
    const substance = medicationSelect.selectedOptions[0]?.dataset.activeSubstance;

    if (!select || !substance) {
        return;
    }

    const placeholder = (text) => new Option(text, '', true, true);

    try {
        const medications = await fetchJson(fillUrl(urlTemplate, { substance }));
        const first = placeholder('Выберите медикамент');
        first.disabled = true;

        select.replaceChildren(
            first,
            ...(medications ?? []).map(
                (medication) => new Option(`${medication.name} (${medication.composition?.active_substance_name ?? '—'})`, medication.id),
            ),
        );
    } catch {
        select.replaceChildren(placeholder('Не удалось загрузить медикаменты'));
    }
}

/**
 * Wire the time chips and the compatible drugs of every prescription row under `root` (present or
 * added later).
 *
 * @param {HTMLElement} root
 * @param {{substanceUrl: string}} options
 */
export function setupPrescriptionRows(root, { substanceUrl }) {
    root.addEventListener('click', (event) => {
        const addTime = event.target.closest('.btn-add-time');
        const removeTime = event.target.closest('[data-remove-time]');
        const addCompatible = event.target.closest('.btn-add-compatible');
        const removeCompatible = event.target.closest('.btn-remove-compatible');

        if (addTime) {
            const wrapper = addTime.closest('.admission-times');
            const time = `${wrapper.querySelector('.admission-hour').value}:${wrapper.querySelector('.admission-minute').value}`;
            const list = wrapper.querySelector('.tag-list');

            if (![...list.children].some((chip) => chip.dataset.time === time)) {
                list.append(timeChip(time));
                syncTimes(wrapper);
            }
        } else if (removeTime) {
            const wrapper = removeTime.closest('.admission-times');
            removeTime.closest('[data-time]').remove();
            syncTimes(wrapper);
        } else if (addCompatible) {
            const wrapper = addCompatible.closest('.compatible-medications');
            const select = wrapper.querySelector('.compatible-medication-select');
            const dosage = wrapper.querySelector('.compatible-dosage');
            const option = select.selectedOptions[0];

            if (!select.value || !dosage.value.trim()) {
                showFlash('Выберите совместимый медикамент и укажите его дозировку.', 'warning');

                return;
            }

            const item = compatibleItem(select.value, option.text, dosage.value.trim());
            wrapper.querySelector('.compatible-list').append(item);
            reindexCompatibleMedications(item.closest('form'));

            select.value = '';
            dosage.value = '';
        } else if (removeCompatible) {
            removeCompatible.closest('.compatible-item').remove();
        }
    });

    // select2 reports a choice as a jQuery event only: listen through jQuery.
    $(root).on('change', '.medication-select', function () {
        loadCompatibleMedications(this, substanceUrl);
    });
}
