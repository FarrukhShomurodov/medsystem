/**
 * The wizards of the patient and examination-map forms have one row of buttons under all steps:
 * "Назад" is disabled on the first step and "Далее" on the last. (app.js moves between the steps.)
 *
 * @param {HTMLElement|null} wizard a .bs-stepper with .btn-prev and .btn-next
 */
export function setupWizardButtons(wizard) {
    if (!wizard) {
        return;
    }

    const steps = wizard.querySelectorAll('.step').length;
    const previous = wizard.querySelector('.btn-prev');
    const next = wizard.querySelector('.btn-next');

    const update = (index) => {
        if (previous) {
            previous.disabled = index <= 0;
        }

        if (next) {
            next.disabled = index >= steps - 1;
        }
    };

    wizard.addEventListener('shown.bs-stepper', (event) => update(event.detail.indexStep));

    // The step shown now: the first, or the one app.js opened for a field that failed validation.
    update([...wizard.querySelectorAll('.step')].findIndex((step) => step.classList.contains('active')));
}
