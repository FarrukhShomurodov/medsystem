/*
| The patient wizard (patients/create, patients/edit): the wizard buttons, "Как адрес проживания"
| (copies the residential address into the registered one) and the removal of an attached file.
*/
import $ from 'jquery';
import { confirmAction } from '../lib/confirm';
import { showFlash } from '../lib/flash';
import { fetchJson } from '../lib/http';
import { setupWizardButtons } from './patients/wizard';

setupWizardButtons(document.querySelector('.bs-stepper'));

const addressFields = [
    ['residential_address_street', 'registered_address_street'],
    ['residential_address_house', 'registered_address_house'],
    ['residential_address_apartment', 'registered_address_apartment'],
    ['residential_postcode', 'registered_postcode'],
];

document.getElementById('copy_address')?.addEventListener('click', () => {
    for (const [from, to] of addressFields) {
        document.getElementById(to).value = document.getElementById(from).value;
    }

    // The selects are select2 widgets: set them through jQuery so the widget shows the value. A region
    // or city typed in (not saved yet) is copied as a new option.
    for (const [from, to] of [
        ['region_id', 'registered_region_id'],
        ['city_id', 'registered_city_id'],
    ]) {
        const source = document.getElementById(from);
        const target = document.getElementById(to);
        const value = source.value;

        if (value && ![...target.options].some((option) => option.value === value)) {
            target.append(new Option(source.selectedOptions[0].text, value));
        }

        $(target).val(value || '').trigger('change');
    }
});

document.addEventListener('click', async (event) => {
    const button = event.target.closest('.delete_file');

    if (!button || !(await confirmAction('Удалить этот файл пациента? Действие нельзя отменить.'))) {
        return;
    }

    button.disabled = true;

    try {
        await fetchJson(button.dataset.url, { method: 'DELETE' });
        button.closest('.file_container').remove();
        showFlash('Файл удалён.');
    } catch (error) {
        button.disabled = false;
        showFlash(error?.data?.message ?? 'Не удалось удалить файл.', 'danger');
    }
});
