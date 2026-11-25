import Stepper from 'bs-stepper';

const steppers = new WeakMap();

/** The 1-based step of the stepper whose pane holds `node`, or 0. */
function stepOf(wizard, node) {
    const pane = node.closest('.content');

    if (!pane || !pane.id) {
        return 0;
    }

    return [...wizard.querySelectorAll('.step')].findIndex((step) => step.dataset.target === `#${pane.id}`) + 1;
}

/**
 * The one wizard initialiser: one Stepper per .bs-stepper.wizard-vertical / .wizard-numbered under
 * `root` (a wizard carries both classes; it still gets one instance).
 *
 * - .btn-next / .btn-prev move between steps without submitting the form they sit in.
 * - After a failed submission the steps holding .is-invalid fields are marked and the first opens.
 * - When the browser stops a submission on a required field of a hidden step, that step opens so
 *   the browser can point at the field.
 *
 * @param {ParentNode} [root]
 */
export function initSteppers(root = document) {
    for (const wizard of root.querySelectorAll('.bs-stepper.wizard-vertical, .bs-stepper.wizard-numbered')) {
        if (steppers.has(wizard)) {
            continue;
        }

        const stepper = new Stepper(wizard, { linear: false });
        steppers.set(wizard, stepper);

        wizard.addEventListener('click', (event) => {
            const button = event.target.closest('.btn-next, .btn-prev');

            if (!button || !wizard.contains(button)) {
                return;
            }

            event.preventDefault();

            if (button.classList.contains('btn-next')) {
                stepper.next();
            } else {
                stepper.previous();
            }
        });

        let reporting = false;

        wizard.addEventListener(
            'invalid',
            (event) => {
                if (reporting) {
                    return;
                }

                // The browser fires one event per invalid field, in order: open the first one's step.
                reporting = true;
                setTimeout(() => {
                    reporting = false;
                });

                const step = stepOf(wizard, event.target);

                if (step > 0) {
                    stepper.to(step);
                }
            },
            true,
        );

        const failed = [...new Set([...wizard.querySelectorAll('.is-invalid')].map((field) => stepOf(wizard, field)))]
            .filter((step) => step > 0)
            .sort((a, b) => a - b);

        const steps = wizard.querySelectorAll('.step');
        failed.forEach((step) => steps[step - 1].classList.add('has-error'));

        if (failed.length > 0) {
            stepper.to(failed[0]);
        }
    }
}

/**
 * The Stepper of a wizard element, for a page script that needs to move it (stepper.to(3)).
 *
 * @param {Element} wizard
 * @returns {Stepper|undefined}
 */
export function stepperOf(wizard) {
    return steppers.get(wizard);
}
