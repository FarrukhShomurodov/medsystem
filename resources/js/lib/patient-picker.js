/*
| The patient picker: a select2 that searches the register through api.patients.index
| (PatientPickerQuery, a few patients per request) instead of listing it. The booking dialog of both
| calendars (pages/receptions/booking.js) and the patient service form (pages/patient-service-form.js)
| use it. Its <select> has no .select2 class, so initSelects() leaves it to the page module.
*/
import $ from 'jquery';

/** "Каримова Дильноза (12.04.1990)": the birth date tells namesakes apart. */
export function patientLabel({ full_name: name, birth_date: birthDate }) {
    return birthDate ? `${name} (${birthDate.split('-').reverse().join('.')})` : name;
}

/**
 * The select2 results of an answer of the endpoint. Each result brings its own _resultId: select2
 * 4.1 normalises AJAX results through a detached method that reads `this.container`, which throws
 * once the library runs as strict module code.
 *
 * @param {Array<{ id: number, full_name: string, birth_date: ?string }>} patients
 */
export function patientResults(patients) {
    return patients.map((patient) => ({
        id: patient.id,
        text: patientLabel(patient),
        _resultId: `patient-result-${patient.id}`,
    }));
}

/**
 * Turns `select` into the patient picker. The endpoint filters and limits the patients by the
 * typed text (select2 sends it as term); data-placeholder is the hint of the empty field.
 *
 * @param {HTMLSelectElement} select
 * @param {{ url: string, dropdownParent?: JQuery }} options
 */
export function initPatientPicker(select, { url, dropdownParent = $(document.body) }) {
    $(select).select2({
        dropdownParent,
        placeholder: select.dataset.placeholder,
        ajax: {
            url,
            dataType: 'json',
            delay: 250,
            cache: true,
            processResults: (patients) => ({ results: patientResults(patients) }),
        },
    });
}
