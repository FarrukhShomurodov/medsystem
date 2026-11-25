/*
| The prescription edit form (patients/medication/edit): times of intake and compatible medications,
| shared with the prescription modal of the patient card (./patients/prescriptions.js).
*/
import { showFlash } from '../lib/flash';
import { loadCompatibleMedications, setupPrescriptionRows } from './patients/prescriptions';

const form = document.getElementById('prescription-form');

if (form) {
    const substanceUrl = form.dataset.substanceUrl;

    setupPrescriptionRows(form, { substanceUrl });
    loadCompatibleMedications(form.querySelector('.medication-select'), substanceUrl);

    // The times of intake sit in a hidden input, which the browser does not check.
    form.addEventListener('submit', (event) => {
        if (form.querySelector('.admission-hidden-input').value === '') {
            event.preventDefault();
            showFlash('Добавьте хотя бы одно время приёма.', 'warning');
            form.querySelector('.admission-hour').focus();
        }
    });
}
