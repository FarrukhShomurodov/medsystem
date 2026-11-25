import { Modal } from '@tabler/core';

let dialog = null;

/** The confirmation modal, created on first use; its texts are set with textContent only. */
function confirmDialog() {
    if (dialog) {
        return dialog;
    }

    const element = document.createElement('div');
    element.className = 'modal modal-blur fade';
    element.id = 'confirm-dialog';
    element.tabIndex = -1;
    element.setAttribute('aria-labelledby', 'confirm-dialog-title');
    element.setAttribute('aria-describedby', 'confirm-dialog-message');
    element.innerHTML = `
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-status bg-danger"></div>
                <div class="modal-body text-center py-4">
                    <i class="bx bx-error-circle confirm-dialog-icon text-danger mb-2" aria-hidden="true"></i>
                    <h2 class="h3 mb-2" id="confirm-dialog-title">Подтвердите действие</h2>
                    <div class="text-secondary" id="confirm-dialog-message"></div>
                </div>
                <div class="modal-footer">
                    <div class="row w-100 g-2 m-0">
                        <div class="col"><button type="button" class="btn w-100" data-bs-dismiss="modal">Отмена</button></div>
                        <div class="col"><button type="button" class="btn btn-danger w-100" data-confirm-accept></button></div>
                    </div>
                </div>
            </div>
        </div>`;
    document.body.append(element);

    dialog = {
        element,
        modal: new Modal(element),
        message: element.querySelector('#confirm-dialog-message'),
        accept: element.querySelector('[data-confirm-accept]'),
        cancel: element.querySelector('[data-bs-dismiss="modal"]'),
    };

    // A destructive action is never the default: focus starts on "Отмена".
    element.addEventListener('shown.bs.modal', () => dialog.cancel.focus());

    return dialog;
}

/**
 * Ask before a destructive action.
 *
 *   if (await MedSystem.confirm('Отменить назначение?', { accept: 'Отменить' })) { ... }
 *
 * @param {string} message
 * @param {{accept?: string}} [options] the text of the confirming button
 * @returns {Promise<boolean>}
 */
export function confirmAction(message, { accept = 'Удалить' } = {}) {
    const { element, modal, message: text, accept: button } = confirmDialog();

    text.textContent = message;
    button.textContent = accept;

    return new Promise((resolve) => {
        let answer = false;

        const onAccept = () => {
            answer = true;
            modal.hide();
        };

        button.addEventListener('click', onAccept, { once: true });
        element.addEventListener(
            'hidden.bs.modal',
            () => {
                button.removeEventListener('click', onAccept);
                resolve(answer);
            },
            { once: true },
        );

        modal.show();
    });
}

/**
 * [data-confirm="Вопрос"] on a form (or on its submit button) asks before the form is sent;
 * on a link, before it is followed. data-confirm-button="Отменить" names the confirming button.
 */
export function setupConfirmations() {
    const confirmed = new WeakSet();

    document.addEventListener(
        'submit',
        (event) => {
            const form = event.target;
            const source = event.submitter?.dataset.confirm ? event.submitter : form;
            const message = source.dataset.confirm;

            if (!message) {
                return;
            }

            if (confirmed.has(form)) {
                confirmed.delete(form);

                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();

            confirmAction(message, { accept: source.dataset.confirmButton }).then((yes) => {
                if (yes) {
                    confirmed.add(form);
                    form.requestSubmit(event.submitter ?? undefined);
                }
            });
        },
        true,
    );

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[data-confirm]');

        if (!link || event.defaultPrevented || event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey) {
            return;
        }

        event.preventDefault();

        confirmAction(link.dataset.confirm, { accept: link.dataset.confirmButton }).then((yes) => {
            if (yes) {
                window.location.assign(link.href);
            }
        });
    });
}
